import {test, expect} from './tools/lib/test-utils.js';
import {
  searchImages,
  deleteImageFromLibrary,
  waitForLibraryLoad,
} from './tools/lib/media-library.js';

const TEST_IMAGES = ['GP0STXWME', 'GP0STXWZH'];
const MEDIA_LIBRARY_PAGE = './wp-admin/admin.php?page=media-picker';

// Temporary fix.
test.skip();

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

    // Search for 2 specific images.
    const images = page.locator('.picker-list > li');
    for (let index = 0; index < 2; index++) {
      await searchImages(page, TEST_IMAGES[index]);
      await expect(images).toHaveCount(index + 1);
    }

    // Select the 2 images.
    await page.getByRole('button', {name: 'Bulk Select'}).click();
    for (let index = 0; index < 2; index++) {
      const image = images.nth(index);
      await image.click();
      await expect(image).toHaveClass('is-selected');
    }

    // Bulk upload them.
    await page.getByRole('button', {name: 'Bulk Upload'}).click();
    await expect(page.getByText('Processing 2 images')).toBeVisible();
    await expect(page.getByPlaceholder('Search')).toBeVisible();

    // Delete the images from the library.
    await page.reload();
    await waitForLibraryLoad(page);
    await deleteImageFromLibrary(page, TEST_IMAGES[0]);
    await page.goto(MEDIA_LIBRARY_PAGE);
    await waitForLibraryLoad(page);
    await deleteImageFromLibrary(page, TEST_IMAGES[1]);
  });
});
