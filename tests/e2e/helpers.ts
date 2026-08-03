import { expect, type Page } from '@playwright/test';
import { OWNER } from './prepare.mjs';

// Log in as the owner account global-setup provisioned, landing on the dashboard.
// Shared by the specs so each starts from an authenticated session.
export async function login(page: Page): Promise<void> {
    await page.goto('/login');
    await page.locator('#email').fill(OWNER.email);
    await page.locator('#password').fill(OWNER.password);
    await page.getByRole('button', { name: 'Log in' }).click();
    await expect(page).toHaveURL(/\/dashboard/);
}
