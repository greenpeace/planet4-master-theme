import {expect} from './test-utils.js';

/**
 * Close the cookies box if needed, by accepting them.
 * If it's there it prevents us from interacting with the rest of the page.
 *
 * @param {import('@playwright/test').Page} page - The Playwright page instance.
 */
async function acceptCookies(page) {
  const cookiesBanner = page.locator('#set-cookie');
  if (!(await cookiesBanner.isVisible())) {
    return;
  }
  await cookiesBanner.getByText('Accept all cookies').click();
  await expect(cookiesBanner).toBeHidden();
}

export {acceptCookies};
