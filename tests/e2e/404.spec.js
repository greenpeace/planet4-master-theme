const { test, expect } = require('@playwright/test');

test('check the 404 page', async ({ page }) => {
  const response = await page.goto('/thispagereallywillnotexist');

  expect(response.status()).toEqual(404);

  await expect(page.locator('input[aria-label="Search"]')).toBeVisible();
});
