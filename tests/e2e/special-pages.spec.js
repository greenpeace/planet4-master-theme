const { test, expect } = require('@playwright/test');

import { login } from './tools/lib/login';
import { rest } from './tools/lib/rest';

test('Test special pages (Act and Explore)', async ({ page, context }) => {
  // Login.
  await page.goto('./');
  await login(page, context);

  // Create 2 new pages.
  const actPage = await rest(context, {
    path: './wp-json/wp/v2/pages',
    method: 'POST',
    data: {
      title: 'Act page test',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
    }
  });

  const explorePage = await rest(context, {
    path: './wp-json/wp/v2/pages',
    method: 'POST',
    data: {
      title: 'Explore page test',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
    }
  });

  // Set these 2 new pages as the Act and Explore pages.
  await page.goto('./wp-admin/admin.php?page=planet4_settings_navigation');
  await page.waitForSelector('#act_page');
  await page.waitForSelector('#explore_page');
  await page.selectOption('#act_page', actPage.id.toString());
  await page.selectOption('#explore_page', explorePage.id.toString());
  await page.locator('input[type="submit"]').click();
  await page.locator('.notice-success').isVisible();

  // Go to each page and make sure they have the right datalayer "pageType" value.
  await page.goto(`./${actPage.slug}`);
  let dataLayer = await page.evaluate(() => window.dataLayer || []);
  if (dataLayer.length > 0) {
    expect(dataLayer[0].pageType).toBe('Act');
  }

  await page.goto(`./${explorePage.slug}`);
  dataLayer = await page.evaluate(() => window.dataLayer || []);
  if (dataLayer.length > 0) {
    expect(dataLayer[0].pageType).toBe('Explore');
  }
});
