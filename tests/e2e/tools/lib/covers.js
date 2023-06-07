const {expect} = require('@playwright/test');

const TAG_NAMES = ['Climate', 'Oceans','renewables', 'Consumption'];
const PAGE_NAMES = ['Vestibulum leo libero', 'Consectetur adipiscing elit', 'Vestibulum placerat'];

async function addCoversBlock(page, style) {
  // Add Covers block.
  await page.locator('.block-editor-block-list__layout').click();
  await page.locator('p.is-selected.wp-block-paragraph').type('/planet-4-covers');
  await page.keyboard.press('Enter');

  // Select the style if needed.
  if (style) {
    const stylePicker = page.locator('.block-editor-block-styles__variants');
    //CSS selector needs single word,hence remove word after space(eg Take Action=>Take).
    await stylePicker.locator(`button[aria-label^=${style.split(' ')[0]}]`).click();
  }

  if (style === 'Take Action') {
    // Fill in the Posts.
    await page.locator('input[placeholder="Select pages"]').type(PAGE_NAMES[0]);
    await page.locator('li.components-form-token-field__suggestion').click();
    await page.locator('input.components-form-token-field__input').type(PAGE_NAMES[1]);
    await page.locator('li.components-form-token-field__suggestion').click();
    await page.locator('input.components-form-token-field__input').type(PAGE_NAMES[2]);
    await page.locator('li.components-form-token-field__suggestion').click();
  } else {
    // Fill in the tags.
    await page.locator('input[placeholder="Select Tags"]').type(TAG_NAMES[0]);
    await page.locator('li.components-form-token-field__suggestion').click();
    await page.locator('input.components-form-token-field__input').type(TAG_NAMES[1]);
    await page.locator('li.components-form-token-field__suggestion').click();
    await page.locator('input.components-form-token-field__input').type(TAG_NAMES[2]);
    await page.locator('li.components-form-token-field__suggestion').click();
    await page.locator('input.components-form-token-field__input').type(TAG_NAMES[3]);
    await page.locator('li.components-form-token-field__suggestion').click();
  }

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
  } else if (style === 'Take Action') {
    await expect(page.locator('.take-action-covers-block')).toBeVisible();
    const frontendCovers = await page.locator('.cover-card').all();
    for (const [, cover] of frontendCovers.entries()) {
      expect(cover.locator('a > img')).toBeVisible();
      let coverName = await cover.locator('a.cover-card-heading').innerText();
      expect(PAGE_NAMES.includes(coverName)).toBe(true);
      expect(cover.locator('.cover-card-tag')).toBeVisible();
      expect(cover.locator('.cover-card-excerpt')).toBeVisible();
      expect(cover.locator('a.cover-card-btn')).toHaveText('Get Involved');
    }
  }

  // Check Load more button
  const loadMoreBtn = await page.$('button.load-more-btn');
  if (loadMoreBtn) {
    await expect(page.locator('button.load-more-btn')).toHaveText('Read more');
  }
}

export {addCoversBlock, checkCoversBlock};
