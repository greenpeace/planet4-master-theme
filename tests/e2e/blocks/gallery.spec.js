import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

const addGalleryAndCheckFrontend = async ({page, admin, editor}, pageTitle, buttonLabel, imagesLocator) => {
  await createPostWithFeaturedImage({page, admin, editor}, {
    title: pageTitle,
    postType: 'page',
  });

  await searchAndInsertBlock({editor, page}, 'planet4-blocks/gallery');

  await editor.openDocumentSettingsSidebar();
  await page.getByRole('region', {name: 'Editor settings'})
    .getByRole('button', {name: buttonLabel, exact: true})
    .click();

  await editor.canvas.getByRole('button', {name: 'Media Library'}).click();

  const imageModal = page.getByRole('dialog', {name: 'Create gallery'});
  await imageModal.getByRole('tab', {name: 'Media Library'}).click();
  await expect(imageModal.locator('[data-id]').first()).toBeVisible();

  const mediaIds = await imageModal
    .locator('.attachment')
    .evaluateAll(items => items.map(item => item.dataset.id));

  // Pick 3 available media items for the gallery block.
  const selectedIds = mediaIds.slice(0, 3);
  for (const id of selectedIds) {
    await imageModal.locator(`[data-id="${id}"]`).click();
  }

  await page.getByRole('button', {name: 'Create a new gallery'}).click();
  await page.getByRole('dialog', {name: 'Edit gallery'})
    .getByRole('button', {name: 'Insert gallery'})
    .click();

  await publishPostAndVisit({page, editor});

  const gallery = page.locator('[data-hydrate="planet4-blocks/gallery"]');
  await expect(gallery).toBeVisible();

  // Layout markup is present:
  const images = gallery.locator(imagesLocator);
  await expect(images).toHaveCount(3);

  // Regression guard. Every <img> must have a real source:
  await expect(gallery.locator(`${imagesLocator}:not([src])`)).toHaveCount(0);
  await expect(gallery.locator(`${imagesLocator}[src=""]`)).toHaveCount(0);

  // Images are loading="lazy", so scroll them into view and confirm they decode:
  for (let i = 0; i < 3; i++) {
    const img = images.nth(i);
    await expect(img).toHaveAttribute('src', /\.(jpe?g|png|webp|gif|avif)/i);
    await expect(img).toHaveAttribute('srcset', /\d+w/);
    await img.scrollIntoViewIfNeeded();
    await expect
      .poll(() => img.evaluate(el => el.complete && el.naturalWidth > 0))
      .toBe(true);
  }

  // Lightbox still works:
  await images.first().click();
  await expect(page.locator('.pswp')).toBeVisible();
};

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

test('Test Gallery slider layout renders images on the frontend', async ({page, admin, editor}) => {
  await addGalleryAndCheckFrontend(
    {page, admin, editor},
    'Test page for Gallery slider',
    'Slider - The slider is a carousel of images. For more than 5 images, consider using a grid.',
    '.carousel-wrap .carousel-item img'
  );
});

test('Test Gallery grid layout renders images on the frontend', async ({page, admin, editor}) => {
  await addGalleryAndCheckFrontend(
    {page, admin, editor},
    'Test page for Gallery grid',
    'Grid - The grid shows thumbnails of lots of images. Good to use when showing lots of activity.',
    '.grid-row .grid-item img'
  );
});

test('Test Gallery 3 columns layout renders images on the frontend', async ({page, admin, editor}) => {
  await addGalleryAndCheckFrontend(
    {page, admin, editor},
    'Test page for Gallery 3 columns',
    '3 Columns - The 3 columns image display is great for accentuating text, and telling a visual story.',
    '.three-column-images .col img'
  );
});
