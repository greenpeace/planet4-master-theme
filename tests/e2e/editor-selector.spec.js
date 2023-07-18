import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit} from './tools/lib/post.js';

test.useAdminLoggedIn();

test('Test Editor basic functionalities', async ({page, admin, editor}) => {
  await admin.createNewPost({postType: 'post', title: 'Test Post', legacyCanvas: true});

  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('This is a test Post.');
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
  expect(h1).toBe('Test Post');
  expect(paragraph).toBe('This is a test Post.');
  await expect(video).toBeVisible();
});
