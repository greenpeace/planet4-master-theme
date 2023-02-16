const { test, expect } = require('@playwright/test');

import { login } from '../../tools/lib/login';
import { rest } from '../../tools/lib/rest';

test('PLANET-7064 - run ticket specific test', async ({ page, context }) => {
  await page.goto('./');

  const nonce = await login(page, context);

  console.log(`Nonce: ${nonce}`);

  const json = await rest(page, context, {
    path: './wp-json/wp/v2/posts',
    method: 'POST',
    data: {
      title: 'Post for comments',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
      categories: [2], // Energy
    }
  });
  console.log(json);
});
