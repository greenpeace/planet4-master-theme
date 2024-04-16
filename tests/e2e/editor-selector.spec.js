import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from './tools/lib/post.js';

const TEST_TITLE = 'Test post';
const TEST_TEXT = 'This is a test post.';

test.useAdminLoggedIn();

test('Test Editor basic functionalities', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({admin, editor}, {title: TEST_TITLE});

  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type(TEST_TEXT);
  await page.keyboard.press('Enter');
  await page.keyboard.type('/youtube');
  await page.getByRole('option', {name: 'Youtube'}).click();

  const block = page.getByRole('document', {name: 'Block: Youtube'});
  await block.waitFor();
  await block.getByRole('textbox').fill('https://youtu.be/3gPvDDHU41E');
  await block.getByRole('button', {name: 'Embed'}).click();

  // Publish post
  await publishPostAndVisit({page, editor});

  // Asserting new Post contains video, paragraph and title
  const h1 = await page.innerHTML('h1.page-header-title');
  const paragraph = await page.innerHTML('.post-details p');
  const video = page.locator('figure.is-provider-youtube');
  expect(h1).toBe(TEST_TITLE);
  expect(paragraph).toBe(TEST_TEXT);
  await expect(video).toBeVisible();
});
