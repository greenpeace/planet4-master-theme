const {test, expect} = require('@playwright/test');
import {login} from './tools/lib/login';
import {rest} from './tools/lib/rest';

test('Test adding a Comment to a Post', async ({page, context}) => {
  test.setTimeout(240 * 1000);
  await page.goto('./');
  await login(context);

  // Post creation using rest api since we already have a test for Post creation
  const newPost = await rest(context, {
    path: './wp-json/wp/v2/posts',
    method: 'POST',
    data: {
      title: 'Test Post',
      content: `
        <!-- wp:paragraph -->
          <p>This is a test post</p>
        <!-- /wp:paragraph -->
      `,
      status: 'publish',
    },
  });

  await page.goto(newPost.link);
  const commentTextbox = page.getByPlaceholder('Your Comment');
  commentTextbox.click();
  commentTextbox.fill('Nice Post');
  await page.locator('label#gdpr-comments-label').click();
  if (await page.getByRole('button', {name: 'Post comment'}).isEnabled()) {
    await page.getByRole('button', {name: 'Post comment'}).click();
  }
  await page.goto('./wp-admin');
  await page.locator('a.menu-icon-comments').click();
  const comment = await page.innerText('.comment.approved p');

  expect(comment).toEqual('Nice Post');
});
