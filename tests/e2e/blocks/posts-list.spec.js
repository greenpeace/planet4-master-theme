import {test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addPostsListBlock,
  addPostsListBlockWithManualOverride,
  checkPostsListBlock,
  checkPostsListBlockWithManualOverride,
  checkPostsListBlockCarouselLayout,
} from '../tools/lib/posts-list.js';

test.useAdminLoggedIn();

test.describe('Test Posts List block', () => {
  test.describe.configure({mode: 'serial'});
  // This is the default layout, so we don't need to select it manually.
  test('Test the List layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: `Test Posts List, List Layout ${Date.now()}`, postType: 'page'});

    // Add a Posts List block with the wanted attributes.
    await addPostsListBlock({page, editor});

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Check that the block displays correctly in the frontend.
    await checkPostsListBlock(page, 'list');
  });

  test('Test the Carousel layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: `Test Posts List, Carousel Layout ${Date.now()}`, postType: 'page'});

    // Add a Posts List block with the Carousel layout.
    await addPostsListBlock({page, editor}, 'Carousel');

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Test that the block is displayed as expected in the frontend.
    await checkPostsListBlockCarouselLayout(page);
  });

  test('Test the Grid layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: `Test Posts List, Grid Layout ${Date.now()}`, postType: 'page'});

    // Add a Posts List block with the wanted attributes.
    await addPostsListBlock({page, editor}, 'Grid');

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Test that the block is displayed as expected in the frontend.
    await checkPostsListBlock(page, 'grid');
  });

  test('Test the Manual Override', async ({page, admin, editor, requestUtils}) => {
    const createdPostIds = [];

    try {
      const suffix = Date.now();

      // Create 4 posts to be selected via the Manual Override.
      const postTitles = Array.from(
        {length: 4},
        (_, i) => `Test Posts List Manual Override Post 1 ${i + 1} ${suffix}`
      );

      for (const title of postTitles) {
        const created = await requestUtils.rest({
          path: '/wp/v2/posts',
          method: 'POST',
          data: {title, status: 'publish'},
        });
        createdPostIds.push(created.id);
      }

      // Create a page to hold the Posts List block.
      await createPostWithFeaturedImage({page, admin, editor}, {title: `Test Posts List, Manual Override ${suffix}`, postType: 'page'});

      // Add a Posts List block using the Manual Override to select the 4 posts created above.
      await addPostsListBlockWithManualOverride({page, editor}, postTitles);

      // Publish page.
      await publishPostAndVisit({page, editor});

      // Check that the block displays correctly in the frontend.
      await checkPostsListBlockWithManualOverride(page, postTitles);
    } finally {
      // Always clean up the posts created via REST, even if the test failed.
      for (const id of createdPostIds) {
        try {
          await requestUtils.rest({
            path: `/wp/v2/posts/${id}`,
            method: 'DELETE',
            data: {force: true},
          });
        } catch (cleanupError) {
          throw new Error(`Failed to clean up post ${id}:`, cleanupError);
        }
      }
    }
  });
});
