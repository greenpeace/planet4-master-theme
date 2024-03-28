import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from './tools/lib/post.js';

test.useAdminLoggedIn();

test('Test Gallery basic functionalities', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({admin, editor}, {
    title: 'Test page for Gallery',
    postType: 'page',
  });
  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('This is a test Page for gallery.');
  await page.keyboard.press('Enter');
  await page.keyboard.type('/gallery');
  await page.getByRole('option', {name: 'Gallery'}).click();

  await page.route('./wp-json/planet4/v1/gallery/images/*', async route => {
    const request = await route.request();
    const response = await request.response();

    await expect(response.status()).toEqual(200);
    await route.continue();
  });

  await editor.canvas.getByRole('button', {name: 'Media Library'}).click();
  const imageModal = await editor.canvas.getByRole('dialog', {name: 'Create gallery'});
  const mediaLibTab = await imageModal.getByRole('tab', {name: 'Media Library'});
  await mediaLibTab.click();
  await imageModal.getByRole('tabpanel', {name: 'Media Library'});

  await imageModal.locator('[data-id="357"]').click();
  await imageModal.locator('[data-id="354"]').click();
  await imageModal.locator('[data-id="350"]').click();
  await imageModal.getByRole('button', {name: 'Create a new gallery'}).click();
  await editor.canvas.getByRole('dialog', {name: 'Edit gallery'}).getByRole('button', {name: 'Insert gallery'}).click();

  // Publish post
  await publishPostAndVisit({page, editor});

  const gallery = page.locator('[data-hydrate="planet4-blocks/gallery"]');
  await expect(gallery).toBeVisible();
  await expect(gallery.locator('.carousel-item')).toHaveCount(3);
});
