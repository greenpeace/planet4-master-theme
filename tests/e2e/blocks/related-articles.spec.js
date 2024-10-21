import {test, expect} from '../tools/lib/test-utils.js';
import {publishPost, updatePost, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {addCategory, addTag, addPostType, removeAllPostTypes, searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

test('Test Related Articles block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test post for Related articles'});

  await searchAndInsertBlock(page, {blockName: 'paragraph'});
  await (await page.waitForSelector('p[data-type="core/paragraph"]')).click();
  await page.keyboard.type('Test content used as a post excerpt.');

  //
  // Add post category, type and tag
  //
  await addCategory({page, editor}, 'Energy');
  await removeAllPostTypes({page, editor});
  await addPostType({page, editor}, 'Press Release');
  await addTag({page, editor}, 'Renewables');

  //
  // Related articles enabled
  //
  await page.locator('.edit-post-layout__metaboxes').getByRole('combobox', {name: 'Include Articles in Post'}).selectOption('Yes');

  const postUrl = await publishPost({page, editor});
  const editUrl = page.url();
  await page.waitForTimeout(1000); // letting metabox post query finish

  await page.goto(postUrl);
  const relatedSection = page.locator('[data-render="planet4-blocks/articles"]');
  await relatedSection.scrollIntoViewIfNeeded();
  relatedSection.locator('.article-list-item');
  await expect(relatedSection.locator('.article-list-item')).not.toHaveCount(0);

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
