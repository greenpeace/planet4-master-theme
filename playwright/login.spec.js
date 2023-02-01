const { test, expect } = require('@playwright/test');

test('check the login functionality', async ({ page }) => {
  await page.goto('/wp-admin/');

  await page.type('#user_login', 'admin');
  await page.type('#user_pass', 'admin');
  await page.getByText('Log In').click();

  await expect(page.locator('#wpadminbar')).toBeVisible();
  await expect(page.locator('h1', { hasText: 'Dashboard' })).toBeVisible();
});
