import {test, expect} from './tools/lib/test-utils.js';
import {updatePost} from './tools/lib/post.js';
import {openMetaBoxesTab, closeMetaBoxesTab} from './tools/lib/editor.js';

const AUTHOR_NAME = 'Alternative Author';

test.useAdminLoggedIn();

test('Test Author override', async ({page, requestUtils}) => {
  // Create a new post for the test.
  const newPost = await requestUtils.rest({
    path: '/wp/v2/posts',
    method: 'POST',
    data: {
      title: 'Test Author Override',
      content: '<p>This is a test post</p>',
      status: 'publish',
      featured_media: 357,
      categories: [1, 2, 3],
    },
  });

  // Go to the edit page for the new post.
  const editUrl = `./wp-admin/post.php?post=${newPost.id}&action=edit`;
  await page.goto(editUrl, {waitUntil: 'domcontentloaded'}); // Default is waituntil: 'load' but that doesn't work for Webkit

  // Fill in the author override.
  await openMetaBoxesTab({page});
  const overrideControl = page.locator('.edit-post-layout__metaboxes').locator('#p4_author_override');
  await overrideControl.scrollIntoViewIfNeeded();
  await expect(overrideControl).toBeVisible();
  await overrideControl.fill(AUTHOR_NAME);
  await page.waitForTimeout(1000); // letting metabox post query finish
  await closeMetaBoxesTab({page});

  // Update the post.
  await page.waitForTimeout(1000);
  await updatePost({page});
  const postUrl = newPost.link;
  await page.goto(postUrl);

  // Make sure the new author name is properly displayed in the frontend.
  const authorLocator = page.locator('.single-post-author');
  expect(authorLocator).toBeVisible();
  expect(await authorLocator.innerText()).toBe(AUTHOR_NAME);
  expect(authorLocator.locator('a')).toHaveCount(0);
});
