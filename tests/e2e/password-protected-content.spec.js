import {test, expect} from './tools/lib/test-utils.js';

const TEST_TITLE = 'Test Private Page';
const TEST_PARAGRAPH = 'This is a paragraph.';
const TEST_PASSWORD = 'password';

test.useAdminLoggedIn();

test.skip('check password protected content', async ({page, requestUtils}) => {
  const protectedPost = await requestUtils.rest({
    path: '/wp/v2/posts',
    method: 'POST',
    data: {
      title: TEST_TITLE,
      content: `<p>${TEST_PARAGRAPH}</p>`,
      status: 'publish',
      featured_media: 357,
      password: TEST_PASSWORD,
    },
  });
  await page.goto(protectedPost.link);

  // Make sure that the title and paragraph are not visible, but the form label is.
  await expect(page.getByText(TEST_TITLE)).toBeHidden();
  await expect(page.getByText(TEST_PARAGRAPH)).toBeHidden();
  await expect(page.getByText('To see the content of this page, please enter your password below')).toBeVisible();

  // Fill in the password and submit the form.
  const form = page.locator('form#password-form');
  await form.waitFor();
  await form.getByRole('textbox').fill(TEST_PASSWORD);
  await form.getByRole('button').click();

  // Make sure that the title and paragraph are now shown.
  await expect(page.getByRole('heading', {name: TEST_TITLE})).toBeVisible();
  await expect(page.getByText(TEST_PARAGRAPH)).toBeVisible();
});
