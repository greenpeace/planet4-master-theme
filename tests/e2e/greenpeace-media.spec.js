import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test.describe('Greenpeace Media tests', () => {
  // test('import an image to the Library using a search term', async ({page, requestUtils}) => {
  //   await page.goto('./wp-admin/admin.php?page=media-picker');
  //   await page.getByPlaceholder('Search').fill('Rainbow Warrior');
  //   await page.keyboard.press('Enter');
  //   await expect(page.locator('.archive-picker-loading')).toBeVisible();
  //   await expect(page.locator('.archive-picker-loading')).toBeHidden();
  //   await page.locator('.picker-list > li').first().click();
  // });

  test('bulk select and upload two images using identifiers', async ({page}) => {
    await page.goto('./wp-admin/admin.php?page=media-picker');

    const bulkSelectButton = page.getByRole('button', {name: 'Bulk Select'});
    const searchInput = page.getByPlaceholder('Search');
    const spinner = page.locator('.archive-picker-loading');
    const images = page.locator('.picker-list > li');

    await expect(spinner).toBeVisible();
    await expect(spinner).toBeHidden();
    await searchInput.fill('GP0STXWME');
    await page.keyboard.press('Enter');
    await expect(spinner).toBeVisible();
    await expect(spinner).toBeHidden();
    await expect(images).toHaveCount(1);
    await searchInput.fill('GP0STXWZH');
    await page.keyboard.press('Enter');
    await expect(spinner).toBeVisible();
    await expect(spinner).toBeHidden();
    await expect(images).toHaveCount(2);
    await bulkSelectButton.click();
    await expect(searchInput).toBeHidden();
    const bulkUploadButton = page.getByRole('button', {name: 'Bulk Upload'});
    await expect(bulkUploadButton).toBeVisible();
    await expect(bulkUploadButton).toBeDisabled();
    for (let index = 0; index < 2; index++) {
      const toSelect = images.nth(index);
      await toSelect.click();
      await expect(toSelect).toHaveClass('is-selected');
    }
    await expect(bulkUploadButton).toBeEnabled();
    await bulkUploadButton.click();
    page.on('dialog', dialog => dialog.accept());
    const loadingMessage = page.getByText('Processing 2 images');
    await expect(loadingMessage).toBeVisible();
    await expect(loadingMessage).toBeHidden();
    await expect(searchInput).toBeVisible();
  });
});

// Assert that after some time the Processing 2 images message is hidden and the search box is shown again.
