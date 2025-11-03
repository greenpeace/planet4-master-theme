import {test, expect} from './tools/lib/test-utils.js';
import {join, resolve} from 'path';
import {readFileSync, writeFileSync, mkdirSync, unlinkSync} from 'fs';
import {createHash} from 'crypto';

test.useAdminLoggedIn();

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

async function uploadMediaFile(page, originalFile) {
  await page.waitForSelector('#plupload-browse-button', {state: 'visible'});
  const fileInput = page.locator('input[type="file"][id^="html5_"]');
  await fileInput.waitFor({state: 'attached'});
  await fileInput.setInputFiles(originalFile);
}

async function replaceMediaFile(page, newFile) {
  const replaceButton = page.locator('.media-replacer-button');
  const fileInput = page.locator('input.replace-media-file');

  await replaceButton.waitFor({state: 'visible'});
  await replaceButton.click();

  await fileInput.setInputFiles(newFile);

  await page.waitForLoadState('domcontentloaded');
}

function hashFile(filePath) {
  const buffer = readFileSync(filePath);
  return createHash('sha256').update(buffer).digest('hex');
}

async function downloadFile(page, url, outputPath) {
  const cacheBustedUrl = url.includes('?') ?
    `${url}&nocache=${Date.now()}` :
    `${url}?nocache=${Date.now()}`;

  const response = await page.request.get(cacheBustedUrl, {
    headers: {'Cache-Control': 'no-cache'},
  });

  if (!response.ok()) {
    throw new Error(`Failed to download file from ${cacheBustedUrl} — HTTP ${response.status()}`);
  }

  const buffer = await response.body();
  writeFileSync(outputPath, buffer);
}

test('Replace Media file in WordPress', async ({page}) => {
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

  unlinkSync(beforePath);
  unlinkSync(afterPath);
});
