import {expect} from '@playwright/test';

const toggleRestAPI = async ({page}, enabled) => {
  await page.goto('./wp-admin/admin.php?page=gf_settings&subview=gravityformswebapi');
  await page.getByRole('checkbox', {label: 'Enabled'}).setChecked(enabled);
  const authSettings = page.locator('#gform-settings-section-gform_section_authentication_v2');
  if (enabled) {
    await expect(authSettings).toBeVisible();
  } else {
    await expect(authSettings).toBeHidden();
  }
  await page.getByRole('button', {name: 'Update'}).click();
  await expect(page.locator('.gforms_note_success')).toBeVisible();
};

const createForm = async ({page}, {title, confirmations}) => {
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
          label: 'Name',
          isRequired: 1,
        },
        {
          type: 'email',
          label: 'Email',
          isRequired: 1,
        },
      ],
      confirmations,
    },
  });

  const createdForm = await response.json();
  return createdForm;
};

export {toggleRestAPI, createForm};
