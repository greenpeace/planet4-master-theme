import {test, expect} from './tools/lib/test-utils.js';

const TEST_TITLE = 'Test Private Page';
const TEST_PARAGRAPH = 'This is a paragraph.';
const TEST_PASSWORD = 'password';

test.useAdminLoggedIn();

test('check password protected content', async ({page, requestUtils}) => {
  test.slow();

  let postId;

  try {
    const protectedPost = await requestUtils.rest({
      path: '/wp/v2/posts',
      method: 'POST',
      data: {
        title: TEST_TITLE,
        content: `<p>${TEST_PARAGRAPH}</p>`,
        status: 'publish',
        password: TEST_PASSWORD,
      },
    });

    postId = protectedPost.id;

    await page.goto(protectedPost.link);

    await expect(page.getByText(TEST_TITLE)).toBeHidden();
    await expect(page.getByText(TEST_PARAGRAPH)).toBeHidden();

    await expect(
      page.getByText(
        'To see the content of this page, please enter your password below'
      )
    ).toBeVisible();

    const form = page.locator('form#password-form');

    await form.getByRole('textbox').fill(TEST_PASSWORD);
    await form.getByRole('textbox').press('Enter');

    await expect(
      page.getByRole('heading', {name: TEST_TITLE})
    ).toBeVisible();

    await expect(page.getByText(TEST_PARAGRAPH)).toBeVisible();

  } finally {
    if (postId) {
      await requestUtils.rest({
        path: `/wp/v2/posts/${postId}`,
        method: 'DELETE',
        data: {
          force: true,
        },
      });
    }
  }
});
