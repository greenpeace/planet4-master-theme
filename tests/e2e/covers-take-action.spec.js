const {test} = require('@playwright/test');
import {newPage, publishPage} from './tools/lib/new-page';
import {addCoversBlock, checkCoversBlock} from './tools/lib/covers';

test('Test Covers block with Take Action covers style', async ({page, context}) => {
  test.setTimeout(480 * 1000);
  // Login and create new page.
  await newPage(page, context);

  // Add Covers block.
  await addCoversBlock(page, 'Take Action');

  // Publish page.
  await publishPage(page);

  // Make sure block shows as expected in the frontend.
  await checkCoversBlock(page, 'Take Action');
});
