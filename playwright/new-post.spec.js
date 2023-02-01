const { test, expect } = require('@playwright/test');

test('create a new post', async ({ page, context }) => {
  await page.goto('/wp-admin/');

  await page.type('#user_login', 'admin');
  await page.type('#user_pass', 'admin');
  await page.getByText('Log In').click();

  await expect(page.locator('#wpadminbar')).toBeVisible();

  const nonce = await page.evaluate('window.wpApiSettings.nonce');

  const response = await context.request.post('/wp-json/wp/v2/posts', {
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce
    },
    data: {
      title: 'Post for comments',
      content: '<!-- wp:paragraph --><p>Random content</p><!-- /wp:paragraph -->',
      status: 'publish',
      categories: [2], // Energy
    }
  });

  expect(response.ok()).toBeTruthy();
  // access post data by response.body()
});
