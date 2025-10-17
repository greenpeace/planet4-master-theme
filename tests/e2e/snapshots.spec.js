import {test, expect} from  './tools/lib/test-utils.js';
import {readSiteMap} from './tools/setup/sitemap.js';

test('Snapshots', async ({page}) => {
  const urls = await readSiteMap();
  for (const url of urls) {
    await page.goto(url);
    await expect(page).toHaveScreenshot();
  }
});
