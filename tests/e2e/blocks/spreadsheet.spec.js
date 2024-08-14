import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';

const SHEET_ID = '2PACX-1vR2LTvb__ifqY0ayZzqWyzkJGPyMUyUvili9YotHs_1YymJqjSeECFImhzlJfN3k9xw0CVBwR4HuTOg';
const TEST_URL = `https://docs.google.com/spreadsheets/d/e/${SHEET_ID}/pubhtml`;

test.useAdminLoggedIn();

test('Test Spreadsheet block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({admin, editor}, {title: 'Test Spreadsheet', postType: 'page'});

  // Add Spreadsheet block.
  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('/spreadsheet');
  await page.getByRole('option', {name: 'Spreadsheet'}).click();

  // Check that the "empty URL" warning is displayed.
  const warning = page.locator('.block-edit-mode-warning');
  await expect(warning).toBeVisible();
  await expect(warning).toHaveText('No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.');

  const apiRoute = `./wp-json/planet4/v1/get-spreadsheet-data?sheet_id=${SHEET_ID}`;
  const apiResponse = {
    header: ['Some 30x30 commitment', '30x30 in the global oceans', 'Highly/fully protected sanctuaries'],
    rows: [
      ['Albania', 'Yes', ''],
      ['Angola', '', ''],
      ['Antigua and Barbuda', 'Yes', ''],
    ],
  };

  // Add URL.
  await page.route(`${apiRoute}&_locale=user`, async route => {
    await route.fulfill({json: apiResponse});
  });
  const responsePromise = page.waitForResponse(`${apiRoute}&_locale=user`);
  await page.getByLabel('Spreadsheet URL').click();
  await page.getByLabel('Spreadsheet URL').fill(TEST_URL);
  const response = await responsePromise;
  expect(response.status()).toEqual(200);

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
  await publishPostAndVisit({page, editor});

  // Test that the block is displayed as expected in the frontend.
  await page.route(apiRoute, async route => {
    await route.fulfill({json: apiResponse});
  });
  const frontendResponse = await page.waitForResponse(apiRoute);
  expect(frontendResponse.status()).toEqual(200);
  const frontendTable = page.locator('table.spreadsheet-table');
  await expect(frontendTable.locator('thead th:first-child button')).toHaveText('Some 30x30 commitment');
  await expect(frontendTable.locator('tbody tr:first-child td:first-child')).toHaveText('Albania');
  await expect(frontendTable).toHaveClass(/is-color-green/);
});

