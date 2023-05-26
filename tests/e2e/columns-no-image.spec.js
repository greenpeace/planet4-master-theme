const {test} = require('@playwright/test');
import {newPage, publishPage} from './tools/lib/new-page';
import {addColumnsBlock, checkColumnsBlock} from './tools/lib/columns';

test('Test Columns block with No Image style', async ({page, context}) => {
  // Login and create new page.
  await newPage(page, context);

  // Add Columns block.
  await addColumnsBlock(page);

  // Publish page.
  await publishPage(page);

  // Make sure block shows as expected in the frontend.
  await checkColumnsBlock(page);
});
