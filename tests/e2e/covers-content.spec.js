import {test} from './tools/lib/test-utils.js';
import {addCoversBlock, checkCoversBlock} from './tools/lib/covers.js';
import {publishPostAndVisit} from './tools/lib/post.js';

test.useAdminLoggedIn();

test('Test Covers block with Content covers style', async ({page, admin, editor}) => {
  await admin.createNewPost({postType: 'page', title: 'Test Covers block', legacyCanvas: true});

  // Add Covers block.
  await addCoversBlock(page, editor, 'Content');

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Make sure block shows as expected in the frontend.
  await checkCoversBlock(page, 'Content');
});
