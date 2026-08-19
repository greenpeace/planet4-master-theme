import {test, expect} from './tools/lib/test-utils.js';
import {updatePost} from './tools/lib/post.js';
import {openMetaBoxesTab, closeMetaBoxesTab} from './tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Related Posts block', async ({page, requestUtils}) => {
  let postId;

  try {
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

    await page.goto(editUrl, {waitUntil: 'domcontentloaded'});

    await openMetaBoxesTab({page});
    await expect(relatedPostsSelect).toBeVisible();
    await relatedPostsSelect.selectOption('Yes');
    await expect(relatedPostsSelect).toHaveValue('yes');
    await closeMetaBoxesTab({page});
    await updatePost({page});

    await page.goto(postUrl, {waitUntil: 'domcontentloaded'});
    const relatedSection = page.locator('.p4-query-loop');
    await expect(relatedSection).toBeVisible();
    await expect(relatedSection.locator('.wp-block-post-template')).not.toHaveCount(0);

    await page.goto(editUrl, {waitUntil: 'domcontentloaded'});

    await openMetaBoxesTab({page});
    await expect(relatedPostsSelect).toBeVisible();
    await relatedPostsSelect.selectOption('No');
    await expect(relatedPostsSelect).toHaveValue('no');
    await closeMetaBoxesTab({page});
    await updatePost({page});

    await page.goto(postUrl, {waitUntil: 'domcontentloaded'});
    await expect(page.locator('.p4-query-loop')).toHaveCount(0);
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
