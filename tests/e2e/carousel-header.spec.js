import {test, expect} from '@playwright/test';
import {login} from './tools/lib/login';

test('Create and check carousel header block', async ({page, context}) => {
  await page.goto('./');
  await login(page, context);

  await page.goto('./wp-admin/post-new.php?post_type=page');

  // Using Editor to find Carousel Block
  await page.getByRole('button', {name: 'Close'}).click();
  const secondCloseBtn = page.getByRole('button', {name: 'Close'});
  if (await secondCloseBtn.isVisible()) {
    await secondCloseBtn.click();
  }

  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').fill('/carousel');
  await page.getByRole('option', {name: 'Carousel Header'}).click();

  // Filling Carousel details
  const title = page.getByRole('textbox', {name: 'Enter title'});
  const description = page.getByRole('textbox', {name: 'Enter description'});
  const ctaBtn = page.getByRole('textbox', {name: 'Enter CTA text'});

  await title.click();
  await title.fill('My test Header 1');

  await description.click();
  await description.fill('Testing carousel description 1');

  await ctaBtn.click();
  await ctaBtn.fill('Read more 1');

  // Check if sidebar is not visible
  const sideBarPanel = page.locator('.edit-post-sidebar__panel-tabs');
  if (await sideBarPanel.isHidden()) {
    await page.locator('.interface-pinned-items button ').click();
  }

  await page.click('[data-type="toggle"]');
  await page.getByLabel('Url for link').fill('https://google.com');

  await page.getByRole('button', {name: 'Edit'}).click();
  await page.getByRole('button', {name: 'Add image'}).click();
  await page.getByRole('tab', {name: 'Media Library'}).click();
  await page.getByRole('checkbox', {name: 'OCEANS-GP0STOM6C'}).click();
  await page.getByRole('button', {name: 'Select', exact: true}).click();

  // Next Slide
  await page.getByRole('button', {name: 'Edit'}).click();
  await page.getByRole('button', {name: 'Add slide'}).click();

  const title2 = page.getByRole('textbox', {name: 'Enter title'}).nth(1);
  const description2 = page.getByRole('textbox', {name: 'Enter description'}).nth(1);
  const ctaBtn2 = page.getByRole('textbox', {name: 'Enter CTA text'}).nth(1);

  await title2.click();
  await title2.fill('My test Header 2');

  await description2.click();
  await description2.fill('Testing carousel description 2');

  await ctaBtn2.click();
  await ctaBtn2.fill('Read more 2');
  await page.click('[data-type="toggle"]>>nth=1');
  await page.getByLabel('Url for link').fill('https://google.com');
  await page.getByLabel('Open in a new tab').check();

  await page.getByRole('button', {name: 'Edit'}).nth(1).click();
  await page.getByRole('button', {name: 'Add image'}).click();
  await page.getByRole('tab', {name: 'Media Library'}).click();
  await page.getByRole('checkbox', {name: 'OCEANS-GP0STOM6C'}).click();
  await page.getByRole('button', {name: 'Select', exact: true}).click();

  // Publish Page
  await page.getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('region', {name: 'Editor publish'}).getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('region', {name: 'Editor publish'}).getByRole('link', {name: 'View Page'}).click();


  // Assertions
  const h2Title1 = await page.innerHTML('.carousel-captions-wrapper h2');
  const paragraphDescription1 = await page.innerHTML('.carousel-captions-wrapper p');
  const ctaButton1 = page.locator('.action-button a').first();

  expect(h2Title1).toBe('My test Header 1');
  expect(paragraphDescription1).toBe('Testing carousel description 1');
  expect(ctaButton1).toBeVisible();

  await page.locator('button.carousel-control-next').click();

  const h2Title2 = await page.innerHTML('.carousel-captions-wrapper h2>>nth=1');
  const paragraphDescription2 = await page.innerHTML('.carousel-captions-wrapper p>>nth=1');
  const ctaButton2 = page.locator('.action-button a').nth(1);

  expect(h2Title2).toBe('My test Header 2');
  expect(paragraphDescription2).toBe('Testing carousel description 2');
  expect(ctaButton2).toBeVisible();

});
