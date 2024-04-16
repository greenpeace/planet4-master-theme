import {test, expect} from './tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from './tools/lib/post.js';

test.useAdminLoggedIn();

test('Create and check check split two column block', async ({page, admin, editor}) => {
  await admin.visitAdminPage('admin.php', 'page=planet4_settings_navigation');
  const checkbox = page.locator('#new_ia');
  const featureIsActiveOnInstance = await checkbox.isChecked();
  if (featureIsActiveOnInstance) {
    test.skip();
  }

  await createPostWithFeaturedImage({admin, editor}, {title: 'Test S2C block', postType: 'page'});

  // Adding Split Two Column Block
  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('/split');
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
  await publishPostAndVisit({page, editor});

  // Assertions
  const h2Title = page.locator('h2.split-two-column-item-title');
  const desc = page.locator('p.split-two-column-item-subtitle').first();
  const link = await page.innerHTML('a.split-two-column-item-link');
  const tag = page.locator('a.split-two-column-item-tag');
  const button = page.locator('a.split-two-column-item-button');

  await expect(h2Title).toBeVisible();
  await expect(desc).toBeVisible();
  expect(link).toBe('Learn more about this issue');
  await expect(tag).toBeVisible();
  await expect(button).toBeVisible();
});
