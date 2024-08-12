import {test, expect} from './tools/lib/test-utils.js';
import {
  searchImages,
  deleteImageFromLibrary,
  waitForLibraryLoad,
} from './tools/lib/media-library.js';

const IMAGE_1 = 'GP0STXWME';
const IMAGE_2 = 'GP0STXWZH';
const MEDIA_LIBRARY_PAGE = './wp-admin/admin.php?page=media-picker';

test.useAdminLoggedIn();

test.describe('Greenpeace Media tests', () => {
  test('import an image to the Library using a search term', async ({page}) => {
    // Make sure to close any alerts.
    page.on('dialog', dialog => dialog.accept());

    // Go to the Media Library and wait for the page to load.
    await page.goto(MEDIA_LIBRARY_PAGE);
    await waitForLibraryLoad(page);

    // Search for an image to import.
    await searchImages(page, 'Rainbow Warrior');
    const image = page.locator('.picker-list > li')
      .filter({hasNotText: 'Added to Media Library'})
      .first();
    await image.click();
    await expect(image).toHaveClass('is-selected');
    const sidebar = page.locator('.archive-picker-sidebar');
    await expect(sidebar).toBeVisible();

    // Import the image.
    await sidebar.getByRole('button', {name: 'Import to Library'}).click();
    await expect(sidebar.getByText('Processing...')).toBeVisible();
    await expect(sidebar.getByText('Added to Library')).toBeVisible();

    // Delete the image from the library.
    await deleteImageFromLibrary(page);
  });

  test('bulk select and upload two images using identifiers', async ({page}) => {
    // Make sure to close any alerts.
    page.on('dialog', dialog => dialog.accept());

    // Go to the Media Library and wait for the page to load.
    await page.goto(MEDIA_LIBRARY_PAGE);
    await waitForLibraryLoad(page);

    // Search for 2 specific images.
    const images = page.locator('.picker-list > li');
    await searchImages(page, IMAGE_1);
    await expect(images).toHaveCount(1);
    await searchImages(page, IMAGE_2);
    await expect(images).toHaveCount(2);
    await page.getByRole('button', {name: 'Bulk Select'}).click();

    // Select the 2 images.
    for (let index = 0; index < 2; index++) {
      await images.nth(index).click();
    }

    // Bulk upload them.
    await page.getByRole('button', {name: 'Bulk Upload'}).click();
    const loadingMessage = page.getByText('Processing 2 images');
    await expect(loadingMessage).toBeVisible();
    await expect(loadingMessage).toBeHidden();

    // Delete the images from the library.
    await page.reload();
    await waitForLibraryLoad(page);
    await deleteImageFromLibrary(page, IMAGE_1);
    await deleteImageFromLibrary(page, IMAGE_2);
  });
});
