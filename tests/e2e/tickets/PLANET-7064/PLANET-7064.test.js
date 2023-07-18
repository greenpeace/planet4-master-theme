/* eslint-disable no-console */
const {test} = require('../../tools/lib/test-utils');

test.useAdminLoggedIn();

test('PLANET-7064 - run ticket specific test', async ({admin, requestUtils}) => {
  await admin.visitAdminPage('admin.php');

  const json = await requestUtils.rest({
    path: './wp-json/wp/v2/posts',
    method: 'POST',
    data: {
      title: 'Post for comments',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
      categories: [2], // Energy
    },
  });
  console.log(json);
});
