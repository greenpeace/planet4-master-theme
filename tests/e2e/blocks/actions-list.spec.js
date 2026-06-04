import {test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addActionsListBlock,
  checkActionsListBlock,
  addActionsListBlockWithManualOverride,
  checkActionsListBlockWithManualOverride,
} from '../tools/lib/actions-list.js';

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
    // Fetch the "Take Action" parent page ID
    const takeActionPages = await requestUtils.rest({
      path: '/wp/v2/pages',
      method: 'GET',
      params: {slug: 'take-action'},
    });
    const takeActionPageId = takeActionPages[0].id;

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
