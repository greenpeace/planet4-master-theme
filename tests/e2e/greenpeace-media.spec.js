import {test, expect} from './tools/lib/test-utils.js';
import {
  searchImages,
  deleteImageFromLibrary,
  waitForLibraryLoad,
} from './tools/lib/media-library.js';

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
    await expect(sidebar.locator('.sidebar-action')).toBeVisible();

    // Delete the image from the library.
    await deleteImageFromLibrary(page);
  });

  test('bulk select and upload two images using identifiers', async ({page}) => {
    // Make sure to close any alerts.
    page.on('dialog', dialog => dialog.accept());

    // Go to the Media Library and wait for the page to load.
    await page.goto(MEDIA_LIBRARY_PAGE);
    await waitForLibraryLoad(page);

    await page.getByRole('button', {name: 'Bulk Select'}).click();

    // Search for 2 random images.
    const imagesList = page.locator('.picker-list');
    await expect(imagesList).toHaveClass(/bulk-select/);

    const selectedImages = [];
    let idx = 0;

    while (selectedImages.length < 2 && idx < (await imagesList.locator('li').count())) {
      const image = imagesList.locator('li:not(.is-disabled)').nth(idx);
      if (typeof image.locator('.bulk-select-checkbox') !== 'undefined') {
        await image.click();
        selectedImages.push(await image.getAttribute('data-id'));
      }

      idx++;
    }

    expect(selectedImages.length).toBe(2);

    // Bulk upload them.
    await page.getByRole('button', {name: 'Bulk Upload'}).click();
    await expect(page.getByText('Processing 2 images')).toBeVisible();
    await expect(page.getByPlaceholder('Search')).toBeVisible();
    await waitForLibraryLoad(page);

    // Delete the images from the library.
    await deleteImageFromLibrary(page, selectedImages[0]);
    await page.goto(MEDIA_LIBRARY_PAGE);
    await waitForLibraryLoad(page);
    await deleteImageFromLibrary(page, selectedImages[1]);
  });
});
