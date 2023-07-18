import {test as setup} from '../lib/test-utils';

setup('authenticate', async ({page, requestUtils}) => {
  await page.goto('./wp-admin/');
  await requestUtils.setupRest();
});
