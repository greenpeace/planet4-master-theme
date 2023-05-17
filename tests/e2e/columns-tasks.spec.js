const {test, expect} = require('@playwright/test');
import {login} from './tools/lib/login';

const TEST_LINKS = ['/act', '/explore', '/'];

test('Test Columns block with Tasks style', async ({page, context}) => {
  // Login.
  await page.goto('./');
  await login(context);

  // Create and navigate to new page.
  await page.goto('./wp-admin/post-new.php?post_type=page');

  // Need to close modal so test can continue.
  await page.waitForSelector('.components-modal__header');
  await page.locator('.components-modal__header button').click();
  expect(page.locator('.components-modal__header')).toBeHidden();

  // Fill in page title.
  await page.locator('.editor-post-title__input').click();
  await page.locator('h1.editor-post-title').fill('Test Page');

  // Add Columns block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').fill('/planet-4-columns');
  await page.keyboard.press('Enter');

  // Select the Tasks style.
  const stylePicker = page.locator('.block-editor-block-styles__variants');
  await stylePicker.locator('button[aria-label^="Tasks"]').click();

  // Fill in the Columns links.
  await page.locator('input[placeholder="Enter link for column 1"]').fill(TEST_LINKS[0]);
  await page.locator('input[placeholder="Enter link for column 2"]').fill(TEST_LINKS[1]);
  await page.locator('input[placeholder="Enter link for column 3"]').fill(TEST_LINKS[2]);

  // Fill in the other fields.
  const backendColumns = await page.locator('.column-wrap').all();
  for (const [index, column] of backendColumns.entries()) {
    await column.locator('h5').fill(`Column ${index + 1}`);
    await column.locator('p').fill(`Description ${index + 1}`);
    await column.locator('div[aria-label="Enter column button text"]').fill(`Button ${index + 1}`);
  }

  // Publish page.
  await page.getByRole('button', { name: 'Publish', exact: true }).click();
  await page.getByRole('region', { name: 'Editor publish' }).getByRole('button', { name: 'Publish', exact: true }).click();
  await page.getByRole('link', { name: 'View Page', exact: true }).first().click();

  // Make sure block shows as expected in the frontend.
  const frontendColums = await page.locator('.column-wrap').all();
  for (const [index, column] of frontendColums.entries()) {
    expect(column.locator('.step-number')).toBeVisible();
    expect(await column.innerHTML('h5')).toBe(`Column ${index + 1}`);
    expect(await column.innerHTML('p')).fill(`Description ${index + 1}`);
    const button = column.locator('a.btn-secondary');
    expect(button).toHaveText(`Button ${index + 1}`);
    expect(button.getAttribute('href')).toBe(TEST_LINKS[index]);
  }
});
