import {test, expect} from './tools/lib/test-utils.js';

test('check the 404 page', async ({page}) => {
  const response = await page.goto('./thispagereallywillnotexist');

  // Check the page status.
  expect(response.status()).toEqual(404);

  // Check the page text.
  const settingsText = await page.evaluate('window.p4_vars.options.page_text_404');
  expect(settingsText).toBeDefined();
  const settingsTextUpdated = settingsText.replace(/\s+/g, ' ').replaceAll('&', '&amp;');
  const pageContent = await page.locator('.speech-bubble').innerHTML();
  expect(pageContent.replace(/\s+/g, ' ')).toContain(settingsTextUpdated.trim());

  // Check the page image background.
  const settingsImage = await page.evaluate('window.p4_vars.options.page_bg_image_404');
  expect(settingsImage).toBeDefined();
  await expect(page.locator('.page-header-background img')).toHaveAttribute('src', settingsImage);

  // Make sure the search input is there.
  await expect(page.locator('form.search-form input')).toBeVisible();
});
