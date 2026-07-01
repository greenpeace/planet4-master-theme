import {test, expect} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock} from '../tools/lib/editor.js';

test.useAdminLoggedIn();

test.describe('Test Take Action Boxout block', () => {
  test.beforeEach(async ({page, admin, editor}) => {
    await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Take action boxout'});

    // Add Take Action Boxout block.
    await searchAndInsertBlock({page}, 'Take Action Boxout');

    await Promise.race([
      page.waitForResponse(r =>
        r.url().includes('/planet4/v1/action-pages')
      ),
      await expect(editor.canvas.locator('.boxout-heading[contenteditable="true"]')).toBeVisible(),
    ]);

  });

  test('Take Action Boxout with existing page', async ({page, editor}) => {
    test.slow();
    // Select the first page option.
    await page.getByRole('combobox', {name: 'Select Take Action Page'}).selectOption({index: 1});

    // Save boxout data to make sure it shows in the frontend.
    const boxoutTitle = await editor.canvas.locator('.boxout-heading').innerText();

    // Publish post.
    await publishPostAndVisit({page, editor});

    // Make sure block shows as expected in the frontend.
    expect(page.locator('.boxout-heading')).toHaveText(boxoutTitle);
  });

  test('Take Action Boxout with custom fields', async ({page, editor}) => {
    // Fill in boxout fields.
    await editor.canvas.locator('.boxout-heading').fill('The boxout title');
    await editor.canvas.locator('.boxout-excerpt').fill('The boxout excerpt');

    // Publish post.
    await publishPostAndVisit({page, editor});

    // Make sure block shows as expected in the frontend.
    await expect(page.locator('.boxout-heading')).toHaveText('The boxout title');
    await expect(page.locator('.boxout-excerpt')).toHaveText('The boxout excerpt');
  });
});

