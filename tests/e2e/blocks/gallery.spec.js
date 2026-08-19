import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Gallery basic functionalities', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {
    title: 'Test page for Gallery',
    postType: 'page',
  });

  // Search block
  await searchAndInsertBlock({editor, page}, 'planet4-blocks/gallery');

  await page.route('./wp-json/planet4/v1/gallery/images/*', async route => {
    const request = route.request();
    const response = await request.response();

    expect(response.status()).toEqual(200);
    await route.continue();
  });

  await editor.canvas.getByRole('button', {name: 'Media Library'}).click();

  const imageModal = page.getByRole('dialog', {name: 'Create gallery'});
  await imageModal.getByRole('tab', {name: 'Media Library'}).click();
  imageModal.getByRole('tabpanel', {name: 'Media Library'});
  await expect(imageModal.locator('[data-id]').first()).toBeVisible();
  const mediaIds = await imageModal
    .locator('.attachment')
    .evaluateAll(items =>
      items.map(item => item.dataset.id)
    );

  // Pick 3 available media items for the gallery block.
  const imageId1 = mediaIds[50];
  const imageId2 = mediaIds[51];
  const imageId3 = mediaIds[52];

  await imageModal.locator(`[data-id="${imageId1}"]`).click();
  await imageModal.locator(`[data-id="${imageId2}"]`).click();
  await imageModal.locator(`[data-id="${imageId3}"]`).click();

  await page.getByRole('button', {name: 'Create a new gallery'}).click();
  await page.getByRole('dialog', {name: 'Edit gallery'}).getByRole('button', {name: 'Insert gallery'}).click();

  // Publish post
  await publishPostAndVisit({page, editor});

  const gallery = page.locator('[data-hydrate="planet4-blocks/gallery"]');
  await expect(gallery).toBeVisible();
  await expect(gallery.locator('.carousel-item')).toHaveCount(3);
});
