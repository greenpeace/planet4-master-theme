import {test, expect} from './tools/lib/test-utils.js';
import {toggleRestAPI, createForm} from './tools/lib/gravity-forms.js';

const CONFIRMATION_MESSAGE = 'This is a dummy confirmation message for testing purposes.';
const TEST_NAME = 'John Doe';
const TEST_EMAIL = 'john.doe@gmail.com';

test.useAdminLoggedIn();

test('check the Gravity Forms confirmation message, text type', async ({requestUtils, page}) => {
  // Enable Gravity Forms rest API.
  await toggleRestAPI({page}, true);

  // Create a new form.
  const createdForm = await createForm({page}, {
    title: 'A test form for confirmation message, text type',
    confirmations: [
      {
        isDefault: true,
        type: 'message',
        message: CONFIRMATION_MESSAGE,
      },
    ],
  });

  // Create a new post with the new form.
  const newPost = await requestUtils.createPost({
    title: 'Gravity Forms test for confirmation message, text type',
    content: `<!-- wp:gravityforms/form {"formId":"${createdForm.id}"} /-->`,
    status: 'publish',
  });
  await page.goto(newPost.link);

  // Fill and submit the form and check the confirmation message text.
  const form = page.locator('form[id^="gform"]');
  await form.getByLabel('Name').fill(TEST_NAME);
  await form.getByLabel('Email').fill(TEST_EMAIL);
  const submitButton = form.getByRole('button', {name: 'Submit'});
  await submitButton.click();
  const confirmationMessage = page.locator('.gform_confirmation_message');
  await expect(confirmationMessage).toBeVisible();
  await expect(confirmationMessage).toContainText(CONFIRMATION_MESSAGE);

  // Check that the entry has been registered as expected.
  await page.goto('./wp-admin/admin.php?page=gf_entries');
  const latestEntry = page.locator('#the-list > tr.entry_row').first();
  await expect(latestEntry).toBeVisible();
  await expect(latestEntry.locator('td[data-colname="Name"]')).toContainText(TEST_NAME);
  await expect(latestEntry.locator('td[data-colname="Email"]')).toContainText(TEST_EMAIL);

  // Delete the form.
  await page.request.delete(`./wp-json/gf/v2/forms/${createdForm.id}`);

  // Disable Gravity Forms rest API.
  await toggleRestAPI({page}, false);
});
