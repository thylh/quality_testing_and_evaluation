import { mkdirSync, mkdtempSync, renameSync, rmSync, writeFileSync } from 'node:fs';
import { spawn, spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import net from 'node:net';
import newman from 'newman';
import { fileURLToPath } from 'node:url';
import { setTimeout as delay } from 'node:timers/promises';

const projectRoot = fileURLToPath(new URL('../..', import.meta.url));
const collectionPath = join(projectRoot, 'tests', 'postman', 'laravel_storefront_api.postman_collection.json');
const reportDirectory = join(projectRoot, 'storage', 'app', 'test-results');
const reportPath = join(reportDirectory, 'postman.json');
const temporaryDirectory = mkdtempSync(join(tmpdir(), 'laravel-postman-'));
const databasePath = join(temporaryDirectory, 'testing.sqlite');
writeFileSync(databasePath, '');

const testEnvironment = {
    ...process.env,
    APP_ENV: 'testing',
    APP_DEBUG: 'false',
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: databasePath,
    SESSION_DRIVER: 'array',
    CACHE_STORE: 'array',
    QUEUE_CONNECTION: 'sync',
};

function runPhp(args) {
    const result = spawnSync('php', args, {
        env: testEnvironment,
        cwd: projectRoot,
        stdio: 'inherit',
    });
    if (result.error) {
        throw result.error;
    }
    if (result.status !== 0) {
        throw new Error(`php ${args.join(' ')} failed with code ${result.status}`);
    }
}

async function getAvailablePort() {
    return new Promise((resolve, reject) => {
        const server = net.createServer();
        server.on('error', reject);
        server.listen(0, '127.0.0.1', () => {
            const { port } = server.address();
            server.close(() => resolve(port));
        });
    });
}

let server;
try {
    runPhp(['artisan', 'migrate', '--force']);
    runPhp(['artisan', 'db:seed', '--class=AddProductsSeeder', '--force']);

    const port = await getAvailablePort();
    const baseUrl = `http://127.0.0.1:${port}`;
    const router = join(projectRoot, 'vendor', 'laravel', 'framework', 'src', 'Illuminate', 'Foundation', 'resources', 'server.php');
    server = spawn('php', ['-S', `127.0.0.1:${port}`, router], {
        env: testEnvironment,
        cwd: join(projectRoot, 'public'),
        stdio: 'ignore',
    });

    let ready = false;
    for (let attempt = 0; attempt < 60; attempt += 1) {
        if (server.exitCode !== null) {
            throw new Error(`Laravel server exited with code ${server.exitCode}`);
        }
        try {
            const response = await fetch(`${baseUrl}/up`);
            if (response.ok) {
                ready = true;
                break;
            }
        } catch {
            await delay(500);
        }
    }
    if (! ready) {
        throw new Error(`Laravel server did not become ready at ${baseUrl}`);
    }

    const summary = await new Promise((resolve, reject) => {
        newman.run({
            collection: collectionPath,
            environment: {
                name: 'Local isolated test environment',
                values: [{ key: 'base_url', value: baseUrl, enabled: true }],
            },
            reporters: ['cli'],
            bail: false,
        }, (error, result) => {
            if (error) {
                reject(error);
                return;
            }
            resolve(result);
        });
    });

    const run = summary.run;
    const executions = run.executions.map((execution) => ({
        name: execution.item.name,
        method: execution.request.method,
        url: new URL(execution.request.url.toString()).pathname,
        status: execution.response?.status ?? 'No response',
        statusCode: execution.response?.code ?? null,
        responseTime: execution.response?.responseTime ?? null,
        assertions: (execution.assertions ?? []).map((assertion) => ({
            name: assertion.assertion,
            passed: assertion.error === undefined,
            error: assertion.error?.message ?? null,
        })),
    }));
    const report = {
        generatedAt: new Date().toISOString(),
        duration: run.timings.completed - run.timings.started,
        stats: run.stats,
        failures: run.failures.map((failure) => ({
            source: failure.source?.name ?? 'Newman',
            error: failure.error?.message ?? 'Unknown Newman failure',
        })),
        executions,
    };

    mkdirSync(reportDirectory, { recursive: true });
    const temporaryReportPath = `${reportPath}.tmp`;
    writeFileSync(temporaryReportPath, `${JSON.stringify(report, null, 2)}\n`);
    renameSync(temporaryReportPath, reportPath);

    if (run.stats.assertions.failed > 0 || run.stats.requests.failed > 0) {
        process.exitCode = 1;
    }
} catch (error) {
    console.error(error);
    process.exitCode = 1;
} finally {
    if (server && server.exitCode === null) {
        server.kill();
        await new Promise((resolve) => server.once('exit', resolve));
    }
    rmSync(temporaryDirectory, { recursive: true, force: true });
}
