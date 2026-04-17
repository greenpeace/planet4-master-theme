import {searchAndInsertBlock} from './editor.js';
import {expect} from './test-utils.js';

const TEST_TITLE = 'Related Stories';
const TEST_CATEGORY = 'Energy';

async function addPostsListBlock(page, layout) {
  // Add Posts List block.
  await searchAndInsertBlock({page}, 'Posts List');

  // Select wanted layout. If none is provided it means we are using the default one which is List.
  if (layout) {
    await page.getByRole('radio', {name: layout}).check();
  }

  // Change amount of posts from 3 to 4.
  await page.getByRole('spinbutton', {name: 'Items per page'}).fill('4');

  // Filter by "Energy" category.
  const editorSettings = page.getByRole('region', {name: 'Editor settings'});
  await editorSettings.getByRole('button', {name: 'Filters options'}).click();
  await page.getByLabel('Show Taxonomies').click();
  await editorSettings.getByLabel('Categories').fill(TEST_CATEGORY);
  await editorSettings.locator(
    '.components-form-token-field__suggestion', {hasText: TEST_CATEGORY}
  ).click();
  await expect(editorSettings.locator(
    '.components-form-token-field__token-text', {hasText: TEST_CATEGORY})
  ).toBeVisible();

  // Change the title.
  await page.getByRole('document', {name: 'Block: Heading'}).fill(TEST_TITLE);
}

async function checkPostsListBlock(page, layout) {
  // Test that the block is displayed as expected in the frontend.
  const block = page.locator('.p4-query-loop');
  await expect(block).toContainClass(`is-custom-layout-${layout}`);
  await expect(block.locator('h2.wp-block-heading')).toHaveText(TEST_TITLE);
  await expect(block.locator('.wp-block-post')).toHaveCount(4);
  for (const category of await block.locator('.wp-block-post-terms:not(.taxonomy-post_tag)').all()) {
    await expect(category).toHaveText(TEST_CATEGORY);
  }
}

export {addPostsListBlock, checkPostsListBlock};
