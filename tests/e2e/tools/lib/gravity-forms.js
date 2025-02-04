import {expect} from '@playwright/test';

const TEST_FIRST_NAME = 'Jon';
const TEST_LAST_NAME = 'Snow';
const TEST_EMAIL = 'jon.snow@gmail.com';

/**
 * Toggle the Gravity Forms rest API to use it for tests.
 * It is disabled by default.
 *
 * @param {Object}  params      - Parameters for publishing the post.
 * @param {Object}  params.page - The page object for interacting with the browser.
 * @param {boolean} enabled     - Whether it should be enabled or disabled.
 */
const toggleRestAPI = async ({page}, enabled) => {
  await page.goto('./wp-admin/admin.php?page=gf_settings&subview=gravityformswebapi');
  await page.getByRole('checkbox', {label: 'Enabled'}).setChecked(enabled);
  const authSettings = page.locator('#gform-settings-section-gform_section_authentication_v2');
  if (enabled) {
    await expect(authSettings).toBeVisible();
  } else {
    await expect(authSettings).toBeHidden();
  }
  await page.getByRole('button', {name: 'Update', exact: true}).click();
  await expect(page.locator('.gforms_note_success')).toBeVisible();
};

/**
 * Create a new Gravity Forms form.
 *
 * @param {Object} params      - Parameters for publishing the post.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {Object} form        - Form parameters.
 * @param {Object} form.title  - The form title.
 */
const createForm = async ({page}, {title}) => {
  const response = await page.request.post('./wp-json/gf/v2/forms', {
    data: {
      title,
      button: {
        type: 'text',
        text: 'Submit',
      },
      fields: [
        {
          type: 'text',
          label: 'First name',
          isRequired: 1,
        },
        {
          type: 'text',
          label: 'Last name',
          isRequired: 1,
        },
        {
          type: 'email',
          label: 'Email',
          isRequired: 1,
        },
      ],
    },
  });

  const createdForm = await response.json();
  return createdForm;
};

/**
 * Fill and submit a Gravity Forms form.
 *
 * @param {Object} params      - Parameters for publishing the post.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {number} formId      - The form id.
 */
const fillAndSubmitForm = async ({page}, formId) => {
  const form = page.locator(`#gform_${formId}`);
  await form.getByLabel('First name').fill(TEST_FIRST_NAME);
  await form.getByLabel('Last name').fill(TEST_LAST_NAME);
  await form.getByLabel('Email').fill(TEST_EMAIL);
  const submitButton = form.getByRole('button', {name: 'Submit'});
  await submitButton.click();
};

/**
 * Check the latest entry for a Gravity Forms form.
 *
 * @param {Object} params      - Parameters for publishing the post.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {number} formId      - The form id.
 */
const checkEntry = async ({page}, formId) => {
  await page.goto(`./wp-admin/admin.php?page=gf_entries&id=${formId}`);
  const latestEntry = page.locator('#the-list > tr.entry_row').first();
  await expect(latestEntry).toBeVisible();
  await expect(latestEntry.locator('td[data-colname="First name"]')).toContainText(TEST_FIRST_NAME);
  await expect(latestEntry.locator('td[data-colname="Last name"]')).toContainText(TEST_LAST_NAME);
  await expect(latestEntry.locator('td[data-colname="Email"]')).toContainText(TEST_EMAIL);
};

/**
 * Change the confirmation type for a Gravity Forms form.
 *
 * @param {Object} params      - Parameters for publishing the post.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {number} formId      - The form id.
 * @param {string} label       - The confirmation type label.
 */
const changeConfirmationType = async ({page}, formId, label) => {
  await page.goto(`./wp-admin/admin.php?page=gf_edit_forms&view=settings&subview=confirmation&id=${formId}`);
  await page.locator('#the-list > tr:first-child').hover();
  await page.getByRole('link', {name: 'Edit', exact: true}).click();
  await page.getByLabel(label, {exact: true}).check();
};

export {toggleRestAPI, createForm, fillAndSubmitForm, checkEntry, changeConfirmationType};
