import {test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addActionsListBlock,
  checkActionsListBlock,
  addActionsListBlockWithManualOverride,
  checkActionsListBlockWithManualOverride,
} from '../tools/lib/actions-list.js';
import {isNewIAEnabled} from '../tools/lib/check-new-ia.js';

test.useAdminLoggedIn();

test.describe('Test Actions List block', () => {
  // This is the default layout, so we don't need to select it manually.
  test('Test the Grid layout', async ({page, admin, editor}) => {
    test.slow();
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Actions List Grid Layout', postType: 'page'});

    // Add Actions List block.
    await addActionsListBlock(page);

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Check the Actions List block.
    await checkActionsListBlock(page);
  });

  test('Test the Manual Override', async ({page, admin, editor, requestUtils}) => {
    const isNewIA = await isNewIAEnabled(admin, page);

    // Get the "Take Action" page ID from the Analytics settings.
    await admin.visitAdminPage('admin.php', 'page=planet4_settings_analytics');
    const takeActionPageId = await page.locator('#take_action_page').inputValue();

    // Skip Test if the new IA is not enabled or if the "Take Action" page ID is not available.
    test.skip(!isNewIA || !takeActionPageId, 'The new IA must be enabled to run this test.');

    // Create 2 actions to be selected via the Manual Override.
    const regularActionTitles = [
      'Test Actions List Manual Override Action 1',
      'Test Actions List Manual Override Action 2',
    ];

    for (const title of regularActionTitles) {
      await requestUtils.rest({
        path: '/wp/v2/p4_action',
        method: 'POST',
        data: {title, status: 'publish'},
      });
    }

    // Create 2 actions as children of the "Take Action" page.
    const childActionTitles = [
      'Test Actions List Manual Override Child Action 1',
      'Test Actions List Manual Override Child Action 2',
    ];

    for (const title of childActionTitles) {
      await requestUtils.rest({
        path: '/wp/v2/p4_action',
        method: 'POST',
        data: {title, status: 'publish', parent: takeActionPageId},
      });
    }

    // Combine all action titles if needed downstream.
    const actionTitles = [...regularActionTitles, ...childActionTitles];

    // Create a page to hold the Actions List block.
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Actions List, Manual Override', postType: 'page'});

    // Add a Actions List block using the Manual Override to select the 2 actions created above.
    await addActionsListBlockWithManualOverride(page, actionTitles);

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Check that the block displays correctly in the frontend.
    await checkActionsListBlockWithManualOverride(page, actionTitles);
  });
});
