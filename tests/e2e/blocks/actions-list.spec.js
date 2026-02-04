import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor';

const TEST_TITLE = 'Campaigns';
const TEST_CATEGORY = 'Energy';

test.useAdminLoggedIn();

test.describe('Test Actions List block', () => {
  // This is the default layout, so we don't need to select it manually.
  test.skip('Test the Grid layout', async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Actions List Grid Layout', postType: 'page'});

    // Add Actions List block.
    await searchAndInsertBlock({page}, 'Actions List');

    // Set Actions per page to 2.
    await page.getByRole('spinbutton', {name: 'Items per page'}).fill('2');

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
    await expect(block).toHaveClass(/is-custom-layout-grid/);
    await expect(block.locator('h2.wp-block-heading')).toHaveText(TEST_TITLE);
    await expect(block.locator('.wp-block-post')).toHaveCount(2);
    for (const category of await block.locator('.taxonomy-category').all()) {
      await expect(category).toHaveText(TEST_CATEGORY);
    }
  });
});
