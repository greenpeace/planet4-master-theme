const { test, expect } = require('@playwright/test');

test('check the 404 page', async ({ page }) => {
  const response = await page.goto('/thispagereallywillnotexist');

  // Check the page status.
  expect(response.status()).toEqual(404);

  // Check the page text.
  const settingsText = await page.evaluate('window.p4bk_vars.page_text_404');
  await expect(settingsText).toBeDefined();
  const pageContent = await page.locator('.speech-bubble').innerHTML();
  await expect(pageContent).toContain(settingsText.replace(/[\r]/g, ''));

  // Check the page image background.
  const settingsImage = await page.evaluate('window.p4bk_vars.page_bg_image_404');
  await expect(settingsImage).toBeDefined();
  await expect(page.locator('.page-header-background img')).toHaveAttribute('src', settingsImage);

  // Make sure the search input is there.
  await expect(page.locator('input[aria-label="Search"]')).toBeVisible();
});
