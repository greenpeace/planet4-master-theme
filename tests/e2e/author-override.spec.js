import {test, expect} from './tools/lib/test-utils.js';
import {updatePost} from './tools/lib/post.js';
import {openMetaBoxesTab, closeMetaBoxesTab, closeWelcomeGuideIfPresent} from './tools/lib/editor.js';

const AUTHOR_NAME = 'Alternative Author';

test.useAdminLoggedIn();

test('Test Author override', async ({page, requestUtils}) => {
  test.slow(); // Editor + metabox load can be slow, especially in CI.

  let postId;

  try {
    // Create a new post for the test.
    const newPost = await requestUtils.rest({
      path: '/wp/v2/posts',
      method: 'POST',
      data: {
        title: `Test Author Override ${Date.now()}`,
        content: '<p>This is a test post</p>',
        status: 'publish',
        featured_media: 357,
        categories: [1, 2, 3],
      },
    });
    postId = newPost.id;

    // Go to the edit page for the new post.
    const editUrl = `./wp-admin/post.php?post=${postId}&action=edit`;
    await page.goto(editUrl, {waitUntil: 'domcontentloaded'}); // Default is waituntil: 'load' but that doesn't work for Webkit

    // Wait for the editor itself to be interactive before touching metaboxes,
    // which load later via a separate admin-ajax request.
    await closeWelcomeGuideIfPresent({page});
    await expect(page.locator('.editor-header__settings').getByRole('button', {name: 'Save'})).toBeVisible();

    // Fill in the author override.
    await openMetaBoxesTab({page});
    const overrideControl = page.locator('.edit-post-layout__metaboxes').locator('#p4_author_override');
    await overrideControl.scrollIntoViewIfNeeded();
    await expect(overrideControl).toBeVisible();
    await overrideControl.fill(AUTHOR_NAME);
    await closeMetaBoxesTab({page});

    // Update the post.
    await updatePost({page});

    const postUrl = newPost.link;
    await page.goto(postUrl, {waitUntil: 'domcontentloaded'});

    // Make sure the new author name is properly displayed in the frontend.
    const authorLocator = page.locator('.single-post-author');
    await expect(authorLocator).toBeVisible();
    await expect(authorLocator).toHaveText(AUTHOR_NAME);
    await expect(authorLocator.locator('a')).toHaveCount(0);
  } finally {
    if (postId) {
      try {
        await requestUtils.rest({
          path: `/wp/v2/posts/${postId}`,
          method: 'DELETE',
          data: {force: true},
        });
      } catch (cleanupError) {
        throw new Error(`Failed to clean up post ${postId}:`, cleanupError);
      }
    }
  }
});
