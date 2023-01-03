const { test, expect } = require('@playwright/test');

test.describe('Footer', () => {
  test.beforeEach(async ({ page }) => {
    // Go to the starting url before each test.
    await page.goto('/');
  });

  test('check the footer looks good', async ({ page }) => {
    await expect(page.locator('.site-footer')).toBeVisible();
    await expect(page.locator('.site-footer--minimal')).toBeHidden();
    await expect(page.locator('.footer-social-media')).toBeVisible();
    await expect(page.locator('.footer-menu')).toBeVisible();
    await expect(page.locator('.copyright')).toBeVisible();
  });

  test('check the country selector behaviour', async ({ page }) => {
    await expect(page.locator('.footer-country-selector')).toBeVisible();
    await page.getByRole('button', { name: 'Toggle worldwide site selection menu' }).click();
    await expect(page.locator('.countries')).toBeVisible();
  });
});
