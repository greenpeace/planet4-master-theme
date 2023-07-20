import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('Test special pages (Act and Explore)', async ({page, requestUtils, admin}) => {
  // Check if new IA is enabled, in which case the Act and Explore pages have been removed.
  await admin.visitAdminPage('admin.php', 'page=planet4_settings_navigation');
  await page.waitForSelector('#new_ia');
  const isNewIAEnabled = await page.locator('#new_ia').isChecked();
  if (isNewIAEnabled) {
    await expect(page.locator('#act_page')).toBeHidden();
    await expect(page.locator('#explore_page')).toBeHidden();
  } else {
    // Create 2 new pages.
    const actPage = await requestUtils.createPage({
      title: 'Act page test',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
    });
    const explorePage = await requestUtils.createPage({
      title: 'Explore page test',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
    });

    // Reload the page to make sure that the new pages are available.
    await page.reload();

    // Save previous values to restore them after the test.
    const previousActPage = await page.locator('#act_page').inputValue();
    const previousExplorePage = await page.locator('#explore_page').inputValue();

    // Set the 2 new pages instead.
    await page.selectOption('#act_page', actPage.id.toString());
    await page.selectOption('#explore_page', explorePage.id.toString());
    await page.locator('input[type="submit"]').click();
    await page.locator('.notice-success').isVisible();

    // Go to each page and make sure they have the right datalayer "pageType" value.
    await page.goto(`./${actPage.slug}`);
    let dataLayer = await page.evaluate(() => window.dataLayer || []);
    if (dataLayer.length > 0) {
      const {pageType} = dataLayer.find(data => data.pageType !== undefined);
      expect(pageType).toBe('Act');
    }

    await page.goto(`./${explorePage.slug}`);
    dataLayer = await page.evaluate(() => window.dataLayer || []);
    if (dataLayer.length > 0) {
      const {pageType} = dataLayer.find(data => data.pageType !== undefined);
      expect(pageType).toBe('Explore');
    }

    // Reset the Act and Explore pages.
    await admin.visitAdminPage('admin.php', 'page=planet4_settings_navigation');
    await page.waitForSelector('#act_page');
    await page.waitForSelector('#explore_page');
    await page.selectOption('#act_page', previousActPage);
    await page.selectOption('#explore_page', previousExplorePage);
    await page.locator('input[type="submit"]').click();
    await page.locator('.notice-success').isVisible();
  }
});
