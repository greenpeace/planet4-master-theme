const { test, expect } = require('@playwright/test');

test('check Explore page', async ({ page }) => {
  await page.goto('/explore');

  await expect(page.locator('h1').filter({ hasText: 'Justice for people and planet' })).toBeVisible();

  // Check Split Two Column block.
  const splitTwoColumn = page.locator('.split-two-column').first();

  await expect(splitTwoColumn.locator('a', { hasText: 'Energy' })).toBeVisible();
  await expect(splitTwoColumn.locator('.split-two-column-item-tag', { hasText: '#renewables' })).toBeVisible();
});
