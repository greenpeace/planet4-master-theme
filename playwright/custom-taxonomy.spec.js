const { test, expect } = require('@playwright/test');

test('check a custom taxonomy (story) page', async ({ page }) => {
  await page.goto('/story');

  await expect(page.locator('h1', { hasText: 'Story' })).toBeVisible();

  // Test author link in listing.
  await page.getByText('Nikos').click();
  await expect(page).toHaveURL('/author/nroussos/');

  // Test author page.
  await expect(page.locator('h1', { hasText: 'Nikos' })).toBeVisible();
});

