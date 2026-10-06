import {expect} from '@playwright/test';

const TEST_FIRST_NAME = 'Jon';
const TEST_LAST_NAME = 'Snow';
const TEST_EMAIL = 'jon.snow@gmail.com';

/**
 * Toggle the Gravity Forms rest API to use it for tests.
 * It is disabled by default.
 *
 * @param {Object}  params       - Parameters for publishing the post.
 * @param {Object}  params.page  - The page object for interacting with the browser.
 * @param {Object}  params.admin - The admin object for interacting with the admin panel.
 * @param {boolean} enabled      - Whether it should be enabled or disabled.
 */
const toggleRestAPI = async ({page, admin}, enabled) => {
  await admin.visitAdminPage('admin.php', 'page=gf_settings&subview=gravityformswebapi');
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
 * @param {Object} params       - Parameters for publishing the post.
 * @param {Object} params.page  - The page object for interacting with the browser.
 * @param {Object} params.admin - The admin object for interacting with the admin panel.
 * @param {number} formId       - The form id.
 */
const checkEntry = async ({page, admin}, formId) => {
  await admin.visitAdminPage('admin.php', `page=gf_entries&id=${formId}`);
  const latestEntry = page.locator('#the-list > tr.entry_row').first();
  await expect(latestEntry).toBeVisible();
  await expect(latestEntry.locator('td[data-colname="First name"]')).toContainText(TEST_FIRST_NAME);
  await expect(latestEntry.locator('td[data-colname="Last name"]')).toContainText(TEST_LAST_NAME);
  await expect(latestEntry.locator('td[data-colname="Email"]')).toContainText(TEST_EMAIL);
};

/**
 * Change the confirmation type for a Gravity Forms form.
 *
 * @param {Object} params       - Parameters for publishing the post.
 * @param {Object} params.page  - The page object for interacting with the browser.
 * @param {Object} params.admin - The admin object for interacting with the admin panel.
 * @param {number} formId       - The form id.
 * @param {string} label        - The confirmation type label.
 */
const changeConfirmationType = async ({page, admin}, formId, label) => {
  await admin.visitAdminPage(
    'admin.php',
    `page=gf_edit_forms&view=settings&subview=confirmation&id=${formId}`
  );
  const confirmationRow = await page.locator('#the-list > tr').first();
  await expect(confirmationRow).toBeVisible();
  await confirmationRow.hover();

  const editLink = await page.getByRole('link', {name: 'Edit', exact: true});
  await expect(editLink).toBeVisible();
  await editLink.click();

  // Gravity Forms uses these values for the confirmation type radio buttons.
  const typeValue = {
    Text: 'message',
    Page: 'page',
    Redirect: 'redirect',
  }[label];

  const typeRadio = page.locator(`input[name="_gform_setting_type"][value="${typeValue}"]`);
  await expect(typeRadio).toBeVisible();

  // Scroll into view first — WebKit won't reliably fire events on off-screen elements
  await typeRadio.scrollIntoViewIfNeeded();

  await typeRadio.check();
  await expect(typeRadio).toBeChecked();

  await page.waitForTimeout(500);
};

/**
 * Select a page in the Gravity Forms "Page" confirmation dropdown.
 *
 * @param {Object} params      - Parameters for interacting with the browser.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {string} title       - The title of the page to select.
 */
const selectConfirmationPage = async ({page}, title) => {
  const root = page.locator('article.gform-dropdown[data-post-type="page"]');
  const control = root.locator('[data-js="gform-dropdown-control"]');
  const list = root.locator('[data-js="gform-dropdown-list"]');
  const search = root.locator('[data-js="gform-dropdown-search"]');
  const option = list.getByRole('button', {name: title, exact: true});

  await expect(control).toBeVisible();
  await expect(control).toBeEnabled();
  // The widget is ready once the spinner is gone and the initial list is rendered.
  await expect(root.locator('.gform-dropdown__spinner')).toBeHidden();
  await expect(list.locator('li').first()).toBeAttached();

  const openers = [
    ['click', () => control.click({delay: 100})], // Hold the press like a human would.
    ['keyboard', () => control.press('Enter')],
    ['dispatch', () => control.dispatchEvent('click')],
  ];
  let attempts = 0;
  // eslint-disable-next-line no-unused-vars
  let used = 'already open';

  await expect(async () => {
    if ((await control.getAttribute('aria-expanded')) !== 'true') {
      const [name, open] = openers[attempts++ % openers.length];
      used = name;
      await open();
    }
    await expect(control).toHaveAttribute('aria-expanded', 'true', {timeout: 1500});

    await search.fill('');
    await search.pressSequentially(title, {delay: 50});
    await expect(option).toBeVisible({timeout: 8000});
  }).toPass({timeout: 30000, intervals: [500, 1000, 2000]});

  await option.click();
  await expect(list).toBeHidden();
  await expect(control).toContainText(title);
};

export {toggleRestAPI, createForm, fillAndSubmitForm, checkEntry, changeConfirmationType, selectConfirmationPage};
