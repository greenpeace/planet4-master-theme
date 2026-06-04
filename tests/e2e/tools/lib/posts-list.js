import {addListBlock, addListBlockWithManualOverride, checkListBlock} from './query-loop-utils.js';
import {expect} from './test-utils.js';

const BLOCK_NAME = 'Posts List';
const TEST_TITLE = 'Related Stories';
const TEST_CATEGORY = 'Energy';
const MANUAL_OVERRIDE_TITLE = 'Posts';

export async function addPostsListBlock(page, layout) {
  await addListBlock(page, BLOCK_NAME, 4, {layout, category: TEST_CATEGORY, title: TEST_TITLE});
}

export async function addPostsListBlockWithManualOverride(page, postTitles) {
  await addListBlockWithManualOverride(page, BLOCK_NAME, postTitles, MANUAL_OVERRIDE_TITLE);
}

export async function checkPostsListBlock(page, layout) {
  await checkListBlock(page, {
    layout,
    title: TEST_TITLE,
    count: 4,
    category: TEST_CATEGORY,
    categoryLocator: '.wp-block-post-terms:not(.taxonomy-post_tag)',
  });
}

export async function checkPostsListBlockWithManualOverride(page, postTitles) {
  await checkListBlock(page, {
    layout: 'list',
    title: MANUAL_OVERRIDE_TITLE,
    count: postTitles.length,
    postTitles,
  });
}

export async function checkPostsListBlockCarouselLayout(page) {
  await checkPostsListBlock(page, 'carousel');

  const block = page.locator('.p4-query-loop');
  await expect(block.locator('.carousel.slide')).toBeVisible();
  await expect(block.locator('.carousel-item.active')).toBeVisible();
}
