const { test, expect } = require('@playwright/test');

test('check the Copyright page', async ({ page }) => {
  await page.goto('/copyright');

  await expect(page.locator('h1', { hasText: 'Copyright' })).toBeVisible();
});
