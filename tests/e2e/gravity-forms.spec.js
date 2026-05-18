import {test, expect} from './tools/lib/test-utils.js';
import {
  toggleRestAPI,
  createForm,
  fillAndSubmitForm,
  checkEntry,
  changeConfirmationType,
} from './tools/lib/gravity-forms.js';

const CONFIRMATION_MESSAGE = 'This is a dummy confirmation message for testing purposes.';
const TEST_REDIRECT = 'https://www.greenpeace.org/international/';

test.useAdminLoggedIn();

test.describe('Gravity Forms tests', () => {
  test.describe.configure({mode: 'serial'});
  const testId = Math.floor(Math.random() * 10000); //NOSONAR
  let createdForm;
  let newPost;

  test.beforeEach(async ({page, admin, requestUtils}) => {
    // Enable Gravity Forms rest API.
    await toggleRestAPI({page, admin}, true);

    // Create a new form.
    createdForm = await createForm({page}, {
      title: `Gravity Forms test form - ${testId}`,
    });

    // Create a new post with the new form.
    newPost = await requestUtils.rest({
      path: '/wp/v2/posts',
      method: 'POST',
      data: {
        title: `Gravity Forms test post - ${testId}`,
        content: `<!-- wp:gravityforms/form {"formId":"${createdForm.id}"} /-->`,
        status: 'publish',
        featured_media: 357,
      },
    });
  });

  test.afterEach(async ({page, admin}) => {
    // Delete the form.
    await page.request.delete(`./wp-json/gf/v2/forms/${createdForm.id}?force=1`);

    // Disable Gravity Forms rest API.
    await toggleRestAPI({page, admin}, false);

    await page.close();
  });

  test('check the confirmation message, text type', async ({page, admin}) => {
    test.slow();

    // Navigate to form confirmation settings via admin helper
    await admin.visitAdminPage(
      'admin.php',
      `page=gf_edit_forms&view=settings&subview=confirmation&id=${createdForm.id}`
    );

    // Make sure the form uses the Text confirmation type.
    await changeConfirmationType({page, admin}, createdForm.id, 'Text');

    // Update the form's default confirmation text.
    // Wait for TinyMCE to fully initialize before attempting to fill it
    const tinymceFrame = page.frameLocator('#_gform_setting_message_ifr');
    const tinymceBody = tinymceFrame.locator('#tinymce');
    await expect(tinymceBody).toBeVisible();

    await tinymceBody.click();
    await tinymceBody.fill(CONFIRMATION_MESSAGE);

    // Verify the content is actually in the editor before saving
    await expect(tinymceBody).toContainText(CONFIRMATION_MESSAGE);

    await page.getByRole('button', {name: 'Save Confirmation'}).click();

    // Wait for success notice and then wait for the network to be idle
    // to ensure the save has fully propagated before navigating away
    await expect(page.locator('.gforms_note_success')).toBeVisible();
    await page.waitForLoadState('networkidle');


    // Go to the post which has the form.
    await page.goto(newPost.link);

    // Fill and submit the form.
    await fillAndSubmitForm({page}, createdForm.id);

    // Check the confirmation message text.
    const confirmationMessage = page.locator('.gform_confirmation_message');
    await expect(confirmationMessage).toBeVisible();
    await expect(confirmationMessage).toContainText(CONFIRMATION_MESSAGE);

    // Check that the entry has been registered as expected.
    await checkEntry({page, admin}, createdForm.id);
  });

  test('check the confirmation message, redirect type', async ({page, admin}) => {
    test.slow();

    await admin.visitAdminPage(
      'admin.php',
      `page=gf_edit_forms&view=settings&subview=confirmation&id=${createdForm.id}`
    );

    // Make sure the form uses the Redirect confirmation type.
    await changeConfirmationType({page, admin}, createdForm.id, 'Redirect');

    // Set up the redirection.
    const redirectInput = page.getByLabel('Redirect URL(Required)');
    await expect(redirectInput).toBeVisible();
    await redirectInput.fill(TEST_REDIRECT);
    await expect(redirectInput).toHaveValue(TEST_REDIRECT);

    await page.getByRole('button', {name: 'Save Confirmation'}).click();
    await expect(page.locator('.gforms_note_success')).toBeVisible();

    // Wait for save to fully propagate before navigating away
    await page.waitForLoadState('networkidle');

    // Go to the post which has the form.
    await page.goto(newPost.link);

    // Fill and submit the form.
    await fillAndSubmitForm({page}, createdForm.id);

    // Make sure the page is redirected as expected.
    await expect(page).toHaveURL(TEST_REDIRECT);

    // Check that the entry has been registered as expected.
    await checkEntry({page, admin}, createdForm.id);
  });

  test('check the confirmation message, page type', async ({page, admin}) => {
    test.slow();

    await admin.visitAdminPage(
      'admin.php',
      `page=gf_edit_forms&view=settings&subview=confirmation&id=${createdForm.id}`
    );

    // Make sure the form uses the Page confirmation type.
    await changeConfirmationType({page, admin}, createdForm.id, 'Page');

    // Set up the page redirect.
    const confirmationSettings = page.locator('#gform_setting_page');
    const selectButton = confirmationSettings.locator('[data-js="gform-dropdown-control"]');
    const spinner = selectButton.locator('.gform-dropdown__spinner');

    await expect(selectButton).toBeVisible();
    await expect(selectButton).toBeEnabled();

    await page.waitForTimeout(500);
    await selectButton.click();

    const pagesList = confirmationSettings.locator('.gform-dropdown__list');

    await expect(pagesList).toBeVisible();

    const searchInput = confirmationSettings.getByPlaceholder('Search all Pages');
    await expect(searchInput).toBeVisible();
    await expect(searchInput).toBeEnabled();
    await searchInput.click();

    // Type keyword slowly
    await page.keyboard.type('Home', {delay: 50});

    // Wait for the spinner to appear and then disappear to ensure results are loaded
    await expect(spinner).toBeVisible({timeout: 5000});
    await expect(spinner).toBeHidden({timeout: 10000});

    // Wait for the expected page to appear in the results and click it
    const homeButton = pagesList.getByRole('button', {name: 'Home'});
    await expect(homeButton).toBeVisible();
    await homeButton.click();

    await expect(pagesList).toBeHidden();
    await expect(selectButton).toContainText('Home');

    await page.getByRole('button', {name: 'Save Confirmation'}).click();
    await expect(page.locator('.gforms_note_success')).toBeVisible();

    // Go to the post which has the form.
    await page.goto(newPost.link);
    await page.waitForLoadState('networkidle');


    // Fill and submit the form.
    await fillAndSubmitForm({page}, createdForm.id);

    // Make sure the page is redirected as expected.
    await expect(page).toHaveURL('./');

    // Check that the entry has been registered as expected.
    await checkEntry({page, admin}, createdForm.id);
  });

  test('check the Hubspot feeds', async ({page, admin}) => {
    await admin.visitAdminPage(
      'admin.php',
      `page=gf_edit_forms&view=settings&subview=gravityformshubspot&id=${createdForm.id}`
    );

    // Add a Hubspot feed.
    const createFeedLink = page.getByRole('link', {name: 'create one'});

    // If the HubSpot add-on isn't activated this link won't exist — skip
    if (!(await createFeedLink.isVisible())) {
      return;
    }

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
    await admin.visitAdminPage(
      'admin.php',
      `page=gf_edit_forms&view=settings&subview=gravityformshubspot&id=${createdForm.id}`
    );
    const hubspotFeed = page.locator('#the-list > tr').first();
    await expect(hubspotFeed.locator('.gform-status-indicator-status')).toContainText('Active');
  });
});
