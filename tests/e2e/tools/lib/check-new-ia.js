/**
 * Checks if the new IA is enabled.
 *
 * @param {import('@playwright/test').Admin} admin - The Playwright admin instance.
 * @param {import('@playwright/test').Page}  page  - The Playwright page instance.
 */
async function isNewIAEnabled (admin, page) {
  await admin.visitAdminPage('admin.php', 'page=planet4_settings_navigation');
  await page.waitForSelector('#new_ia');
  const isEnabled = await page.locator('#new_ia').isChecked();
  return isEnabled;
}

export {isNewIAEnabled};
