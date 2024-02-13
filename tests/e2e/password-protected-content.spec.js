import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit} from './tools/lib/post.js';

const TEST_TITLE = 'Test Private Page';
const TEST_PARAGRAPH = 'This is a paragraph.';
const TEST_PASSWORD = 'password';

test.useAdminLoggedIn();

test('check password protected content', async ({page, admin, editor}) => {
  await admin.createNewPost({postType: 'page', title: TEST_TITLE, legacyCanvas: true});

  // Add a dummy paragraph.
  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type(TEST_PARAGRAPH);

  await editor.openDocumentSettingsSidebar();
  await editor.canvas.getByRole('region', {name: 'Editor settings'})
    .getByRole('button', {name: 'Page'})
    .click();

  // Change the page visibility to "Password protected" and set a password.
  await editor.canvas.getByRole('button', {name: 'Select visibility: Public'}).click();
  await editor.canvas.getByRole('radio', {name: 'Password protected'}).click();
  await editor.canvas.getByPlaceholder('Use a secure password').fill(TEST_PASSWORD);

  // Publish page and navigate to it.
  await publishPostAndVisit({page, editor});

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
