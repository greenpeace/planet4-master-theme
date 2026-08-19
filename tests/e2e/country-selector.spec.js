import {test, expect} from './tools/lib/test-utils.js';

test('check the country selector behaviour', async ({page}) => {
  await page.goto('./');
  await page.waitForLoadState('domcontentloaded');

  const toggleContainer = page.locator('.country-selector-toggle-container');
  await expect(toggleContainer).toBeVisible();

  const toggleButton = page.locator('button.country-control-toggle');
  await toggleButton.scrollIntoViewIfNeeded();
  await expect(toggleButton).toBeVisible();
  await expect(toggleButton).toBeEnabled();

  await toggleButton.click();

  const countriesList = page.locator('.countries-list');

  // WebKit fix for click() event
  try {
    await expect(countriesList).toBeVisible({timeout: 5000});
  } catch {
    await toggleButton.dispatchEvent('click');
    await expect(countriesList).toBeVisible();
  }
});
