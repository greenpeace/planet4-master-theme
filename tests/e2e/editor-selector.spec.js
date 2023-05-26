const {test, expect} = require('@playwright/test');
import {newPost, publishPost} from './tools/lib/new-post';

test('Test Editor basic functionalities', async ({page, context}) => {
  // Login and create new post.
  await newPost(page, context);

  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').fill('This is a test Post.');
  await page.keyboard.press('Enter');
  await page.locator('p.is-selected.wp-block-paragraph').fill('/youtube');
  await page.keyboard.press('Enter');
  await page.locator('[aria-label="YouTube URL"]').fill('https://youtu.be/3gPvDDHU41E');
  await page.getByRole('button', {name: 'Embed'}).click();

  // Publish post
  await publishPost(page);

  // Asserting new Post contains video, paragraph and title
  const h1 = await page.innerHTML('h1.page-header-title');
  const paragraph = await page.innerHTML('.post-details p');
  const video = page.locator('figure.is-provider-youtube');
  expect(h1).toBe('Test Post');
  expect(paragraph).toBe('This is a test Post.');
  expect(video).toBeVisible();
});
