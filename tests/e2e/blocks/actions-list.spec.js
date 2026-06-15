import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addActionsListBlock,
  checkActionsListBlock,
  addActionsListBlockWithManualOverride,
  checkActionsListBlockWithManualOverride,
  checkActionsListBlockCarouselLayout,
} from '../tools/lib/actions-list.js';
import {isNewIAEnabled} from '../tools/lib/check-new-ia.js';

test.useAdminLoggedIn();

test.describe('Test Actions List block', () => {
  test.describe.configure({mode: 'serial'});
  // This is the default layout, so we don't need to select it manually.
  test('Test the Grid layout', async ({page, admin, editor}) => {
    test.slow();
    const uniqueTitle = `Test Actions List Grid Layout ${Date.now()}`;

    await createPostWithFeaturedImage({page, admin, editor}, {title: uniqueTitle, postType: 'page'});
    await addActionsListBlock({page, editor});
    await publishPostAndVisit({page, editor});
    await checkActionsListBlock(page);
  });

  test('Test the Manual Override', async ({page, admin, editor, requestUtils}) => {
    const isNewIA = await isNewIAEnabled(admin, page);

    // Get the "Take Action" page ID from the Analytics settings.
    await admin.visitAdminPage('admin.php', 'page=planet4_settings_analytics');
    const takeActionPageInput = page.locator('#take_action_page');
    await expect(takeActionPageInput).toBeVisible();
    const takeActionPageId = await takeActionPageInput.inputValue();

    test.skip(!isNewIA || !takeActionPageId, 'The new IA must be enabled or the "Take Action" page ID must be available to run this test.');

    const createdActionIds = [];

    try {
      const suffix = Date.now();

      // Create 2 actions to be selected via the Manual Override.
      const regularActionTitles = [
        `Test Actions List Manual Override Action 1 ${suffix}`,
        `Test Actions List Manual Override Action 2 ${suffix}`,
      ];

      for (const title of regularActionTitles) {
        const created = await requestUtils.rest({
          path: '/wp/v2/p4_action',
          method: 'POST',
          data: {title, status: 'publish'},
        });
        createdActionIds.push(created.id);
      }

      // Create 2 actions as children of the "Take Action" page.
      const childActionTitles = [
        `Test Actions List Manual Override Child Action 1 ${suffix}`,
        `Test Actions List Manual Override Child Action 2 ${suffix}`,
      ];

      for (const title of childActionTitles) {
        const created = await requestUtils.rest({
          path: '/wp/v2/p4_action',
          method: 'POST',
          data: {title, status: 'publish', parent: takeActionPageId},
        });
        createdActionIds.push(created.id);
      }

      const actionTitles = [...regularActionTitles, ...childActionTitles];
      const pageTitle = `Test Actions List, Manual Override ${suffix}`;

      await createPostWithFeaturedImage({page, admin, editor}, {title: pageTitle, postType: 'page'});
      await addActionsListBlockWithManualOverride({page, editor}, actionTitles);
      await publishPostAndVisit({page, editor});
      await checkActionsListBlockWithManualOverride(page, actionTitles);
    } finally {
      // Clean up REST-created actions even if the test failed midway.
      for (const id of createdActionIds) {
        try {
          await requestUtils.rest({
            path: `/wp/v2/p4_action/${id}`,
            method: 'DELETE',
            data: {force: true},
          });
        } catch (cleanupError) {
          throw new Error(`Failed to clean up action ${id}:`, cleanupError);
        }
      }
    }
  });

  test('Test the Carousel layout', async ({page, admin, editor}) => {
    const uniqueTitle = `Test Actions List, Carousel Layout ${Date.now()}`;

    await createPostWithFeaturedImage({page, admin, editor}, {title: uniqueTitle, postType: 'page'});
    await addActionsListBlock({page, editor}, 'Carousel');
    await publishPostAndVisit({page, editor});
    await checkActionsListBlockCarouselLayout(page);
  });
});
