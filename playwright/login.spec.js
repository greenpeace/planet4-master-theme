const { test, expect } = require('@playwright/test');

test('check the login functionality', async ({ page, httpCredentials }) => {
  await page.goto('/wp-admin/');

  await page.type('#user_login', httpCredentials.username);
  await page.type('#user_pass', httpCredentials.password);
  await page.getByText('Log In').click();

  await expect(page.locator('#wpadminbar')).toBeVisible();
  await expect(page.locator('h1', { hasText: 'Dashboard' })).toBeVisible();
});
