const { test, expect } = require('@playwright/test');

test('check the About Us page', async ({ page }) => {
  await page.goto('/about-us-2');

  await expect(page.getByText('Who we are')).toBeVisible();
});
