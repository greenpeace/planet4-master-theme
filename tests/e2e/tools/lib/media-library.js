import {expect} from './test-utils.js';

/**
 * Searches for images in the Library based on a search term or image id.
 *
 * @param {Object} page       - The page object for interacting with the browser.
 * @param {string} searchTerm - The term to be searched, can be an image id.
 */
async function searchImages(page, searchTerm) {
  const searchInput = page.getByPlaceholder('Search');

  await searchInput.fill(searchTerm);
  await page.keyboard.press('Enter');
  await waitForLibraryLoad(page);
}

/**
 * Deletes an image from the Library.
 * The image id is optional, it's not needed if the image is already selected.
 *
 * @param {Object} page      - The page object for interacting with the browser.
 * @param {string} [imageId] - The image to be deleted (optional).
 */
async function deleteImageFromLibrary(page, imageId) {
  const sidebar = page.locator('.archive-picker-sidebar');
  if (imageId) {
    // Search for the image.
    await searchImages(page, imageId);
    const image = page.locator('.picker-list > li');

    // Select the image.
    await image.click();
  }

  // Open image link via the sidebar.
  await expect(sidebar).toBeVisible();
  await sidebar.locator('.sidebar-action').click();

  // Delete the image and wait for confirmation.
  const deleteLink = page.getByText('Delete permanently');
  await expect(deleteLink).toBeVisible();
  await deleteLink.click();
  await expect(page.getByText('Media file permanently deleted.')).toBeVisible();

  // Go back to the Media Library.
  await page.goto('./wp-admin/admin.php?page=media-picker');
  await waitForLibraryLoad(page);
}

/**
 * Waits for the Library page to be fully loaded, based on the spinner.
 *
 * @param {Object} page - The page object for interacting with the browser.
 */
async function waitForLibraryLoad(page) {
  const spinner = page.locator('.archive-picker-loading');
  await expect(spinner).toBeVisible();
  await expect(spinner).toBeHidden();
}

export {searchImages, deleteImageFromLibrary, waitForLibraryLoad};
