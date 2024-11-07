import {test, expect} from '../tools/lib/test-utils.js';
import {publishPost, updatePost, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  addCategory, addTag, addPostType,
  removeAllPostTypes,
} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Related Posts block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({admin, editor}, {title: 'Test post for Related posts'});

  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('Test paragraph.');

  //
  // Add post category, type and tag
  //
  await editor.openDocumentSettingsSidebar();
  await addCategory({editor}, 'Energy');
  await removeAllPostTypes({editor});
  await addPostType({editor}, 'Press Release');
  await addTag({editor}, 'Renewables');

  //
  // Related articles enabled
  //
  await page.locator('.edit-post-layout__metaboxes').getByRole('combobox', {name: 'Include Articles in Post'}).selectOption('Yes');

  const postUrl = await publishPost({page, editor});
  const editUrl = await page.url();
  await page.waitForTimeout(1000); // letting metabox post query finish

  await page.goto(postUrl);
  const relatedSection = await page.locator('.p4-query-loop');
  await relatedSection.scrollIntoViewIfNeeded();
  await relatedSection.locator('.wp-block-post-template');
  await expect(relatedSection.locator('.wp-block-post-template')).not.toHaveCount(0);

  //
  // Related articles disabled
  //
  await page.goto(editUrl);
  await page.locator('.edit-post-layout__metaboxes').getByRole('combobox', {name: 'Include Articles in Post'}).selectOption('No');
  await updatePost({page});
  await page.waitForTimeout(1000); // letting metabox post query finish

  await page.goto(postUrl);
  await expect(page.locator('.p4-query-loop')).toHaveCount(0);
});
