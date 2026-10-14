import { mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { spawn, spawnSync } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = process.env.SELENIUM_BASE_URL ?? 'http://127.0.0.1:8017';
const temporaryDirectory = mkdtempSync(join(tmpdir(), 'laravel-selenium-'));
const databasePath = join(temporaryDirectory, 'testing.sqlite');
writeFileSync(databasePath, '');

const testEnvironment = {
    ...process.env,
    APP_ENV: 'testing',
    DB_CONNECTION: 'sqlite',
    DB_DATABASE: databasePath,
    SESSION_DRIVER: 'cookie',
    CACHE_STORE: 'array',
    QUEUE_CONNECTION: 'sync',
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
    const migration = spawnSync('php', ['artisan', 'migrate', '--force'], {
        env: testEnvironment,
        stdio: 'inherit',
    });

    if (migration.error) {
        throw migration.error;
    }
    if (migration.status !== 0) {
        throw new Error(`Test database migration failed with code ${migration.status}`);
    }

    server = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8017'], {
        env: testEnvironment,
        stdio: 'inherit',
    });

    await waitForServer();

    const testProcess = spawn(
        process.execPath,
        ['--test', 'tests/selenium/authentication.test.js'],
        { stdio: 'inherit' },
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