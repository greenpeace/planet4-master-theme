import {searchAndInsertBlock} from './editor.js';
import {expect} from './test-utils.js';

const TEST_TITLE = 'Related Stories';
const TEST_CATEGORY = 'Energy';
const MANUAL_OVERRIDE_TITLE = 'Posts';

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

async function addPostsListBlockWithManualOverride(page, postTitles) {
  // Add Posts List block (default List layout).
  await searchAndInsertBlock({page}, 'Posts List');

  // Change amount of posts from 3 to 4.
  await page.getByRole('spinbutton', {name: 'Items per page'}).fill('4');

  // Expand the "Manual override" panel and select posts.
  const editorSettings = page.getByRole('region', {name: 'Editor settings'});
  const manualOverridePanel = editorSettings.getByRole('button', {name: 'Manual override'});
  if (await manualOverridePanel.getAttribute('aria-expanded') === 'false') {
    await manualOverridePanel.click();
  }

  // Select each post via the token field autocomplete.
  for (const title of postTitles) {
    await editorSettings.locator('.components-form-token-field__input').fill(title);
    // Use .first() to guard against duplicate suggestions
    await editorSettings.locator(
      '.components-form-token-field__suggestion', {hasText: title}
    ).first().click();
    await expect(editorSettings.locator(
      '.components-form-token-field__token-text', {hasText: title})
    ).toBeVisible();
  }

  // Change the block title.
  await page.getByRole('document', {name: 'Block: Heading'}).fill(MANUAL_OVERRIDE_TITLE);
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

async function checkPostsListBlockWithManualOverride(page, postTitles) {
  const block = page.locator('.p4-query-loop');
  await expect(block).toContainClass('is-custom-layout-list');
  await expect(block.locator('h2.wp-block-heading')).toHaveText(MANUAL_OVERRIDE_TITLE);
  await expect(block.locator('.wp-block-post')).toHaveCount(postTitles.length);
  for (const title of postTitles) {
    await expect(block.locator('.wp-block-post-title', {hasText: title})).toBeVisible();
  }
}

export {addPostsListBlock, addPostsListBlockWithManualOverride, checkPostsListBlock, checkPostsListBlockWithManualOverride};
