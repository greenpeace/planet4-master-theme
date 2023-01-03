const { test, expect } = require('@playwright/test');

test('check the Act page', async ({ page }) => {
  await page.goto('/act');

  await expect(page.locator('.covers-block')).toBeVisible();
  await expect(page.locator('.cover-card').first()).toBeVisible();

  await expect(page.locator('.cover-card-tag', { hasText: 'Consumption' }).first()).toBeVisible();
  await expect(page.locator('.cover-card-tag', { hasText: 'renewables' }).first()).toBeVisible();
  await expect(page.locator('.cover-card-tag', { hasText: 'Climate' }).first()).toBeVisible();
});
