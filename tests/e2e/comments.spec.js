import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('Test adding a Comment to a Post', async ({page, admin, requestUtils}) => {
  // Disable the Cloudflare Turnstile captcha.
  // As the captcha is an iframe, we cannot test clicking on it.
  await page.goto('./wp-admin/options-discussion.php');
  await page.uncheck('#planet4_cloudflare_turnstile');

  const newPost = await requestUtils.rest({
    path: '/wp/v2/posts',
    method: 'POST',
    data: {
      title: 'Test post',
      content: '<p>This is a test post</p>',
      status: 'publish',
      featured_media: 357,
    },
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
