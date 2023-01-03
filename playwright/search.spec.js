const { test, expect } = require('@playwright/test');

test('check search works', async ({ page }) => {
  await page.goto('/');

  await page.type('#search_input', 'climate');
  await page.keyboard.press('Enter');
  await page.locator('h1', { hasText: 'for \'climate\'' }).waitFor('visible');
  await expect(page.locator('.search-result-item-headline', { hasText: '#Climate' }).first()).toBeVisible();
});
