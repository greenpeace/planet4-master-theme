import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor';

const TEST_TITLE = 'Related Stories';
const TEST_CATEGORY = 'Energy';

test.useAdminLoggedIn();

test.describe('Test Posts List block', () => {
  // This is the default layout, so we don't need to select it manually.
  test('Test the List layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Posts List List Layout', postType: 'page'});

    // Add Posts List block.
    await searchAndInsertBlock({page}, 'Posts List');

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

    // Publish page.
    await publishPostAndVisit({page, editor});

    // Test that the block is displayed as expected in the frontend.
    const block = page.locator('.p4-query-loop');
    await expect(block).toHaveClass(/is-custom-layout-list/);
    await expect(block.locator('h2.wp-block-heading')).toHaveText(TEST_TITLE);
    await expect(block.locator('.wp-block-post')).toHaveCount(4);
    for (const category of await block.locator('.taxonomy-category').all()) {
      await expect(category).toHaveText(TEST_CATEGORY);
    }
  });
});
