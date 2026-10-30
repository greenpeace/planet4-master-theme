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
  const form = page.locator(`form[data-formid="${formId}"]`);
  await expect(form).toBeVisible();

  await form.getByLabel('First name').fill(TEST_FIRST_NAME);
  await form.getByLabel('Last name').fill(TEST_LAST_NAME);
  await form.getByLabel('Email').fill(TEST_EMAIL);

  await form.getByRole('button', {name: 'Submit'}).click();
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
  await expect(latestEntry.locator('[data-colname="First name"]')).toContainText(TEST_FIRST_NAME);
  await expect(latestEntry.locator('[data-colname="Last name"]')).toContainText(TEST_LAST_NAME);
  await expect(latestEntry.locator('[data-colname="Email"]')).toContainText(TEST_EMAIL);
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

/**
 * Create a post in the editor, add the Gravity Forms block, select the form and publish.
 *
 * @param {Object} params        - Fixtures.
 * @param {Object} params.page   - The page object.
 * @param {Object} params.admin  - The admin object.
 * @param {Object} params.editor - The editor object.
 * @param {Object} post          - Post parameters.
 * @param {string} post.title    - The post title.
 * @param {number} post.formId   - The form id to embed.
 * @return {Promise<number>} The published post id.
 */
const addFormToNewPost = async ({page, admin, editor}, {title, formId}) => {
  await admin.createNewPost({title, postType: 'post', legacyCanvas: true});

  await editor.openDocumentSettingsSidebar();

  // Open the "Analytics & Tracking" panel if it's collapsed.
  const panelToggle = page.getByRole('button', {name: 'Analytics & Tracking'});
  if ((await panelToggle.getAttribute('aria-expanded')) !== 'true') {
    await panelToggle.click();
  }

  // Set Global Project to "Not Applicable".
  const globalProject = page.getByLabel('Global Project');
  await globalProject.selectOption('Not Applicable');
  await expect(globalProject).toHaveValue('Not Applicable');

  await page.getByRole('button', {name: 'Set featured image'}).click();

  await expect(page.getByRole('dialog', {name: 'Featured image'})).toBeVisible();
  await page.locator('button#menu-item-browse').click();

  const mediaSearchInput = page.locator('#media-search-input');
  await mediaSearchInput.fill('NATURE-GP0STOE2U');
  await page.keyboard.press('Enter');

  const thumbnail = page.locator('li[aria-label="NATURE-GP0STOE2U"]').first();
  await expect(thumbnail).toBeVisible();
  await thumbnail.click();

  // Get the file url.
  const fileUrl = await page.locator('#attachment-details-copy-link').inputValue();
  // Remove the file extension.
  const fileName = fileUrl.slice(0, fileUrl.length - 4).split('/');
  await page.getByRole('button', {name: 'Set featured image'}).click();

  // Check the featured image is correctly assigned.
  await expect(page.locator('.editor-post-featured-image__preview-image'))
    .toHaveAttribute('src', new RegExp(fileName[fileName.length - 1]));

  await editor.insertBlock({name: 'gravityforms/form'});

  // Pick the form in the block placeholder.
  const block = editor.canvas.locator('[data-type="gravityforms/form"]');
  await expect(block).toBeVisible();
  const formSelect = block.locator('select').first();
  await formSelect.selectOption(String(formId));

  return await editor.publishPost();
};

export {toggleRestAPI, createForm, fillAndSubmitForm, checkEntry, changeConfirmationType, selectConfirmationPage, addFormToNewPost};
