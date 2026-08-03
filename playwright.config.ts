import { defineConfig, devices } from '@playwright/test';
import { APP_ENV, BASE_URL, E2E_PORT } from './tests/e2e/prepare.mjs';

// End-to-end tests: drive the real app in a browser over the critical happy path
// (log in → create client → project → task → start/stop a timer). They run
// against an isolated SQLite build the webServer command sets up, so they never
// touch your MySQL dev data. Run with `npm run test:e2e`.
export default defineConfig({
    testDir: './tests/e2e',
    // Shared owner + one database + the single-running-timer invariant mean the
    // specs must not race each other; keep it serial and deterministic.
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['list']] : [['list']],

    use: {
        baseURL: BASE_URL,
        trace: 'on-first-retry',
    },

    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

    // Prepare the isolated environment THEN serve, as one command. Playwright
    // starts the webServer before any global setup, so the prep must live here to
    // guarantee `.env.e2e` + the migrated SQLite DB exist before serve binds the
    // port. APP_ENV=e2e is forwarded to the built-in server (serve's passthrough
    // list), so every request loads `.env.e2e`. Reuse a hand-started server
    // locally; always start fresh in CI.
    webServer: {
        command: `node tests/e2e/prepare.mjs && php artisan serve --host=127.0.0.1 --port=${E2E_PORT}`,
        url: BASE_URL,
        env: { APP_ENV },
        reuseExistingServer: !process.env.CI,
        timeout: 180_000,
    },
});
