const { test, expect } = require('@playwright/test');

test('check Sitemap page', async ({ page }) => {
  await page.goto('/sitemap');

  const sitemapPage = page.locator('.page-sitemap');
  await expect(sitemapPage).toBeVisible();

  // Ensure all types of sitemap entries are there:
  // Act
  await expect(sitemapPage.getByText('Consectetur adipiscing elit')).toBeVisible();

  // Explore
  await expect(sitemapPage.getByText('Energy')).toBeVisible();
  await expect(sitemapPage.getByText('#Coal')).toBeVisible();
  await expect(sitemapPage.getByText('Nature')).toBeVisible();
  await expect(sitemapPage.getByText('#Forests')).toBeVisible();

  // About
  await expect(sitemapPage.getByText('Community Policy')).toBeVisible();

  // Articles
  await expect(sitemapPage.getByText('Press Release')).toBeVisible();
  await expect(sitemapPage.getByText('Publication')).toBeVisible();
  await expect(sitemapPage.getByText('Story')).toBeVisible();
});
