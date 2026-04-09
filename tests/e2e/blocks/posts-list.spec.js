import {test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {addPostsListBlock, checkPostsListBlock} from '../tools/lib/posts-list.js';

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
});
