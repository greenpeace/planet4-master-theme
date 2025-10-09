import {expect, test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {searchAndInsertBlock, searchAndInsertPattern, closeBlockInserter} from '../tools/lib/editor.js';

const PARAGRAPH_CONTENT = `Nulla in odio et augue aliquet dictum ac sit amet dolor.
  Aenean sed orci ac lectus dignissim commodo. Mauris fermentum orci sed faucibus feugiat.
  Curabitur sed eros et ex sodales lobortis sodales et est. Maecenas sit amet iaculis libero.
  Duis laoreet nisi lorem, eget convallis magna tristique nec. Nunc eu est risus.
  Mauris lorem mi, imperdiet in velit vitae, ullamcorper ullamcorper nulla. Aenean fringilla sodales turpis.
  Duis convallis dui et scelerisque commodo.`;

const HEADINGS = [
  'Lorem ipsum dolor sit amet',
  'Fusce pretium elit fermentum, semper massa nec, convallis ipsum',
  'Suspendisse',
  'Nulla feugiat nibh et arcu commodo, sed aliquam tellus rhoncus',
  'Vivamus suscipit mattis elit vel hendrerit',
  'Praesent ullamcorper libero eget libero scelerisque porttitor',
  'Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos',
  'Quisque accumsan',
  'Suspendisse pellentesque tellus lacus, varius gravida massa posuere ut',
  'Donec tristique nibh vel vestibulum condimentum',
];

test.useAdminLoggedIn();

test('Test Secondary Navigation block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Counter', postType: 'page'});

  // Add Page Header block.
  await searchAndInsertPattern({page}, 'p4/page-header-img-right');
  await closeBlockInserter({page});

  // Add Secondary Navigation block.
  await searchAndInsertBlock({page}, 'Secondary Navigation Menu');
  await closeBlockInserter({page});

  // Make sure it displays the empty message at first.
  await expect(page.locator('.EmptyMessage')).toBeVisible();

  // Add content (headings and paragraphs).
  for (let index = 0; index < HEADINGS.length; index++) {
    await searchAndInsertBlock({page}, 'Heading');
    await closeBlockInserter({page});
    await page.getByRole('region', {name: 'Editor content'}).locator('h2').nth(index).fill(HEADINGS[index]);
    await searchAndInsertBlock({page}, 'Paragraph');
    await closeBlockInserter({page});
    // For the paragraphs we need to ignore the first paragraph which is in the Page Header block.
    await page.getByRole('region', {name: 'Editor content'}).locator('p').nth(index + 1).fill(PARAGRAPH_CONTENT);
  };

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Test that the block is displayed as expected in the frontend:
  // The Secondary Navigation block is present.
  const secondaryNavigationBlock = page.locator('[data-render="planet4-blocks/secondary-navigation"]');
  await expect(secondaryNavigationBlock).toBeVisible();

  // The links in the block match all h2 elements present in the page content.
  for (let index = 0; index < HEADINGS.length; index++) {
    await expect(secondaryNavigationBlock.locator('.secondary-navigation-link').nth(index)).toHaveText(HEADINGS[index]);
  };

  // On scroll down, the block should become sticky.
  await page.getByRole('heading', {name: HEADINGS.at(-1)}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // Make sure that the block remains sticky on scroll up.
  await page.getByRole('heading', {name: HEADINGS.at(-3)}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // The Secondary Navigation block should have navigation arrows present for left or right scrolling.
  // await expect(secondaryNavigationBlock.locator('.nav-arrow.right')).toHaveCount(1);
  // await expect(secondaryNavigationBlock.locator('.nav-arrow.left')).toHaveCount(1);

  // On mobile, the block should be a dropdown menu.
  await page.setViewportSize({width: 320});
  await expect(secondaryNavigationBlock.locator('.dropdown-btn')).toBeVisible();
});

