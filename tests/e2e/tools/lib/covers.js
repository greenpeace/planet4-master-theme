const {expect} = require('@playwright/test');

const TAG_NAMES = ['Climate', 'Oceans','renewables', 'Consumption'];

async function addCoversBlock(page, style) {
  // Add Covers block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').type('/planet-4-covers');
  await page.keyboard.press('Enter');

  // Select the style if needed.
  if (style) {
    const stylePicker = page.locator('.block-editor-block-styles__variants');
    await stylePicker.locator(`button[aria-label^=${style}]`).click();
  }

  // Fill in the tags.
  await page.locator('input[placeholder="Select Tags"]').type(TAG_NAMES[0]);
  await page.locator('li.components-form-token-field__suggestion').click();
  await page.locator('input.components-form-token-field__input').type(TAG_NAMES[1]);
  await page.locator('li.components-form-token-field__suggestion').click();
  await page.locator('input.components-form-token-field__input').type(TAG_NAMES[2]);
  await page.locator('li.components-form-token-field__suggestion').click();
  await page.locator('input.components-form-token-field__input').type(TAG_NAMES[3]);
  await page.locator('li.components-form-token-field__suggestion').click();

  await page.locator('input[placeholder="Override button text"]').fill('Read more');
}

async function checkCoversBlock(page, style) {
  if (style === 'Campaign') {
    await expect(page.locator('.campaign-covers-block')).toBeVisible();
    const frontendCovers = await page.locator('.campaign-card-column').all();
    for (const [, cover] of frontendCovers.entries()) {
      expect(cover.locator('a > div.thumbnail-large >img')).toBeVisible();
      expect(cover.locator('.yellow-cta')).toBeVisible();
      let tagName = await cover.locator('.yellow-cta').innerText();
      tagName = tagName.replace(/#/g, '');
      expect(TAG_NAMES.includes(tagName)).toBe(true);
    }
  }

  // Check Load more button
  const loadMoreBtn = await page.$('button.load-more-btn');
  if (loadMoreBtn) {
    await expect(page.locator('button.load-more-btn')).toHaveText('Read more');
  }
}

export {addCoversBlock, checkCoversBlock};
