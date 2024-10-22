import {expect} from "@playwright/test";

/**
 * Publishes a post using the provided editor and returns the URL of the published post.
 *
 * @param {Object} params - Parameters for publishing the post.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {Object} params.editor - The editor object used to publish the post.
 * @return {Promise<string>} The URL of the published post.
 */
async function publishPost({page, editor}) {
  await editor.publishPost();

  const urlString = await page
    .getByRole('region', {name: 'Editor publish'})
    .getByRole('textbox', {name: 'address'})
    .inputValue();

  return urlString;
}

/**
 * Updates a post and waits for the confirmation snackbar.
 *
 * @param {Object} params      - Parameters for updating the post.
 * @param {Object} params.page - The page object representing the browser page.
 * @return {Promise<void>} - A promise that resolves when the snackbar confirming the update is visible.
 */
async function updatePost({page}) {
  const updateButton = await page.locator('.editor-header__settings').getByRole('button', {name: 'Save'});
  await updateButton.click();

  return page.waitForSelector('.components-snackbar');
}

/**
 * Publishes a post and then navigates to the published post's URL.
 *
 * @param {Object} params - Parameters for publishing the post and visiting the URL.
 * @param {Object} params.page - The page object for interacting with the browser.
 * @param {Object} params.editor - The editor object used to publish the post.
 */
async function publishPostAndVisit({page, editor}) {
  const urlString = await publishPost({page, editor});

  await page.goto(urlString);
}

/**
 * Creates a new post with a featured image set.
 *
 * @param {Object} p - Parameters for creating the post and setting the featured image.
 * @param {Object} p.page - The page object used to interact with the editor.
 * @param {Object} p.admin - The admin object used to create a new post.
 * @param {Object} p.editor - The editor object used to interact with the editor.
 * @param {Object} params - Additional parameters for creating the post.
 * @return {Promise<Object>} The newly created post.
 */
async function createPostWithFeaturedImage({page, admin, editor}, params) {
  const newPost = await admin.createNewPost({...params, legacyCanvas: true});

  await editor.openDocumentSettingsSidebar();

  await page.getByRole('button', {name: 'Set featured image'}).click();

  await page.getByRole('dialog', {name: 'Featured image'});
  await page.locator('button#menu-item-browse[aria-selected="true"]').click();

  const mediaSearchInput = await page.locator('#media-search-input');
  await mediaSearchInput.click();
  await mediaSearchInput.fill('OCEANS-GP0STOM6C');
  await mediaSearchInput.press('Enter');

  const thumbnail = await page.locator('li[aria-label="OCEANS-GP0STOM6C"]').nth(0);
  await page.waitForSelector('li[aria-label="OCEANS-GP0STOM6C"]');
  expect(thumbnail).toBeVisible();
  await thumbnail.click();

  await page.getByRole('button', {name: 'Set featured image'}).click();

  return newPost;
}

export {publishPost, publishPostAndVisit, createPostWithFeaturedImage, updatePost};
