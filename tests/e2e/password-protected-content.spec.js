const {test, expect} = require('@playwright/test');
import {login} from './tools/lib/login';

const TEST_TITLE = 'Test Private Page';
const TEST_PARAGRAPH = 'This is a paragraph.';
const TEST_PASSWORD = 'password';

test('check password protected content', async ({page, context}) => {
  // Login.
  await page.goto('./');
  await login(context);

  // Create and navigate to new page.
  await page.goto('./wp-admin/post-new.php?post_type=page');

  // Need to close modal so test can continue.
  await page.waitForSelector('.components-modal__header');
  await page.locator('.components-modal__header button').click();
  await expect(page.locator('.components-modal__header')).toBeHidden();

  // Fill in page title.
  await page.locator('.editor-post-title__input').click();
  await page.locator('h1.editor-post-title').fill(TEST_TITLE);

  // Add a dummy paragraph.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').fill(TEST_PARAGRAPH);

  // Change the page visibility to "Password protected" and set a password.
  await page.locator('button.edit-post-sidebar__panel-tab', {hasText: 'Page'}).click();
  await page.locator('button[aria-label^="Select visibility:"]').click();
  await page.getByLabel('Password protected').check();
  await page.getByPlaceholder('Use a secure password').fill(TEST_PASSWORD);

  // Publish page and navigate to it.
  await page.getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('region', {name: 'Editor publish'}).getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('link', {name: 'View Page', exact: true}).first().click();

  // Make sure that the title and paragraph are not visible, but the form label is.
  await expect(page.getByText(TEST_TITLE)).toBeHidden();
  await expect(page.getByText(TEST_PARAGRAPH)).toBeHidden();
  await expect(page.getByText('To see the content of this page, please enter your password below')).toBeVisible();

  // Fill in the password and submit the form.
  await page.locator('#password-form input').fill(TEST_PASSWORD);
  await page.getByText('Submit').click();

  // Make sure that the title and paragraph are now shown.
  await expect(page.getByText(TEST_TITLE)).toBeVisible();
  await expect(page.getByText(TEST_PARAGRAPH)).toBeVisible();
});
