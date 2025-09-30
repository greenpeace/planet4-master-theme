import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

const CATEGORY = 'Nature';

test.useAdminLoggedIn();

test('Test Topic Link block', async ({page, admin, editor, baseURL}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Topic Link'});

  // Add Topic Link block.
  await searchAndInsertBlock({page}, 'Topic Link');

  // Select the Nature category
  await page.getByRole('combobox', {name: 'Select Category:'}).selectOption({label: CATEGORY});

  // Add a background image.
  await page.getByRole('button', {name: 'Select Background Image'}).click();
  const imageModal = page.getByRole('dialog', {name: 'Select or Upload Media'});
  await imageModal.locator('[data-id="357"]').click();
  await page.getByRole('button', {name: 'Select', exact: true}).click();
  await expect(imageModal).toBeHidden();

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Check that the block is correctly rendered in the frontend.
  // On local the baseURL doesn't end with '/' so we need to add it manually.
  const href = `${baseURL}${baseURL.endsWith('/') ? '' : '/'}category/${CATEGORY.toLowerCase()}/`;
  await expect(page.locator('.topic-link-block > a')).toHaveAttribute('href', href);
  await expect(page.locator('.topic-link-content > p')).toHaveText(`Learn more about ${CATEGORY}`);
});

