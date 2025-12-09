import {test, expect} from './tools/lib/test-utils.js';
import {join, resolve} from 'node:path';
import {readFileSync, writeFileSync, mkdirSync, unlinkSync, existsSync} from 'node:fs';
import {createHash} from 'node:crypto';

test.useAdminLoggedIn();

/**
 * Switches the WordPress Media Library UI to "List" view if it's not already active.
 *
 * @async
 * @param {import('@playwright/test').Page} page - Playwright page instance.
 */
async function switchMediaLibraryToListView(page) {
  await page.waitForSelector('.view-switch');
  const isListView = await page.locator('.view-list').evaluate(el =>
    el.classList.contains('current')
  );
  if (!isListView) {
    await page.click('.view-list');
    await page.waitForURL('**/upload.php?mode=list');
  }
}

/**
 * Uploads a media file through the WordPress Media Library file input.
 *
 * @async
 * @param {import('@playwright/test').Page} page         - The Playwright page instance.
 * @param {string | Buffer}                 originalFile - File to upload.
 */
async function uploadMediaFile(page, originalFile) {
  await page.waitForSelector('#plupload-browse-button', {state: 'visible'});
  const fileInput = page.locator('input[type="file"][id^="html5_"]');
  await fileInput.waitFor({state: 'attached'});
  await fileInput.setInputFiles(originalFile);
}

/**
 * Replaces an already-uploaded media file using the Media Replacer plugin UI.
 *
 * @async
 * @param {import('@playwright/test').Page}                          page    - The Playwright page instance.
 * @param {string | Buffer | import('@playwright/test').FilePayload} newFile - Replacement file.
 */
async function replaceMediaFile(page, newFile) {
  const replaceButton = page.locator('.media-replacer-button');
  const fileInput = page.locator('input.replace-media-file');

  await replaceButton.click();

  await fileInput.setInputFiles(newFile);

  await page.waitForLoadState('domcontentloaded');
}

/**
 * Ensures that the "Stateless" mode is enabled in the WP Stateless plugin settings.
 * If not enabled, the test is skipped.
 *
 * @async
 * @param {import('@playwright/test').Page}     page     - Playwright page instance.
 * @param {import('@playwright/test').TestInfo} testInfo - Playwright TestInfo object used for skipping.
 */
async function ensureStatelessModeEnabled(page, testInfo) {
  await page.goto('./wp-admin/upload.php?page=stateless-settings');

  const isStatelessChecked = await page.locator('#sm_mode_stateless').isChecked();

  if (!isStatelessChecked) {
    testInfo.skip('Skipping: Stateless mode is NOT active.');
  }
}

/**
 * Generates a SHA-256 hash for the given file.
 *
 * @param {string} filePath - Path to the file to hash.
 * @return {string} The SHA-256 hexadecimal hash of the file.
 */
function hashFile(filePath) {
  const buffer = readFileSync(filePath);
  return createHash('sha256').update(buffer).digest('hex');
}


/**
 * Downloads a file via Playwright’s APIRequest and saves it to disk.
 * A cache-busting query parameter is appended to avoid cached responses.
 *
 * @async
 * @param {import('@playwright/test').Page} page       - The Playwright page instance.
 * @param {string}                          url        - The URL of the file to download.
 * @param {string}                          outputPath - Where to save the downloaded file.
 * @throws {Error} If the HTTP request returns a non-OK status code.
 */
async function downloadFile(page, url, outputPath) {
  const cacheBustedUrl = url.includes('?') ?
    `${url}&nocache=${Date.now()}` :
    `${url}?nocache=${Date.now()}`;

  const response = await page.context().request.get(cacheBustedUrl, {
    headers: {'Cache-Control': 'no-cache'},
  });

  if (!response.ok()) {
    throw new Error(`Failed to download file from ${cacheBustedUrl} — HTTP ${response.status()}`);
  }

  const buffer = await response.body();
  writeFileSync(outputPath, buffer);
}

test('Replace Media file (PDF) in WordPress', async ({page}, testInfo) => {
  await ensureStatelessModeEnabled(page, testInfo);

  const originalFile = resolve('tests/data/test_media_replacer_pdf_1.pdf');
  const newFile = resolve('tests/data/test_media_replacer_pdf_2.pdf');
  const tmpDir = resolve('tests/tmp');
  mkdirSync(tmpDir, {recursive: true});

  const beforePath = join(tmpDir, 'before.pdf');
  const afterPath = join(tmpDir, 'after.pdf');

  await page.goto('./wp-admin/upload.php');
  await switchMediaLibraryToListView(page);

  // --- Upload the first file ---
  await page.locator('#wpbody-content').getByRole('link', {name: 'Add Media File'}).click();
  await uploadMediaFile(page, originalFile);

  const uploadedFileName = await page.locator('.media-item-wrapper .media-list-title strong').innerText();
  expect(uploadedFileName).toContain('test_media_replacer_pdf_1');

  const editLink = page.locator('.media-item-wrapper a.edit-attachment');
  await editLink.click();

  // --- Download the original file for hashing ---
  const fileUrl = await page.locator('#attachment_url').inputValue();
  await downloadFile(page, fileUrl, beforePath);
  const oldHash = hashFile(beforePath);

  // --- Replace the media file ---
  await replaceMediaFile(page, newFile);
  await page.waitForTimeout(10000); // Allow for backend processing

  const successNotice = page.locator('.notice-success');
  await expect(successNotice).toContainText('These files were successfully replaced:', {timeout: 10000});

  // --- Download the new file for hashing ---
  const newFileUrl = await page.locator('#attachment_url').inputValue();
  await downloadFile(page, newFileUrl, afterPath);
  const newHash = hashFile(afterPath);

  expect(newHash).not.toBe(oldHash);

  if (existsSync(beforePath)) {unlinkSync(beforePath);}
  if (existsSync(afterPath)) {unlinkSync(afterPath);}
});
