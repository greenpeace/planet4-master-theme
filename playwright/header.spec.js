const { test, expect } = require('@playwright/test');

test('check header', async ({ page }) => {
  await page.goto('/act');

  await expect(page.locator('.page-header')).toBeVisible();
  await expect(page.locator('.page-header-title')).toBeVisible();
});
