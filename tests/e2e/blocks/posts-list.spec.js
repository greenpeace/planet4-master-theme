import {test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addPostsListBlock,
  addPostsListBlockWithManualOverride,
  checkPostsListBlock,
  checkPostsListBlockWithManualOverride,
} from '../tools/lib/posts-list.js';

test.useAdminLoggedIn();

test.describe('Test Posts List block', () => {
  // This is the default layout, so we don't need to select it manually.
  test('Test the List layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Posts List, List Layout', postType: 'page'});

    // Add a Posts List block with the wanted attributes.
    await addPostsListBlock(page);

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Check that the block displays correctly in the frontend.
    await checkPostsListBlock(page, 'list');
  });

  test('Test the Grid layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Posts List, Grid Layout', postType: 'page'});

    // Add a Posts List block with the wanted attributes.
    await addPostsListBlock(page, 'Grid');

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Test that the block is displayed as expected in the frontend.
    await checkPostsListBlock(page, 'grid');
  });

  test('Test the Manual Override', async ({page, admin, editor, requestUtils}) => {
    // Create 4 posts to be selected via the Manual Override.
    const postTitles = [
      'Test Posts List Manual Override Post 1',
      'Test Posts List Manual Override Post 2',
      'Test Posts List Manual Override Post 3',
      'Test Posts List Manual Override Post 4',
    ];

    for (const title of postTitles) {
      await requestUtils.rest({
        path: '/wp/v2/posts',
        method: 'POST',
        data: {title, status: 'publish'},
      });
    }

    // Create a page to hold the Posts List block.
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Posts List, Manual Override', postType: 'page'});

    // Add a Posts List block using the Manual Override to select the 4 posts created above.
    await addPostsListBlockWithManualOverride(page, postTitles);

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Check that the block displays correctly in the frontend.
    await checkPostsListBlockWithManualOverride(page, postTitles);
  });
});
