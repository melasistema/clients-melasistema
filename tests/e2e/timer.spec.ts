import { expect, test } from '@playwright/test';
import { login } from './helpers';

// The critical end-to-end path a freelancer walks on day one: sign in, set up a
// client → project → task, then start tracking time. This exercises the full
// ownership chain, the nested scoped routes, and the persistent timer chrome in
// one browser flow — the thing unit/feature tests can't prove renders and wires
// together.
const CLIENT = 'Acme Studio';
const PROJECT = 'Website Redesign';
const TASK = 'Design the homepage';

test('happy path: log in, build the hierarchy, and start a timer', async ({ page }) => {
    await login(page);

    // --- Create a client -------------------------------------------------
    await page.goto('/clients');
    await page.getByRole('link', { name: 'Add client' }).click();
    await page.locator('#company_name').fill(CLIENT);
    await page.locator('#contact_name').fill('Jane Doe');
    await page.locator('#contact_email').fill('jane@acme.test');
    await page.getByRole('button', { name: 'Save' }).click();

    // Back on the index with the new client listed.
    await expect(page.getByText(CLIENT)).toBeVisible();

    // --- Create a project (hourly) --------------------------------------
    await page.getByRole('link', { name: 'Projects' }).click();
    await page.getByRole('link', { name: 'Add project' }).click();
    await page.locator('#name').fill(PROJECT);
    await page.locator('#hourly_rate').fill('60');
    await page.getByRole('button', { name: 'Save' }).click();
    await expect(page.getByText(PROJECT)).toBeVisible();

    // --- Create a task ---------------------------------------------------
    await page.getByRole('link', { name: 'Tasks' }).click();
    await page.getByRole('link', { name: 'Add task' }).click();
    await page.locator('#title').fill(TASK);
    await page.getByRole('button', { name: 'Save' }).click();
    await expect(page.getByText(TASK)).toBeVisible();

    // --- Start the timer -------------------------------------------------
    await page.getByRole('button', { name: 'Start' }).click();

    // The row flips to the running state: a live "Running (…)" clock and a Stop
    // control where Start was.
    await expect(page.getByText(/Running \(/)).toBeVisible();
    await expect(page.getByRole('button', { name: 'Stop', exact: true })).toBeVisible();

    // The persistent timer bar (app chrome, shared on every request) picks it up —
    // its Stop control carries the "Stop timer" label and only exists while a
    // timer runs, so its presence proves the global bar rendered.
    await expect(page.getByRole('button', { name: 'Stop timer' })).toBeVisible();

    // --- Stop the timer (clean up the running state) ---------------------
    await page.getByRole('button', { name: 'Stop', exact: true }).click();
    await expect(page.getByText('Stopped')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Start' })).toBeVisible();
});
