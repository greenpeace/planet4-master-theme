import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('Test adding a Comment to a Post', async ({page, requestUtils, admin}) => {
  // Post creation using rest api since we already have a test for Post creation
  const newPost = await requestUtils.createPost({
    title: 'Test Post',
    content: `
      <!-- wp:paragraph -->
        <p>This is a test post</p>
      <!-- /wp:paragraph -->
    `,
    status: 'publish',
  });

  await page.goto(newPost.link);
  await page.getByPlaceholder('Your Comment').fill('Nice Post');
  await page.locator('label#gdpr-comments-label').click();
  if (await page.getByRole('button', {name: 'Post comment'}).isEnabled()) {
    await page.getByRole('button', {name: 'Post comment'}).click();
  }
  await admin.visitAdminPage('index.php');
  await page.locator('a.menu-icon-comments').click();
  const comment = await page.innerText('.comment.approved p');

  expect(comment).toEqual('Nice Post');
});
