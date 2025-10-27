// import {test, expect} from '../tools/lib/test-utils.js';
import {test, expect} from './tools/lib/test-utils.js';
import {updatePost} from './tools/lib/post.js';

const AUTHOR_NAME = 'Alternative Author';

test.useAdminLoggedIn();

// test.skip(({browserName}) => browserName === 'webkit', 'Skip on WebKit due to unsupported setting');

test('Test Author override', async ({page, requestUtils}) => {
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
  const editUrl = `./wp-admin/post.php?post=${newPost.id}&action=edit`;
  const postUrl = newPost.link;

  await page.goto(editUrl);

  const overrideControl = page.locator('.edit-post-layout__metaboxes').locator('#p4_author_override');
  await expect(overrideControl).toBeVisible();
  await overrideControl.fill(AUTHOR_NAME);

  await page.waitForTimeout(1000);
  await updatePost({page});
  await page.goto(postUrl);

  const authorLocator = page.locator('.single-post-author');
  expect(authorLocator).toBeVisible();
  expect(await authorLocator.innerText()).toBe(AUTHOR_NAME);
  expect(authorLocator.locator('a')).toHaveCount(0);
});
