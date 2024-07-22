import {test, expect} from '../tools/lib/test-utils.js';
import {publishPost, createPostWithFeaturedImage} from '../tools/lib/post.js';

import fieldsData from '../fixtures/enform/ensapi_sample_fields_response.json';
import questionsData from '../fixtures/enform/ensapi_sample_questions_response.json';
import questionData from '../fixtures/enform/ensapi_sample_question_236734_response.json';
import optinData from '../fixtures/enform/ensapi_sample_question_3877_response.json';
import depOptinData from '../fixtures/enform/ensapi_sample_question_220954_response.json';
import {formFields, formFieldsAttributes} from '../fixtures/enform/enformData.js';

test.describe.configure({mode: 'serial'});
let enFormId;
let pageUrl;
let featureIsActiveOnInstance;

test.useAdminLoggedIn();

test.describe('create, use and submit EN Form', () => {

  test('activate EN Form block', async ({page, admin}) => {
    // Enable EN Form block
    await admin.visitAdminPage('admin.php', 'page=planet4_settings_features');
    const checkbox = page.getByRole('checkbox', {name: ' Activate the EN Form block, as well as the "Progress Bar inside EN Form" Counter block style.'});
    featureIsActiveOnInstance = await checkbox.isChecked();
    if (!featureIsActiveOnInstance) {
      await checkbox.click();
      await page.locator('input[type="submit"]').click();
    }

    // Complete EN API settings to avoid api/cache issues
    await page.goto('./wp-admin/admin.php?page=en-settings');

    const adminPublicApiKey = page.locator('input#p4en_public_api');
    const adminPrivateApiKey = page.locator('input#p4en_private_api');
    const frontendPublicApiKey = page.locator('input#p4en_frontend_public_api');
    const frontendPrivateApiKey = page.locator('input#p4en_frontend_private_api');

    const apubKey = await adminPublicApiKey.inputValue();
    const aprvKey = await adminPrivateApiKey.inputValue();
    const fpubKey = await frontendPublicApiKey.inputValue();
    const fprvKey = await frontendPrivateApiKey.inputValue();

    if (!apubKey) {
      await adminPublicApiKey.fill('admin-public');
    }
    if (!aprvKey) {
      await adminPrivateApiKey.fill('admin-private');
    }
    if (!fpubKey) {
      await frontendPublicApiKey.fill('frontend-public');
    }
    if (!fprvKey) {
      await frontendPrivateApiKey.fill('frontend-private');
    }
    await page.locator('input[type="submit"]').click();
  });

  test('create simple EN Form', async ({page, admin, requestUtils}) => {
    // Insert relevant data in transient cache
    const postData = {
      'ens_auth_public_token': 'public-token',
      'ens_auth_token': 'private-token',
      'ens_supporter_fields_response': fieldsData.supporter,
      'ens_supporter_questions_response': questionsData.questions,
      'ens_supporter_question_by_id_response_236734': questionData['question.236734'],
      'ens_supporter_question_by_id_response_3887': optinData['question.3887'],
      'ens_supporter_question_by_id_response_220954': depOptinData['question.220954'],
    };
    const items = [];
    for (const key in postData) {
      items.push({key, value: JSON.stringify(postData[key])});
    }

    await requestUtils.rest({
      path: '/planet4/v1/transient',
      method: 'POST',
      data: {items},
    });

    // Create a new form
    await admin.visitAdminPage('post-new.php', 'post_type=p4en_form');
    const metaboxHeadings = await page.locator('h2').allInnerTexts();
    expect(metaboxHeadings.includes('Form preview')).toBeTruthy();
    expect(metaboxHeadings.includes('Selected Components')).toBeTruthy();
    expect(metaboxHeadings.includes('Available Fields')).toBeTruthy();
    expect(metaboxHeadings.includes('Available Questions')).toBeTruthy();
    expect(metaboxHeadings.includes('Available Opt-ins')).toBeTruthy();

    await page.locator('input[name="post_title"]').fill('Acceptance Test - ENForm');
    const fields = [].concat(formFields.fields, formFields.questions);
    for (const field of fields) {
      if (field.name === 'Email') {
        continue; // Email field is there by default
      }
      const element = await page.waitForSelector(`button[data-name="${field.name}"]`);
      await element.scrollIntoViewIfNeeded();
      await element.click();
    }

    // Set fields attributes.
    for (const field of fields) {
      const element = await page.waitForSelector(`tr[data-en-id="${field.id}"] .field-type-select`);
      await element.scrollIntoViewIfNeeded();
      if (field.name !== 'Email') { // Email field type is not modifiable
        await element.selectOption(formFieldsAttributes[field.name].type);
      }
      if (formFieldsAttributes[field.name]?.required && field.name !== 'Email') {
        await page.locator(`tr[data-en-id="${field.id}"] input[type="checkbox"]`).click();
      }
      if (formFieldsAttributes[field.name]?.label?.length > 0 && field.type !== 'OPT') {
        await page.locator(`tr[data-en-id="${field.id}"] input[type="text"]`).fill(formFieldsAttributes[field.name].label);
      }
      if (formFieldsAttributes[field.name]?.default_value?.length > 0) {
        await page.locator(`tr[data-en-id="${field.id}"] .dashicons-edit`).click();
        const defaultValField = await page.waitForSelector(`.dialog-${field.id} input[data-attribute="default_value"]`);
        await defaultValField.fill(formFieldsAttributes[field.name].default_value);
        await page.locator(`.dialog-${field.id} button[title='Close']`).click();
      }
    }

    // Set dependency field value.
    for (const field of fields) {
      if (!formFieldsAttributes[field.name]?.dependency) {
        continue;
      }
      await page.locator(`#en_form_selected_fields_table tr[data-en-id="${field.id}"] .dashicons-edit`).click();
      const element = await page.waitForSelector(`.dialog-${field.id} .dependency-select`);
      await element.selectOption(formFieldsAttributes[field.name].dependency);
      await page.locator(`.dialog-${field.id} button[title='Close']`).click();
    }

    // Reorder last field with the second to last field to assert that ordering fields works.
    const source = page.locator('tr:last-child span.dashicons-sort');
    const target = page.locator('tr:nth-last-child(2) span.dashicons-sort');
    await source.dragTo(target);

    await page.locator('#publish').click();

    // Assert that the fields were saved with the expected attributes/values.
    for (const field of fields) {
      const element = page.locator(`tr[data-en-id="${field.id}"]`);
      await expect(element).toHaveAttribute('data-en-name', field.name);
      if (formFieldsAttributes[field.name]?.required) {
        await page.locator(`tr[data-en-id="${field.id}"] input[type="checkbox"]`).isChecked();
      }
      if (formFieldsAttributes[field.name]?.label?.length > 0 && field.type !== 'OPT') {
        const labelField = page.locator(`tr[data-en-id="${field.id}"] input[type="text"]`);
        expect(labelField).toHaveValue(formFieldsAttributes[field.name].label);
      }
      if (formFieldsAttributes[field.name]?.default_value?.length > 0) {
        await page.locator(`tr[data-en-id="${field.id}"] .dashicons-edit`).click();
        const defaultValField = page.locator(`.dialog-${field.id} input[data-attribute="default_value"]`);
        await expect(defaultValField).toHaveValue(formFieldsAttributes[field.name].default_value);
        await page.locator(`.dialog-${field.id} button[title="Close"]`).click();
      }
    }

    const currentUrl = page.url();
    enFormId = new URL(currentUrl).searchParams.get('post');
  });

  test('create Page with EN Form side style', async ({page, admin, editor}) => {
    pageUrl = await createPageWithENForm(page, admin, editor, 'side');
  });

  test('fill EN Form (side style) on frontend', async ({page}) => {
    await fillENFormAndSubmit(page);
  });

  test('create Page with EN Form full-width-bg style', async ({page, admin, editor}) => {
    pageUrl = await createPageWithENForm(page, admin, editor, 'full-width-bg');
  });

  test('fill EN Form (full-width-bg style) on frontend', async ({page}) => {
    await fillENFormAndSubmit(page);
  });

  test('disable EN Form block', async ({page, admin}) => {
    await admin.visitAdminPage('admin.php', 'page=planet4_settings_features');

    if (featureIsActiveOnInstance) {
      return;
    }

    const checkbox = page.getByRole('checkbox', {name: ' Activate the EN Form block, as well as the "Progress Bar inside EN Form" Counter block style.'});
    const checked = await checkbox.isChecked();
    if (checked) {
      await checkbox.click();
      await page.locator('input[type="submit"]').click();
    }
  });
});

