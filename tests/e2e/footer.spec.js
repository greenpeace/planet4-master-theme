import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('check footer menu', async ({page}) => {
  test.slow();
  const footerMenuNames = ['Footer Primary', 'Footer Secondary', 'Footer Social'];
  const footerMenuLinks = [];

  // Fetch menu details from admin.
  await page.goto('./wp-admin/nav-menus.php');

  for (const footerMenu of footerMenuNames) {
    const menuSelect = page.locator('select#select-menu-to-edit');
    await menuSelect.selectOption({label: footerMenu});

    await Promise.all([
      page.waitForNavigation({waitUntil: 'domcontentloaded'}),
      page.locator('span.submit-btn input.button').click(),
    ]);

    await expect(menuSelect).toHaveValue(await menuSelect.locator(`option:has-text("${footerMenu}")`).getAttribute('value'));

    const menuItemsList = page.locator('ul#menu-to-edit li');
    await expect(menuItemsList.first()).toBeVisible();

    const elementsCount = await menuItemsList.count();
    const menuLinks = [];

    for (let index = 0; index < elementsCount; index++) {
      const element = menuItemsList.nth(index);
      const menuItem = {};
      menuItem.menu = await element.locator('.edit-menu-item-title').inputValue();

      const itemType = (await element.locator('.item-type').innerText()).trim();
      if (itemType === 'Custom Link') {
        menuItem.link = await element.locator('.edit-menu-item-url').inputValue();
      } else {
        menuItem.link = await element.locator('p.link-to-original a').getAttribute('href');
      }

      menuLinks.push(menuItem);
    }
    footerMenuLinks.push({menuname: footerMenu, links: menuLinks});
  }

  await page.goto('./', {waitUntil: 'domcontentloaded'});

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
