const { test, expect } = require('@playwright/test');

test('check cookies banner', async ({ page }) => {
  await page.goto('/');

  // Check that cookies banner is present.
  const cookiesBanner = page.locator('#set-cookie');
  await expect(cookiesBanner).toBeVisible();

  // Accept all cookies.
  await cookiesBanner.getByText('Accept all cookies').click();

  // Check that the cookies banner disappears.
  await expect(cookiesBanner).toBeHidden();
});
