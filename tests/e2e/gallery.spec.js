import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from './tools/lib/post.js';
import {searchAndInsertBlock} from './tools/lib/editor.js';

test.useAdminLoggedIn();

test.skip('Test Gallery basic functionalities', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {
    title: 'Test page for Gallery',
    postType: 'page',
  });

  // Search block
  await searchAndInsertBlock({page}, 'Planet 4 Gallery', 'planet4-blocks-gallery');

  await page.route('./wp-json/planet4/v1/gallery/images/*', async route => {
    const request = route.request();
    const response = await request.response();

    expect(response.status()).toEqual(200);
    await route.continue();
  });

  await page.getByRole('button', {name: 'Media Library'}).click();

  const imageModal = page.getByRole('dialog', {name: 'Create gallery'});
  await imageModal.getByRole('tab', {name: 'Media Library'}).click();
  imageModal.getByRole('tabpanel', {name: 'Media Library'});

  await imageModal.locator('[data-id="357"]').click();
  await imageModal.locator('[data-id="354"]').click();
  await imageModal.locator('[data-id="350"]').click();

  await page.getByRole('button', {name: 'Create a new gallery'}).click();
  await page.getByRole('dialog', {name: 'Edit gallery'}).getByRole('button', {name: 'Insert gallery'}).click();

  // Publish post
  await publishPostAndVisit({page, editor});

  const gallery = page.locator('[data-hydrate="planet4-blocks/gallery"]');
  await expect(gallery).toBeVisible();
  await expect(gallery.locator('.carousel-item')).toHaveCount(3);
});
