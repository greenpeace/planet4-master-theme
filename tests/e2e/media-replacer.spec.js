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
 * Replaces an already-uploaded media file using the Media Replacer plugin UI.
 *
 * @async
 * @param {import('@playwright/test').Page}                          page    - The Playwright page instance.
 * @param {string | Buffer | import('@playwright/test').FilePayload} newFile - Replacement file.
 */
async function replaceMediaFile(page, newFile) {
  const replaceButton = page.locator('.media-replacer-button');
  await expect(replaceButton).toBeVisible();

  // Listen for the file chooser BEFORE clicking the button
  // If we click first, the dialog opens and closes before we can handle it
  const [fileChooser] = await Promise.all([
    page.waitForEvent('filechooser'),
    replaceButton.click(),
  ]);

  // Set the file through the file chooser dialog
  await fileChooser.setFiles(newFile);

  await page.waitForLoadState('domcontentloaded');
  await page.waitForLoadState('networkidle');
}

/**
 * Ensures that the "Stateless" mode is enabled in the WP Stateless plugin settings.
 * If not enabled, the test is skipped.
 *
 * @async
 * @param {import('@playwright/test').Page}     page     - Playwright page instance.
 * @param {import('@playwright/test').Page}     admin    - Playwright page instance for admin.
 * @param {import('@playwright/test').TestInfo} testInfo - Playwright TestInfo object used for skipping.
 */
async function ensureStatelessModeEnabled(page, admin, testInfo) {
  // --- Check Stateless mode via Admin settings page ---
  await admin.visitAdminPage('upload.php', 'page=stateless-settings');
  const isStatelessChecked = await page.locator('#sm_mode_stateless').isChecked();
  if (!isStatelessChecked) {
    testInfo.skip(true, 'Skipping: Stateless mode is NOT active.');
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

test('Replace Media file (PDF) in WordPress', async ({page, admin, requestUtils}, testInfo) => {

  test.slow();
  await ensureStatelessModeEnabled(page, admin, testInfo);

  const originalFile = resolve('tests/data/test_media_replacer_pdf_1.pdf');
  const newFile = resolve('tests/data/test_media_replacer_pdf_2.pdf');
  const tmpDir = resolve('tests/tmp');
  mkdirSync(tmpDir, {recursive: true});

  const beforePath = join(tmpDir, 'before.pdf');
  const afterPath = join(tmpDir, 'after.pdf');

  await page.goto('./wp-admin/upload.php');
  await switchMediaLibraryToListView(page);

  // --- Upload the first file ---
  const uploadedMedia = await requestUtils.uploadMedia(originalFile);
  expect(uploadedMedia.slug).toContain('test_media_replacer_pdf_1');

  // --- Navigate to the media edit page ---
  await admin.visitAdminPage('post.php', `post=${uploadedMedia.id}&action=edit`);

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

  // --- Cleanup via REST API ---
  await requestUtils.deleteMedia(uploadedMedia.id);

  if (existsSync(beforePath)) {unlinkSync(beforePath);}
  if (existsSync(afterPath)) {unlinkSync(afterPath);}
});
