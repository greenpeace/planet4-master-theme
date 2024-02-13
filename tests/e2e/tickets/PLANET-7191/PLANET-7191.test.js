import {test, expect} from '../../tools/lib/test-utils.js';

test('check search filters toggles', async ({page}) => {
  await page.goto('./?s=');

  const actionLink = await page.getByRole('link', {name: 'Action Type'});
  const contentLink = await page.getByRole('link', {name: 'Content Type'});
  const actionList = await page.locator('.filteritem').filter({has: actionLink}).getByRole('tabpanel');
  const contentList = await page.locator('.filteritem').filter({has: contentLink}).getByRole('tabpanel');

  await expect(actionList).toBeVisible();
  await expect(contentList).toBeVisible();

  await actionLink.click();
  await expect(actionList).toBeHidden();
  await expect(contentList).toBeVisible();

  await contentLink.click();
  await expect(actionList).toBeHidden();
  await expect(contentList).toBeHidden();

  await contentLink.click();
  await expect(actionList).toBeHidden();
  await expect(contentList).toBeVisible();

  await actionLink.click();
  await expect(actionList).toBeVisible();
  await expect(contentList).toBeVisible();
});
