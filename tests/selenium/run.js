import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { spawn, spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import net from 'node:net';
import { fileURLToPath } from 'node:url';
import { setTimeout as delay } from 'node:timers/promises';

let baseUrl = process.env.SELENIUM_BASE_URL ?? null;
const projectRoot = fileURLToPath(new URL('../..', import.meta.url));
const temporaryDirectory = mkdtempSync(join(tmpdir(), 'laravel-selenium-'));
const databasePath = join(temporaryDirectory, 'testing.sqlite');
writeFileSync(databasePath, '');

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

const testEnvironment = {
    ...process.env,
    APP_ENV: 'testing',
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: databasePath,
    SESSION_DRIVER: 'cookie',
    CACHE_STORE: 'array',
    QUEUE_CONNECTION: 'sync',
    SELENIUM_BASE_URL: baseUrl,
};

let server;

async function waitForServer() {
    for (let attempt = 0; attempt < 60; attempt += 1) {
        if (server?.exitCode !== null) {
            throw new Error(`Laravel server exited with code ${server.exitCode}`);
        }

        try {
            const response = await fetch(`${baseUrl}/up`);
            if (response.ok) {
                return;
            }
        } catch {
            // Wait for the Laravel development server to start listening.
        }

        await delay(500);
    }

    throw new Error(`Laravel server did not become ready at ${baseUrl}`);
}

try {
    const port = await getAvailablePort();
    baseUrl = process.env.SELENIUM_BASE_URL ?? `http://127.0.0.1:${port}`;

    testEnvironment.SELENIUM_BASE_URL = baseUrl;

    const migration = spawnSync('php', ['artisan', 'migrate', '--force'], {
        env: testEnvironment,
        cwd: projectRoot,
        stdio: 'inherit',
    });

    if (migration.error) {
        throw migration.error;
    }
    if (migration.status !== 0) {
        throw new Error(`Test database migration failed with code ${migration.status}`);
    }

    const seeding = spawnSync('php', ['artisan', 'db:seed', '--class=AddProductsSeeder', '--force'], {
        env: testEnvironment,
        cwd: projectRoot,
        stdio: 'inherit',
    });

    if (seeding.error) {
        throw seeding.error;
    }
    if (seeding.status !== 0) {
        throw new Error(`Test database seeding failed with code ${seeding.status}`);
    }

    const productCount = spawnSync('php', ['artisan', 'tinker', '--execute', 'echo App\\Models\\Product::count();'], {
        env: testEnvironment,
        cwd: projectRoot,
        encoding: 'utf8',
    });

    if (productCount.error) {
        throw productCount.error;
    }
    if (productCount.status !== 0) {
        throw new Error(`Product count verification failed with code ${productCount.status}`);
    }

    console.log(`Selenium test database seed count: ${productCount.stdout.trim()}`);

    server = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', `--port=${port}`], {
        env: testEnvironment,
        cwd: projectRoot,
        stdio: 'inherit',
    });

    await waitForServer();

    const testProcess = spawn(
        process.execPath,
        ['--test', 'tests/selenium/authentication.test.js'],
        {
            env: testEnvironment,
            stdio: 'inherit',
        },
    );

    process.exitCode = await new Promise((resolve, reject) => {
        testProcess.once('error', reject);
        testProcess.once('exit', (code) => resolve(code ?? 1));
    });
} catch (error) {
    console.error(error);
    process.exitCode = 1;
} finally {
    if (server?.pid && process.platform === 'win32') {
        spawnSync('taskkill', ['/PID', String(server.pid), '/T', '/F'], { stdio: 'ignore' });
    } else if (server && server.exitCode === null) {
        server.kill();
        await new Promise((resolve) => server.once('exit', resolve));
    }
    rmSync(temporaryDirectory, { recursive: true, force: true });
}