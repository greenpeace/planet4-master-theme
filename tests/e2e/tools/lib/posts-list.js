import {addListBlock, addListBlockWithManualOverride, checkListBlock} from './query-loop-utils.js';
import {expect} from './test-utils.js';

const BLOCK_NAME = 'Posts List';
const TEST_TITLE = 'Related Stories';
const TEST_CATEGORY = 'Energy';
const MANUAL_OVERRIDE_TITLE = 'Posts';

/**
 * Adds the Posts List block to the page.
 *
 * @param {import('@playwright/test').Page} page   - The Playwright page instance.
 * @param {string}                          layout - The layout of the block.
 */
export async function addPostsListBlock(page, layout) {
  await addListBlock(page, BLOCK_NAME, 4, {layout, category: TEST_CATEGORY, title: TEST_TITLE});
}

/**
 * Adds the Posts List block to the page with the Manual Override posts selected.
 *
 * @param {import('@playwright/test').Page} page       - The Playwright page instance.
 * @param {string[]}                        postTitles - The titles of the posts to include in the block to override the default ones.
 */
export async function addPostsListBlockWithManualOverride(page, postTitles) {
  await addListBlockWithManualOverride(page, BLOCK_NAME, postTitles, MANUAL_OVERRIDE_TITLE);
}

/**
 * Validate the Posts List block tests.
 *
 * @param {import('@playwright/test').Page} page   - The Playwright page instance.
 * @param {string}                          layout - The layout of the block.
 */
export async function checkPostsListBlock(page, layout) {
  await checkListBlock(page, {
    layout,
    title: TEST_TITLE,
    count: 4,
    category: TEST_CATEGORY,
    categoryLocator: '.wp-block-post-terms:not(.taxonomy-post_tag)',
  });
}

/**
 * Validate the Posts List block tests with manual override.
 *
 * @param {import('@playwright/test').Page} page       - The Playwright page instance.
 * @param {string[]}                        postTitles - The titles of the posts to include in the block to override the default ones.
 */
export async function checkPostsListBlockWithManualOverride(page, postTitles) {
  await checkListBlock(page, {
    layout: 'list',
    title: MANUAL_OVERRIDE_TITLE,
    count: postTitles.length,
    postTitles,
  });
}

/**
 * Validate the Posts List block tests with carousel layout.
 *
 * @param {import('@playwright/test').Page} page - The Playwright page instance.
 */
export async function checkPostsListBlockCarouselLayout(page) {
  await checkPostsListBlock(page, 'carousel');

  const block = page.locator('.p4-query-loop');
  await expect(block.locator('.carousel.slide')).toBeVisible();
  await expect(block.locator('.carousel-item.active')).toBeVisible();
}
