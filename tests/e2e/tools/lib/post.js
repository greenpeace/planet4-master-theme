/**
 * Publishes a post using the provided editor and returns the URL of the published post.
 *
 * @param {Object} params        - Parameters for publishing the post.
 * @param {Object} params.page   - The page object for interacting with the browser.
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
 * Publishes a post and then navigates to the published post's URL.
 *
 * @param {Object} params        - Parameters for publishing the post and visiting the URL.
 * @param {Object} params.page   - The page object for interacting with the browser.
 * @param {Object} params.editor - The editor object used to publish the post.
 */
async function publishPostAndVisit({page, editor}) {
  const urlString = await publishPost({page, editor});

  await page.goto(urlString);
}

/**
 * Creates a new post with a featured image set.
 *
 * @param {Object} p        - Parameters for creating the post and setting the featured image.
 * @param {Object} p.admin  - The admin object used to create a new post.
 * @param {Object} p.editor - The editor object used to interact with the editor.
 * @param {Object} params   - Additional parameters for creating the post.
 * @return {Promise<Object>} The newly created post.
 */
async function createPostWithFeaturedImage({admin, editor}, params) {
  const newPost = await admin.createNewPost({...params, legacyCanvas: true});
  const editorSettings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
  await editorSettings.getByRole('button', {name: 'Set featured image'}).click();
  const imageModal = await editor.canvas.getByRole('dialog', {name: 'Featured image'});
  const mediaLibraryTab = await imageModal.locator('#menu-item-browse');
  const mediaLibraryTabOpen = await mediaLibraryTab.getAttribute('aria-selected');
  if (mediaLibraryTabOpen === 'false') {
    await mediaLibraryTab.click();
  }
  await imageModal.getByRole('checkbox', {name: 'OCEANS-GP0STOM6C'}).click();
  await imageModal.getByRole('button', {name: 'Set featured image'}).click();

  return newPost;
}

export {publishPost, publishPostAndVisit, createPostWithFeaturedImage};
