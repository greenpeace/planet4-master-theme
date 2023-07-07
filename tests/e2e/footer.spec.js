import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('check footer menu', async ({page}) => {
  const footerMenuNames = ['Footer Primary', 'Footer Secondary', 'Footer Social'];
  const footerMenuLinks = [];

  // Fetch menu details from admin.
  await page.goto('./wp-admin/nav-menus.php');

  for (const footerMenu of footerMenuNames) {
    await page.locator('select#select-menu-to-edit').selectOption({label: footerMenu});
    await page.locator('span.submit-btn input.button').click();

    const arrayOfLocators = page.locator('ul#menu-to-edit li');
    const elementsCount = await arrayOfLocators.count();
    const menuLinks = [];

    for (let index = 0; index < elementsCount; index++) {
      const element = await arrayOfLocators.nth(index);
      const menuItem = [];
      menuItem.menu = await element.locator('.edit-menu-item-title').inputValue();

      const itemType = await element.locator('.item-type').innerText();
      if (itemType === 'Custom Link') {
        menuItem.link = await element.locator('.edit-menu-item-url').inputValue();
      } else {
        menuItem.link = await element.locator('p.link-to-original a').getAttribute('href');
      }

      menuLinks.push(menuItem);
    }
    footerMenuLinks.push({menuname: footerMenu, links: menuLinks});
  }

  // Test fetched menu details on frontend.
  await page.goto('./');

  await expect(page.locator('.site-footer')).toBeVisible();
  await expect(page.locator('.site-footer--minimal')).toBeHidden();
  await expect(page.locator('.footer-social-media')).toBeVisible();
  await expect(page.locator('.footer-menu')).toBeVisible();
  await expect(page.locator('.copyright')).toBeVisible();

  for (const footerMenuLink of footerMenuLinks) {
    for (const menuItem of footerMenuLink.links) {
      if (footerMenuLink.menuname !== 'Footer Social') {
        await expect(page.locator(`.footer-menu a:has-text("${menuItem.menu}")`)).toHaveAttribute('href', menuItem.link);
      } else {
        await expect(page.locator(`.footer-social-media a[data-ga-label="${menuItem.menu}"]`)).toHaveAttribute('href', menuItem.link);
      }
    }
  }
});
