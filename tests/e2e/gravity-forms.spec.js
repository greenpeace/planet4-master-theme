import {test, expect} from './tools/lib/test-utils.js';
import {toggleRestAPI, createForm} from './tools/lib/gravity-forms.js';

const CONFIRMATION_MESSAGE = 'This is a dummy confirmation message for testing purposes.';
const TEST_FIRST_NAME = 'Jon';
const TEST_LAST_NAME = 'Snow';
const TEST_EMAIL = 'jon.snow@gmail.com';

test.useAdminLoggedIn();

test.describe('Gravity Forms tests', () => {
  test.describe.configure({mode: 'serial'});
  const testId = Math.floor(Math.random() * 10000); //NOSONAR
  let createdForm;

  test.beforeAll(async ({browser}) => {
    const page = await browser.newPage();
    // Enable Gravity Forms rest API.
    await toggleRestAPI({page}, true);

    // Create a new form.
    createdForm = await createForm({page}, {
      title: `Gravity Forms test form - ${testId}`,
    });
    await page.close();
  });

  test.afterAll(async ({browser}) => {
    const page = await browser.newPage();
    // Delete the form.
    await page.request.delete(`./wp-json/gf/v2/forms/${createdForm.id}?force=1`);

    // Disable Gravity Forms rest API.
    await toggleRestAPI({page}, false);
  });

  test('check the confirmation message, text type', async ({requestUtils, page}) => {
    // Update the form's default confirmation text.
    await page.goto(`./wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=confirmation&id=${createdForm.id}`);
    await page.locator('#the-list > tr:first-child').hover();
    await page.getByRole('link', {name: 'Edit', exact: true}).click();
    await page.frameLocator('#_gform_setting_message_ifr').locator('#tinymce').fill(CONFIRMATION_MESSAGE);
    await page.getByRole('button', {name: 'Save Confirmation'}).click();
    await expect(page.locator('.gforms_note_success')).toBeVisible();

    // Create a new post with the new form.
    const newPost = await requestUtils.createPost({
      title: `Gravity Forms test post - ${testId}`,
      content: `<!-- wp:gravityforms/form {"formId":"${createdForm.id}"} /-->`,
      status: 'publish',
    });
    await page.goto(newPost.link);

    // Fill and submit the form and check the confirmation message text.
    const form = page.locator('form[id^="gform"]');
    await form.getByLabel('First name').fill(TEST_FIRST_NAME);
    await form.getByLabel('Last name').fill(TEST_LAST_NAME);
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
    await expect(latestEntry.locator('td[data-colname="First name"]')).toContainText(TEST_FIRST_NAME);
    await expect(latestEntry.locator('td[data-colname="Last name"]')).toContainText(TEST_LAST_NAME);
    await expect(latestEntry.locator('td[data-colname="Email"]')).toContainText(TEST_EMAIL);
  });

  test('check the Hubspot feeds', async ({page}) => {
    // Add a Hubspot feed.
    await page.goto(`./wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=gravityformshubspot&id=${createdForm.id}`);
    const createFeedLink = page.getByRole('link', {name: 'create one'});
    // If the Huspot add-on isn't activated, the page will be empty so this link won't be there.
    if (await createFeedLink.isVisible()) {
      await createFeedLink.click();
      // Set lead status to "new".
      await page.selectOption('#_hs_customer_hs_lead_status', {label: 'New'});
      // Map contact fields.
      await page.selectOption('#_hs_customer_firstname', {label: 'First name'});
      await page.selectOption('#_hs_customer_lastname', {label: 'Last name'});
      await page.selectOption('#_hs_customer_email', {label: 'Email'});
      // Save Hubspot feed.
      await page.getByRole('button', {name: 'Save Settings'}).click();
      await expect(page.locator('.gforms_note_success')).toBeVisible();

      // Check that the feed is active.
      await page.goto(`./wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=gravityformshubspot&id=${createdForm.id}`);
      const hubspotFeed = page.locator('#the-list > tr').first();
      await expect(hubspotFeed.locator('.gform-status-indicator-status')).toContainText('Active');
    }
  });
});
