import {test, expect} from './tools/lib/test-utils.js';
import {updatePost} from './tools/lib/post.js';
import {openMetaBoxesTab, closeMetaBoxesTab} from './tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Related Posts block', async ({page, requestUtils}) => {
  let postId;

  try {
    // 1. Create isolated test post.
    const newPost = await requestUtils.rest({
      path: '/wp/v2/posts',
      method: 'POST',
      data: {
        title: `Test post for Related Posts ${Date.now()}`,
        content: 'This is a test post',
        status: 'publish',
        featured_media: 357,
        tags: [7], // tag: Renewables
        categories: [2], // category: Energy
      },
    });
    postId = newPost.id;

    const editUrl = `./wp-admin/post.php?post=${postId}&action=edit`;
    const postUrl = newPost.link;
    const relatedPostsSelect = page
      .locator('.edit-post-layout__metaboxes')
      .getByRole('combobox', {name: 'Include Related Posts'});

    // 2. Open editor.
    await page.goto(editUrl, {waitUntil: 'domcontentloaded'});

    // 3. Enable Related Posts.
    await openMetaBoxesTab({page});
    await expect(relatedPostsSelect).toBeVisible();
    await relatedPostsSelect.selectOption('Yes');
    await expect(relatedPostsSelect).toHaveValue('yes');
    await closeMetaBoxesTab({page});
    await updatePost({page});

    // 4. Verify frontend.
    await page.goto(postUrl, {waitUntil: 'domcontentloaded'});
    const relatedSection = page.locator('.p4-query-loop');
    await expect(relatedSection).toBeVisible();
    await expect(relatedSection.locator('.wp-block-post-template')).not.toHaveCount(0);

    // 5. Reopen editor.
    await page.goto(editUrl, {waitUntil: 'domcontentloaded'});

    // 6. Disable Related Posts.
    await openMetaBoxesTab({page});
    await expect(relatedPostsSelect).toBeVisible();
    await relatedPostsSelect.selectOption('No');
    await expect(relatedPostsSelect).toHaveValue('no');
    await closeMetaBoxesTab({page});
    await updatePost({page});

    // 7. Verify frontend.
    await page.goto(postUrl, {waitUntil: 'domcontentloaded'});
    await expect(page.locator('.p4-query-loop')).toHaveCount(0);
  } finally {
    // 8. Always clean up, without masking the original test failure.
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
