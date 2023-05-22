const {test, expect} = require('@playwright/test');
import {login} from './tools/lib/login';

test('checks if the welcome modal on the editor is present and closed when the button is clicked', async ({page, context}) => {
  test.setTimeout(240 * 1000);
  await page.goto('./');
  await login(context);
  await page.goto('./wp-admin/post-new.php?post_type=page');
  const locator = page.locator('.components-modal__screen-overlay');
  await expect(locator).toBeVisible();
  await page.getByRole('button', {name: 'Close'}).click();
  await expect(locator).toBeHidden();
});
