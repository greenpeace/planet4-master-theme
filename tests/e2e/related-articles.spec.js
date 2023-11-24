import {test, expect} from './tools/lib/test-utils.js';
import {publishPost, updatePost} from './tools/lib/post.js';
import {
  openPostSettingsPanel,
  addCategory, addTag, addPostType,
  removeAllPostTypes, addFeaturedImage,
} from './tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Related Articles block', async ({page, admin, editor}) => {
  await admin.createNewPost({postType: 'post', title: 'Test post for Related articles', legacyCanvas: true});

  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('Test paragraph.');

  //
  // Add post category, type and tag
  //
  await openPostSettingsPanel({editor, page});
  await addCategory({editor}, 'Energy');
  await removeAllPostTypes({editor});
  await addPostType({editor}, 'Press Release');
  await addTag({editor}, 'Renewables');
  await addFeaturedImage({editor}, 328);

  //
  // Related articles enabled
  //
  await page.locator('.edit-post-layout__metaboxes').getByRole('combobox', {name: 'Include Articles in Post'}).selectOption('Yes');

  const postUrl = await publishPost({page, editor});
  const editUrl = await page.url();
  await page.waitForTimeout(1000); // letting metabox post query finish

  await page.goto(postUrl);
  const relatedSection = await page.locator('[data-render="planet4-blocks/articles"]');
  await relatedSection.scrollIntoViewIfNeeded();
  await relatedSection.locator('.article-list-item');
  await expect(relatedSection.locator('.article-list-item')).not.toHaveCount(0);

  //
  // Related articles disabled
  //
  await page.goto(editUrl);
  await page.locator('.edit-post-layout__metaboxes').getByRole('combobox', {name: 'Include Articles in Post'}).selectOption('No');
  await updatePost({page});
  await page.waitForTimeout(1000); // letting metabox post query finish

  await page.goto(postUrl);
  await expect(page.locator('[data-render="planet4-blocks/articles"]')).toHaveCount(0);
});
