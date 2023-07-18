import {test, expect} from './tools/lib/test-utils.js';

test('check search works', async ({page}) => {
  await page.goto('./');

  const searchBox = page.getByPlaceholder('Search');
  await searchBox.click();
  await searchBox.type('climate');
  await page.keyboard.press('Enter');

  const searchResult = await page.innerHTML('.result-statement');
  const searchTags = await page.locator('.search-result-item-headline').allInnerTexts();

  expect(searchResult).toContain('climate');
  expect(searchTags).toContain('#Climate');
});
