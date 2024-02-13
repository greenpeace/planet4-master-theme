import {test, expect} from './tools/lib/test-utils.js';

test('check the country selector behaviour', async ({page}) => {
  await page.goto('./');
  await expect(page.locator('.country-selector-toggle-container')).toBeVisible();
  await page.locator('button.country-control-toggle').click();
  await expect(page.locator('.countries-list')).toBeVisible();
});
