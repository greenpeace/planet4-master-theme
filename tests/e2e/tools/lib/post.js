import {expect} from './test-utils';

/**
 * Waits for the underlying WP REST API save request (POST or PUT to
 * /wp/v2/<type>/<id> or /wp/v2/<type>) to complete successfully.
 * This is more reliable than watching for the snackbar, which can
 * resolve prematurely against a stale snackbar left over from a
 * previous save in the same session.
 *
 * @param {Object}   page   - Playwright page object.
 * @param {Function} action - Async function that triggers the save (e.g. clicking Save/Publish).
 */
async function waitForPostSaveResponse(page, action) {
  const [response] = await Promise.all([
    page.waitForResponse(
      resp =>
        /\/wp\/v2\/[a-z0-9_-]+(\/\d+)?(\?.*)?$/i.test(resp.url()) &&
        ['POST', 'PUT'].includes(resp.request().method()) &&
        resp.status() < 400,
      {timeout: 15000}
    ),
    action(),
  ]);

  return response;
}

/**
 * Publishes a post using the provided editor and returns the URL of the published post.
 *
 * @param {Object} params        - Parameters for publishing the post.
 * @param {Object} params.page   - The page object for interacting with the browser.
 * @param {Object} params.editor - The editor object used to publish the post.
 *
 * @return {Promise<string>} The URL of the published post.
 */
async function publishPost({page, editor}) {
  // We should be able to remove this check once we update Playwright to the latest version.
  const closeSettingsSidebar = page.getByRole('button', {name: 'Close Settings'});
  if (await closeSettingsSidebar.isVisible()) {
    await closeSettingsSidebar.click();
  }

  await waitForPostSaveResponse(page, () => editor.publishPost());

  // Snackbar confirms the UI has caught up, in addition to the network response above.
  const snackbarLink = page.locator('.components-snackbar__content a').last();
  await expect(snackbarLink).toBeVisible();

  return snackbarLink.getAttribute('href');
}

/**
 * Updates a post and waits both for the underlying save request to
 * succeed and for the confirmation snackbar to appear.
 *
 * @param {Object} params      - Parameters for updating the post.
 * @param {Object} params.page - The page object representing the browser page.
 */
async function updatePost({page}) {
  const updateButton = page.locator('.editor-header__settings').getByRole('button', {name: 'Save'});

  await waitForPostSaveResponse(page, () => updateButton.click());

  await expect(page.locator('.components-snackbar').last()).toBeVisible();
}

/**
 * Publishes a post and then navigates to the published post's URL.
 *
 * @param {Object} params        - Parameters for publishing the post and visiting the URL.
 * @param {Object} params.page   - The page object for interacting with the browser.
 * @param {Object} params.editor - The editor object used to publish the post.
 */
async function publishPostAndVisit({page, editor}) {
  const urlString = await publishPost({page, editor});

  await page.goto(urlString, {waitUntil: 'domcontentloaded'});
}

/**
 * Creates a new post with a featured image set.
 *
 * @param {Object} p        - Parameters for creating the post and setting the featured image.
 * @param {Object} p.page   - The page object used to interact with the editor.
 * @param {Object} p.admin  - The admin object used to create a new post.
 * @param {Object} p.editor - The editor object used to interact with the editor.
 * @param {Object} params   - Additional parameters for creating the post.
 * @return {Promise<Object>} The newly created post.
 */
async function createPostWithFeaturedImage({page, admin, editor}, params) {
  const newPost = await admin.createNewPost({...params, legacyCanvas: true});

  await editor.openDocumentSettingsSidebar();

  await page.getByRole('button', {name: 'Set featured image'}).click();

  await expect(page.getByRole('dialog', {name: 'Featured image'})).toBeVisible();
  await page.locator('button#menu-item-browse').click();

  const mediaSearchInput = page.locator('#media-search-input');
  await mediaSearchInput.fill('OCEANS-GP0STOM6C');
  await page.keyboard.press('Enter');

  const thumbnail = page.locator('li[aria-label="OCEANS-GP0STOM6C"]').first();
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

  return newPost;
}

export {publishPost, publishPostAndVisit, createPostWithFeaturedImage, updatePost};
