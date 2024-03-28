import {openComponentPanel} from './editor.js';

async function publishPost({page, editor}) {
  await editor.publishPost();

  const urlString = await page
    .getByRole('region', {name: 'Editor publish'})
    .getByRole('textbox', {name: 'address'})
    .inputValue();

  return urlString;
}

async function publishPostAndVisit({page, editor}) {
  const urlString = await publishPost({page, editor});

  await page.goto(urlString);
}

async function createPostWithFeaturedImage({admin, editor}, params) {
  const newPost = await admin.createNewPost({...params, legacyCanvas: true});
  const editorSettings = await openComponentPanel({editor}, 'Featured image');
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