async function createPageWithENForm(page, admin, editor, style) {
  await createPostWithFeaturedImage({admin, editor}, {
    title: 'Test page with enform (style)',
    postType: 'page',
  });

  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('/enform');
  await page.getByRole('option', {name: 'EN Form'}).click();
  await page.waitForSelector('.block.enform-wrap');

  if (style === 'full-width-bg') {
    await page.getByRole('button', {name: 'Full page width with background'}).click();
  }
  const goalSelector = await page.getByRole('combobox', {name: 'Select Goal'});
  await goalSelector.selectOption('Petition Signup');
  const formSelector = await page.getByRole('combobox', {name: 'Planet 4 Engaging Networks form'});
  await formSelector.selectOption(enFormId);

  await page.waitForSelector('#en__field_supporter_emailAddress');

  // Publish page.
  const url = await publishPost({page, editor});
  return url;
}

async function fillENFormAndSubmit(page) {
  const fields = [].concat(formFields.fields, formFields.questions);

  await page.goto(pageUrl);

  // Check dependency field.
  for (const field of fields) {
    const dep = formFieldsAttributes[field.name]?.dependency;
    if (formFieldsAttributes[field.name].type === 'Checkbox' && dep) {
      await expect(page.locator(`.dependency-${field.name}`)).toBeDisabled();
    }
  }

  // Fill the form's fields
  for (const field of fields) {
    if (['Email', 'Text'].includes(formFieldsAttributes[field.name].type)) {
      const name = field.questionId ? `supporter.questions.${field.id}` : `supporter.${field.property}`;
      await page.locator(`[name="${name}"]`).fill('Lorem ipsum');
    } else if (formFieldsAttributes[field.name].type === 'Checkbox') {
      await page.locator(`label[for="en__field_supporter_questions_${field.id}"]`).dispatchEvent('click');
    }
  }

  await expect(page.locator('#p4en_form')).toContainText('Please enter a valid e-mail address.');
  await page.locator('[name="supporter.emailAddress"]').fill('test@example.com');

  await page.route('./wp-json/planet4/v1/enform/*', async route => {
    expect(route.request().postDataJSON()).toMatchObject({
      standardFieldNames: true,
      supporter: {
        questions: {
          'question.236734': 'Lorem ipsum',
          'question.220954': 'Y',
          'question.3887': 'Y',
        },
        emailAddress: 'test@example.com',
        firstName: 'Lorem ipsum',
        lastName: 'Lorem ipsum',
        'NOT_TAGGED_1': 'hidden field ασφ (0287#$%^ 日本語',
      },
    });

    await route.fulfill({json: []});
  });
  await page.getByRole('button', {name: 'Sign'}).click();
}
