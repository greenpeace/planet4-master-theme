import {test, expect} from '@playwright/test';
import {login} from './tools/lib/login';

test('Create and check check split two column block', async ({page, context}) => {
  await page.goto('./');
  await login(page, context);

  await page.goto('./wp-admin/post-new.php?post_type=page');

  await page.waitForSelector('.components-modal__header');
  await page.locator('.components-modal__header button').click();
  expect(page.locator('.components-modal__header')).toBeHidden();

  // Adding Split Two Column Block
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').type('/split');
  await page.getByRole('option', {name: 'Split Two Columns'}).click();

  const title = page.getByRole('textbox', {name: 'Enter title'});
  const description = page.getByRole('textbox', {name: 'Enter Description'}).first();
  const btn = page.getByRole('textbox', {name: 'Enter button text'});

  await title.click();
  await title.fill('Test Split Two Columns Block');

  await description.click();
  await description.fill('Testing description field...');

  await btn.click();
  await btn.fill('Read more');

  // Check if sidebar is not visible
  const sideBarPanel = page.locator('.edit-post-sidebar__panel-tabs');
  if (await sideBarPanel.isHidden()) {
    await page.locator('.interface-pinned-items button ').click();
  }

  await page.getByRole('combobox', {name: 'Select an issue'}).selectOption('69');
  await page.getByRole('combobox', {name: 'Select a tag'}).selectOption('13');


  // Publish Page
  await page.getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('region', {name: 'Editor publish'}).getByRole('button', {name: 'Publish', exact: true}).click();
  await page.getByRole('region', {name: 'Editor publish'}).getByRole('link', {name: 'View Page'}).click();


  // Assertions
  const h2Title = page.locator('h2.split-two-column-item-title');
  const desc = page.locator('p.split-two-column-item-subtitle').first();
  const link = await page.innerHTML('a.split-two-column-item-link');
  const tag = page.locator('a.split-two-column-item-tag');
  const button = page.locator('a.split-two-column-item-button');

  expect(h2Title).toBeVisible();
  expect(desc).toBeVisible();
  expect(link).toBe('Learn more about this issue');
  expect(tag).toBeVisible();
  expect(button).toBeVisible();
});
