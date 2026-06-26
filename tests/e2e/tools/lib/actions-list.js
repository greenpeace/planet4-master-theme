import {addListBlock, addListBlockWithManualOverride, checkListBlock} from './query-loop-utils.js';

const BLOCK_NAME = 'Actions List';
const TEST_TITLE = 'Campaigns';
const TEST_CATEGORY = 'Energy';
const MANUAL_OVERRIDE_TITLE = 'Actions';

export async function addActionsListBlock(page, layout) {
  await addListBlock(page, BLOCK_NAME, 2, {layout, category: TEST_CATEGORY, title: TEST_TITLE});
}

export async function addActionsListBlockWithManualOverride(page, actionTitles) {
  await addListBlockWithManualOverride(page, BLOCK_NAME, actionTitles, MANUAL_OVERRIDE_TITLE);
}

export async function checkActionsListBlock(page) {
  await checkListBlock(page, {
    layout: 'grid',
    title: TEST_TITLE,
    count: 2,
    category: TEST_CATEGORY,
  });
}

export async function checkActionsListBlockWithManualOverride(page, actionTitles) {
  await checkListBlock(page, {
    layout: 'grid',
    title: MANUAL_OVERRIDE_TITLE,
    count: actionTitles.length,
    postTitles: actionTitles,
  });
}
