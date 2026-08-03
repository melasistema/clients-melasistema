import { expect, test } from '@playwright/test';
import { login } from './helpers';
import { OWNER } from './prepare.mjs';

// Authentication is the gate in front of the whole app — pin that valid
// credentials get in, bad ones are rejected, and unauthenticated visitors are
// bounced to login.
test.describe('authentication', () => {
    test('owner can log in with valid credentials', async ({ page }) => {
        await login(page);
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('wrong password is rejected and stays on login', async ({ page }) => {
        await page.goto('/login');
        await page.locator('#email').fill(OWNER.email);
        await page.locator('#password').fill('not-the-password');
        await page.getByRole('button', { name: 'Log in' }).click();

        // The validation error renders and we never reach the dashboard.
        await expect(page.getByText(/These credentials do not match|credenziali/i)).toBeVisible();
        await expect(page).toHaveURL(/\/login/);
    });

    test('guests are redirected to login', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });
});
