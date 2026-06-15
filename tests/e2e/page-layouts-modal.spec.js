import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('checks if the welcome modal on the editor is present and closed when the button is clicked', async ({page, admin}) => {
  await admin.createNewPost({postType: 'page', legacyCanvas: true});

  const modal = page.getByRole('dialog', {name: 'Choose a pattern'});
  await expect(modal).toBeVisible();

  const closeButton = modal.getByRole('button', {name: 'Close'});
  await expect(closeButton).toBeEnabled();
  await closeButton.click();

  await expect(modal).toBeHidden();
});
