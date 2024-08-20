import {test, expect} from './test-utils.js';

test.useAdminLoggedIn();

async function reSync(page) {
  // Go to ElasticSearch sync page
  await page.goto('./wp-admin/admin.php?page=elasticpress-sync');

  const mainButton = page.locator('.ep-sync-controls button.is-primary');
  const checkBox = page.locator('#inspector-checkbox-control-0');
  const syncProgress = page.locator('.ep-sync-progress__details');

  let mainButtonText = await mainButton.allInnerTexts();
  let syncProgressText = await syncProgress.allInnerTexts();

  // If the sync was already executed, stop it
  if (mainButtonText.includes('Stop sync')) {
    expect(syncProgressText.includes('Sync paused'));
    await mainButton.click();
    mainButtonText = await mainButton.allInnerTexts();
  }

  // Click on the checkbox and execute the sync process
  await checkBox.check();
  await mainButton.click();

  expect(mainButtonText.includes('Start sync'));
  expect(syncProgressText.includes('Sync in progress'));

  // Wait for the sync to complete
  await page.waitForFunction(
    () => document.querySelector('.ep-sync-progress__details').innerText.includes('Sync complete')
  );

  syncProgressText = await syncProgress.allInnerTexts();
  expect(syncProgressText.includes('Sync complete'));
}

export {reSync};
