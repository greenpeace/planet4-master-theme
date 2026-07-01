import {addListBlock, addListBlockWithManualOverride, checkListBlock} from './query-loop-utils.js';

const BLOCK_NAME = 'Actions List';
const TEST_TITLE = 'Campaigns';
const TEST_CATEGORY = 'Energy';
const MANUAL_OVERRIDE_TITLE = 'Actions';

/**
 * Adds the Actions List block to the page.
 *
 * @param {{Page, Editor}} options - Page and Editor object.
 * @param {string}         layout  - The layout of the block.
 */
export async function addActionsListBlock({page, editor}, layout) {
  await addListBlock({page, editor}, BLOCK_NAME, 2, {layout, category: TEST_CATEGORY, title: TEST_TITLE});
}

/**
 * Adds the Actions List block to the page with the Manual Override posts selected.
 *
 * @param {{Page, Editor}} options      - Page and Editor object
 * @param {string[]}       actionTitles - The titles of the actions to include in the block to override the default ones.
 */
export async function addActionsListBlockWithManualOverride({page, editor}, actionTitles) {
  await addListBlockWithManualOverride({page, editor}, BLOCK_NAME, actionTitles, MANUAL_OVERRIDE_TITLE);
}


/**
 * Validate the Actions List block tests.
 *
 * @param {import('@playwright/test').Page} page - The Playwright page instance.
 */
export async function checkActionsListBlock(page) {
  await checkListBlock(page, {
    layout: 'grid',
    title: TEST_TITLE,
    count: 2,
    category: TEST_CATEGORY,
  });
}

/**
 * Validate the Actions List block tests with manual override.
 *
 * @param {import('@playwright/test').Page} page         - The Playwright page instance.
 * @param {string[]}                        actionTitles - The titles of the actions to include in the block to override the default ones.
 */
export async function checkActionsListBlockWithManualOverride(page, actionTitles) {
  await checkListBlock(page, {
    layout: 'grid',
    title: MANUAL_OVERRIDE_TITLE,
    count: actionTitles.length,
    postTitles: actionTitles,
  });
}
