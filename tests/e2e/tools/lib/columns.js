const {expect} = require('@playwright/test');

const TEST_LINKS = ['/act', '/explore', '/'];

async function addColumnsBlock(page, style) {
  // Add Columns block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').type('/planet-4-columns');
  await page.keyboard.press('Enter');

  // Select the style if needed.
  if (style) {
    const stylePicker = page.locator('.block-editor-block-styles__variants');
    await stylePicker.locator(`button[aria-label^=${style}]`).click();
  }

  // Fill in the Columns links.
  await page.locator('input[placeholder="Enter link for column 1"]').fill(TEST_LINKS[0]);
  await page.locator('input[placeholder="Enter link for column 2"]').fill(TEST_LINKS[1]);
  await page.locator('input[placeholder="Enter link for column 3"]').fill(TEST_LINKS[2]);

  // Fill in the other fields.
  const backendColumns = await page.locator('.column-wrap').all();
  for (const [index, column] of backendColumns.entries()) {
    await column.locator(style === 'Tasks' ? 'h5' : 'h3').fill(`Column ${index + 1}`);
    await column.locator('p').fill(`Description ${index + 1}`);
    await column.locator(
      `div[aria-label="Enter column ${['Images','Icons'].includes(style) ? 'link' : 'button'} text"]`
    ).fill(`${['Images','Icons'].includes(style) ? 'Link' : 'Button'} ${index + 1}`);

    if (style === 'Icons') {
      await column.locator('.columns-image-placeholder').hover({noWaitAfter: true});
      await column.locator('.dashicons-plus-alt2').click();
      // Select image from media library modal.
      const mediaModal = page.locator('.media-modal').nth(index);
      await mediaModal.locator('button#menu-item-browse').click();
      await mediaModal.locator('.attachments-wrapper ul.attachments li').nth(-1-index).click();
      await mediaModal.locator('button.media-button-select').click();
    }
  }
};

async function checkColumnsBlock(page, style) {
  await expect(page.locator('.columns-block')).toBeVisible();
  const frontendColums = await page.locator('.column-wrap').all();
  for (const [index, column] of frontendColums.entries()) {
    if (style === 'Tasks') {
      await expect(column.locator('.step-number')).toBeVisible();
    }
    await expect(column.locator(style === 'Tasks' ? 'h5' : 'h3')).toHaveText(`Column ${index + 1}`);
    await expect(column.locator('p')).toHaveText(`Description ${index + 1}`);
    if (style === 'Images' || style === 'Icons') {
      await expect(column.locator('.attachment-container > a > img')).toBeVisible();
      const link = column.locator('a.standalone-link');
      await expect(link).toHaveText(`Link ${index + 1}`);
      await expect(link).toHaveAttribute('href', TEST_LINKS[index]);
    } else {
      const button = column.locator('a.btn-secondary');
      await expect(button).toHaveText(`Button ${index + 1}`);
      await expect(button).toHaveAttribute('href', TEST_LINKS[index]);
    }
  }
}

export {addColumnsBlock, checkColumnsBlock};
