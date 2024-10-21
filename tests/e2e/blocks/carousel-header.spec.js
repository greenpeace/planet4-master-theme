import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

test('Create and check carousel header block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Carousel', postType: 'page'});

  // Add block
  await searchAndInsertBlock(page, {blockName: 'Carousel Header'});

  // Filling Carousel details
  let title = page.getByRole('textbox', {name: 'Enter title'});
  await title.click();
  await title.fill('My test Header 1');

  let description = page.getByRole('textbox', {name: 'Enter description'});
  await description.click();
  await description.fill('Testing carousel description 1');

  let ctaBtn = page.getByRole('textbox', {name: 'Enter CTA text'});
  await ctaBtn.click();
  await ctaBtn.fill('Read more 1');

  // // Check if sidebar is not visible
  await editor.openDocumentSettingsSidebar();

  await page.locator('[data-type="toggle"]').nth(0).click();
  await page.getByLabel('Url for link').nth(0).fill('https://google.com');

  await page.getByRole('button', {name: 'Edit'}).click();
  await page.getByRole('button', {name: 'Add image'}).click();
  await page.getByRole('tab', {name: 'Media Library'}).click();
  await page.getByRole('checkbox', {name: 'NATURE-GP0STOE2U'}).click();
  await page.getByRole('button', {name: 'Select', exact: true}).click();

  // Next Slide
  await page.getByRole('button', {name: 'Edit'}).click();
  await page.getByRole('button', {name: 'Add slide'}).click();

  // Re-declare variables
  title = page.getByRole('textbox', {name: 'Enter title'}).nth(1);
  await title.click();
  await title.fill('My test Header 2');

  description = page.getByRole('textbox', {name: 'Enter description'}).nth(1);
  await description.click();
  await description.fill('Testing carousel description 2');

  ctaBtn = page.getByRole('textbox', {name: 'Enter CTA text'}).nth(1);
  await ctaBtn.click();
  await ctaBtn.fill('Read more 2');

  await page.getByRole('button', {name: 'Edit'}).nth(1).click();
  await page.getByRole('button', {name: 'Add image'}).click();
  await page.getByRole('tab', {name: 'Media Library'}).click();
  await page.getByRole('checkbox', {name: 'OCEANS-GP0STOM6C'}).click();
  await page.getByRole('button', {name: 'Select', exact: true}).click();

  await page.locator('[data-type="toggle"]>>nth=1').click();
  await page.getByLabel('Url for link').fill('https://yahoo.com');
  await page.getByLabel('Open in a new tab').check();

  // Publish Page
  await publishPostAndVisit({page, editor});

  // Assertions
  const h2Title1 = await page.innerHTML('.carousel-captions-wrapper h2');
  const paragraphDescription1 = await page.innerHTML('.carousel-captions-wrapper p');
  const ctaButton1 = page.locator('.action-button a').first();

  expect(h2Title1).toBe('My test Header 1');
  expect(paragraphDescription1).toBe('Testing carousel description 1');
  await expect(ctaButton1).toBeVisible();

  await page.waitForSelector('button.carousel-control-next');
  await page.locator('button.carousel-control-next').click();

  const h2Title2 = await page.innerHTML('.carousel-captions-wrapper h2>>nth=1');
  const paragraphDescription2 = await page.innerHTML('.carousel-captions-wrapper p>>nth=1');
  const ctaButton2 = page.locator('.action-button a').nth(1);

  expect(h2Title2).toBe('My test Header 2');
  expect(paragraphDescription2).toBe('Testing carousel description 2');
  await expect(ctaButton2).toBeVisible();
});
