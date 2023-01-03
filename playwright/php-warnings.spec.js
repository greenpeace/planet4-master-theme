const { test, expect } = require('@playwright/test');

test('check there are no PHP warnings', async ({ page }) => {
  await page.goto('/');
  const content = await page.content();
  expect(content.indexOf('<b>Warning</b>:')).toEqual(-1);
});

