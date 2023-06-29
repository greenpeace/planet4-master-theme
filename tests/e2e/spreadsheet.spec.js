const {test, expect} = require('@playwright/test');
import {newPage, publishPage} from './tools/lib/new-page';

const TEST_URL = 'https://docs.google.com/spreadsheets/d/e/2PACX-1vR2LTvb__ifqY0ayZzqWyzkJGPyMUyUvili9YotHs_1YymJqjSeECFImhzlJfN3k9xw0CVBwR4HuTOg/pubhtml';

test('Test Spreadsheet block', async ({page, context}) => {
  // Login and create new page.
  await newPage(page, context);

  // Add Spreadsheet block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').type('/spreadsheet');
  await page.keyboard.press('Enter');

  // Check that the "empty URL" warning is displayed.
  const warning = page.locator('.block-edit-mode-warning');
  await expect(warning).toBeVisible();
  await expect(warning).toHaveText('No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.');

  // Add URL.
  await page.getByLabel('Spreadsheet URL').click();
  await page.getByLabel('Spreadsheet URL').fill(TEST_URL);

  // Check that the warning is now hidden.
  await expect(warning).toBeHidden();

  // Test that the data is properly displayed in the editor.
  const editorTable = page.locator('table.spreadsheet-table');
  await expect(editorTable.locator('thead th:first-child button')).toHaveText('Some 30x30 commitment');
  await expect(editorTable.locator('tbody tr:first-child td:first-child')).toHaveText('Albania');

  // Change table color to green and make sure it's applied.
  await page.locator('button[aria-label="Color: green"]').click();
  await expect(editorTable).toHaveClass(/is-color-green/);

  // Publish page.
  await publishPage(page);

  // Test that the block is displayed as expected in the frontend.
  const frontendTable = page.locator('table.spreadsheet-table');
  await expect(frontendTable.locator('thead th:first-child button')).toHaveText('Some 30x30 commitment');
  await expect(frontendTable.locator('tbody tr:first-child td:first-child')).toHaveText('Albania');
  await expect(frontendTable).toHaveClass(/is-color-green/);
});

