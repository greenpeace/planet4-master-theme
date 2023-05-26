const {expect} = require('@playwright/test');

const TEST_LINKS = ['/act', '/explore', '/'];

async function addColumnsBlock(page, style) {
  // Add Columns block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').fill('/planet-4-columns');
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
    await column.locator('div[aria-label="Enter column button text"]').fill(`Button ${index + 1}`);
  }
};

async function checkColumnsBlock(page, style) {
  const frontendColums = await page.locator('.column-wrap').all();
  for (const [index, column] of frontendColums.entries()) {
    if (style === 'Tasks') {
      expect(column.locator('.step-number')).toBeVisible();
    }
    expect(await column.innerHTML(style === 'Tasks' ? 'h5' : 'h3')).toBe(`Column ${index + 1}`);
    expect(await column.innerHTML('p')).fill(`Description ${index + 1}`);
    const button = column.locator('a.btn-secondary');
    expect(button).toHaveText(`Button ${index + 1}`);
    expect(button.getAttribute('href')).toBe(TEST_LINKS[index]);
  }
}

export {addColumnsBlock, checkColumnsBlock};
