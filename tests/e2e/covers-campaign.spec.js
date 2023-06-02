const {test} = require('@playwright/test');
import {newPage, publishPage} from './tools/lib/new-page';
import {addCoversBlock, checkCoversBlock} from './tools/lib/covers';

test('Test Covers block with Campaign covers style', async ({page, context}) => {
  // Login and create new page.
  await newPage(page, context);

  // Add Covers block.
  await addCoversBlock(page, 'Campaign');

  // Publish page.
  await publishPage(page);

  // Make sure block shows as expected in the frontend.
  await checkCoversBlock(page, 'Campaign');
});
