const { test, expect } = require('@playwright/test');

test('check cookies banner', async ({ page }) => {
  await page.goto('./');

  // Check that cookies banner is present.
  const cookiesBanner = page.locator('#set-cookie');
  await expect(cookiesBanner).toBeVisible();

  // Check that the text is the one from the P4 settings.
  const cookiesText = await page.evaluate('window.p4bk_vars.cookies_field');
  await expect(cookiesText).toBeDefined();
  const cookiesContent = await cookiesBanner.locator('.cookies-text').innerHTML();
  await expect(cookiesContent).toContain(cookiesText.replace('&', '&amp;'));

  // Accept all cookies.
  await cookiesBanner.getByText('Accept all cookies').click();

  // Check that the cookies banner disappears.
  await expect(cookiesBanner).toBeHidden();
});
