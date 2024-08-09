import {expect} from './test-utils.js';

const TAG_NAMES = ['Climate', 'Oceans', 'renewables', 'Consumption'];
const PAGE_NAMES = ['Vestibulum leo libero', 'Consectetur adipiscing elit', 'Vestibulum placerat'];
const POST_TYPES = ['Publication', 'Press Release'];

async function addCoversBlock(page, editor, style = '') {
  // Add Covers block.
  await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
  await page.keyboard.type('/planet-4-covers');
  await page.getByRole('option', {name: 'Covers'}).click();

  // Select the style if needed.
  if (style) {
    const stylePicker = await page.locator('.block-editor-block-styles__variants');
    //CSS selector needs single word,hence remove word after space(eg Take Action=>Take).
    const label = style === 'Content' ? /Content|Default/ : style.split(' ')[0];
    await stylePicker.getByRole('button', {name: label}).click();
  }

  const settings = await editor.canvas.getByRole('region', {name: 'Editor settings'});
  const suggestions = settings
    .getByRole('listbox')
    .and(settings.locator('[id*="suggestions"]'));

  if (style === 'Take Action') {
    // Fill in the Posts.
    const postsInput = await settings.getByLabel('Select pages');
    await postsInput.scrollIntoViewIfNeeded();

    await postsInput.type(PAGE_NAMES[0]);
    await suggestions.getByRole('option').first().click();

    await postsInput.type(PAGE_NAMES[1]);
    await suggestions.getByRole('option').first().click();

    await postsInput.type(PAGE_NAMES[2]);
    await suggestions.getByRole('option').first().click();
  } else {
    // Fill in the tags.
    const tagsInput = await settings.getByLabel('Select Tags');
    await tagsInput.scrollIntoViewIfNeeded();

    await tagsInput.type(TAG_NAMES[0]);
    await suggestions.getByRole('option').first().click();

    await tagsInput.type(TAG_NAMES[1]);
    await suggestions.getByRole('option').first().click();

    await tagsInput.type(TAG_NAMES[2]);
    await suggestions.getByRole('option').first().click();

    await tagsInput.type(TAG_NAMES[3]);
    await suggestions.getByRole('option').first().click();
  }

  // Default style, i.e 'Content cover'.
  if (style === 'Content') {
    // Fill in the Post types.
    const postTypesInput = await page.getByLabel('Select Post Types');
    await postTypesInput.scrollIntoViewIfNeeded();

    await postTypesInput.type(POST_TYPES[0]);
    await suggestions.getByRole('option').first().click();

    await postTypesInput.type(POST_TYPES[1]);
    await suggestions.getByRole('option').first().click();
  }
  await page.getByLabel('Button Text').fill('Read more');
}

async function checkCoversBlock(page, style) {
  // Default style, i.e 'Content cover'.
  if (style === 'Content') {
    await expect(page.locator('.content-covers-block')).toBeVisible();
    const frontendCovers = await page.locator('.post-column.cover').all();
    for (const [, cover] of frontendCovers.entries()) {
      await expect(cover.locator('.content-covers-block-image > a > img')).toBeVisible();
      await expect(cover.locator('.content-covers-block-information > h5 > a')).toBeVisible();
      await expect(cover.locator('.content-covers-block-information > .publication-date')).toBeVisible();
      await expect(cover.locator('.content-covers-block-information > .post-excerpt')).toBeVisible();
    }
  } else if (style === 'Campaign') {
    await expect(page.locator('.campaign-covers-block')).toBeVisible();
    const frontendCovers = await page.locator('.campaign-card-column').all();
    for (const [, cover] of frontendCovers.entries()) {
      await expect(cover.locator('a > div.thumbnail-large >img')).toBeVisible();
      await expect(cover.locator('.yellow-cta')).toBeVisible();
      let tagName = await cover.locator('.yellow-cta').innerText();
      tagName = tagName.replace(/#/g, '');
      expect(TAG_NAMES.includes(tagName)).toBeTruthy();
    }
  } else if (style === 'Take Action') {
    await expect(page.locator('.take-action-covers-block')).toBeVisible();
    const frontendCovers = await page.locator('.cover-card').all();
    for (const [, cover] of frontendCovers.entries()) {
      await expect(cover.locator('a > img')).toBeVisible();
      const coverName = await cover.locator('a.cover-card-heading').innerText();
      expect(PAGE_NAMES.includes(coverName)).toBe(true);
      await expect(cover.locator('.cover-card-tag')).toBeVisible();
      await expect(cover.locator('.cover-card-excerpt')).toBeVisible();
      await expect(cover.locator('a.cover-card-btn')).toHaveText('Get Involved');
    }
  }

  // Check Load more button
  const loadMoreBtn = await page.$('button.load-more-btn');
  if (loadMoreBtn) {
    await expect(page.locator('button.load-more-btn')).toHaveText('Read more');
  }
}

export {addCoversBlock, checkCoversBlock};
