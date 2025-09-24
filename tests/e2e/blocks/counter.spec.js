import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

const COUNTER_URL = 'https://counter.greenpeace.org/signups?id=testcounter';
const COUNTER_GOAL = 10000;
const COUNTER_TITLE = 'Counter Block';
const COUNTER_DESCRIPTION = 'Counter description';

test.useAdminLoggedIn();

test('Test Counter block', async ({page, admin, editor, request}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Counter', postType: 'page'});

  // Add Counter block.
  await searchAndInsertBlock({page}, 'Counter');

  // Pick the "Progress Bar" style.
  const stylePicker = page.locator('.block-editor-block-styles__variants');
  await stylePicker.locator('button[aria-label="Progress Bar"]').click();

  // Change the block settings.
  await page.getByLabel('API URL for Goal Reached').fill(COUNTER_URL);
  await page.getByLabel('Goal', {exact: true}).fill(`${COUNTER_GOAL}`);
  await page.getByPlaceholder('e.g. "signatures collected of').fill('Signatures collected: %completed% from %target%. (%remaining% remaining)');

  // Change block title and description.
  await page.getByRole('textbox', {name: 'Enter title'}).fill(COUNTER_TITLE);
  await page.getByRole('textbox', {name: 'Enter description'}).fill(COUNTER_DESCRIPTION);

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Test that the block is displayed as expected in the frontend:
  // Title and description.
  await expect(page.locator('.page-section-header')).toHaveText(COUNTER_TITLE);
  await expect(page.locator('.page-section-description')).toHaveText(COUNTER_DESCRIPTION);

  // Counter text with correct values.
  const apiResponse = await request.get(COUNTER_URL);
  const {unique_count: completed} = await apiResponse.json();
  const counterTextNumbers = page.locator('.counter-target');
  await expect(counterTextNumbers.nth(0)).toHaveText(`${completed}`);
  await expect(counterTextNumbers.nth(1)).toHaveText(`${COUNTER_GOAL}`);
  await expect(counterTextNumbers.nth(2)).toHaveText(`${COUNTER_GOAL - completed}`);

  // Counter progress bar with correct percentage.
  const completedPercentage = Math.round((completed / COUNTER_GOAL) * 100);
  await expect(page.locator('.progress-bar')).toHaveAttribute('style', `width: calc(${completedPercentage}% + 20px);`);
});

