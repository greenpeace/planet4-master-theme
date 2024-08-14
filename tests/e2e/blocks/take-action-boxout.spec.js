import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';

test.useAdminLoggedIn();

test.describe('Test Take Action Boxout block', () => {
  test.beforeEach(async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({admin, editor}, {title: 'Test Take action boxout'});

    // Add Take Action Boxout block.
    const actionRequest = page.waitForRequest(/.*\/wp-json\/wp\/v2\/p4_action.*/);//NOSONAR
    await editor.canvas.getByRole('button', {name: 'Add default block'}).click();
    await page.keyboard.type('/take-action-boxout');
    await page.getByRole('option', {name: 'Take Action Boxout'}).click();
    await actionRequest;

    await editor.canvas
      .getByRole('document', {name: 'Block: Take Action Boxout'})
      .waitFor();
  });

  test('Take Action Boxout with existing page', async ({page, editor}) => {
    // Select the first page option.

    await editor.canvas.getByRole('region', {name: 'Editor settings'})
      .getByRole('combobox', {name: 'Select Take Action Page'})
      .selectOption({index: 1});

    // Save boxout data to make sure it shows in the frontend.
    const boxoutTitle = await page.innerHTML('.boxout-heading');

    // Publish post.
    await publishPostAndVisit({page, editor});

    // Make sure block shows as expected in the frontend.
    expect((await page.innerHTML('.boxout-heading')).trim()).toBe(boxoutTitle);
  });

  test('Take Action Boxout with custom fields', async ({page, editor}) => {
    // Fill in boxout fields.
    await page.locator('.boxout-heading').click();
    await page.locator('.boxout-heading').fill('The boxout title');
    await page.locator('.boxout-excerpt').click();
    await page.locator('.boxout-excerpt').fill('The boxout excerpt');

    // Publish post.
    await publishPostAndVisit({page, editor});

    // Make sure block shows as expected in the frontend.
    const boxoutTitle = (await page.innerHTML('.boxout-heading')).trim();
    const boxoutExcerpt = await page.innerHTML('.boxout-excerpt');
    expect(boxoutTitle).toBe('The boxout title');
    expect(boxoutExcerpt).toBe('The boxout excerpt');
  });
});

