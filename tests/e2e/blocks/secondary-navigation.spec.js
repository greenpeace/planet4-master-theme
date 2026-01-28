import {expect, test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  searchAndInsertBlock,
  searchAndInsertPattern,
  closeBlockInserter,
  addHeadingOrParagraph,
} from '../tools/lib/editor.js';

const NAV_LINK_CLASS = '.secondary-navigation-link';

const PARAGRAPH_CONTENT = `Nulla in odio et augue aliquet dictum ac sit amet dolor.
  Aenean sed orci ac lectus dignissim commodo. Mauris fermentum orci sed faucibus feugiat.
  Curabitur sed eros et ex sodales lobortis sodales et est. Maecenas sit amet iaculis libero.
  Duis laoreet nisi lorem, eget convallis magna tristique nec. Nunc eu est risus.`;

const HEADINGS = [
  'Lorem ipsum dolor sit amet',
  'Suspendisse',
  'Nulla feugiat nibh',
  'Vivamus suscipit mattis',
  'Praesent ullamcorper libero',
  'Quisque accumsan',
  'Donec tristique',
];

async function waitForBlock({page, blockType}) {
  await page.waitForFunction(
    type => {
      return !!document.querySelector(`[data-type="${type}"]`);
    },
    blockType,
    {timeout: 40000}
  );
}

test.useAdminLoggedIn();

test('Test Secondary Navigation block', async ({page, admin, editor}) => {
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Secondary Navigation', postType: 'page'});

  // Add Page Header block.
  await searchAndInsertPattern({page}, 'p4/page-header-img-right');
  await closeBlockInserter({page});

  // Add Secondary Navigation block.
  await searchAndInsertBlock({page}, 'Secondary Navigation Menu');
  await closeBlockInserter({page});

  await waitForBlock({
    page,
    blockType: 'planet4-blocks/secondary-navigation',
  });

  const blockRoot = page.locator('[data-type="planet4-blocks/secondary-navigation"]');

  // Make sure it displays the empty message at first.
  const emptyMessage = blockRoot.locator('.EmptyMessage');
  await expect(emptyMessage).toBeVisible();

  // Add content (headings and paragraphs).
  for (const heading of HEADINGS) {
    await addHeadingOrParagraph({page}, 'Heading', 'h2', heading);
    await page.waitForTimeout(20);
    await addHeadingOrParagraph({page}, 'Paragraph', 'p', PARAGRAPH_CONTENT);
  }

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Test that the block is displayed as expected in the frontend:
  // The Secondary Navigation block is present.
  const secondaryNavigationBlock = page.locator('[data-render="planet4-blocks/secondary-navigation"]');
  await expect(secondaryNavigationBlock).toBeVisible();

  // The links in the block match all h2 elements present in the page content.
  for (let index = 0; index < HEADINGS.length; index++) {
    await expect(secondaryNavigationBlock.locator(NAV_LINK_CLASS).nth(index)).toHaveText(HEADINGS[index]);
  };

  // The Secondary Navigation block should have navigation arrows present for left or right scrolling.
  const rightArrow = secondaryNavigationBlock.locator('.nav-arrow.right');
  const leftArrow = secondaryNavigationBlock.locator('.nav-arrow.left');
  await expect(rightArrow).toBeVisible();
  // Click on right arrow a few times to make sure the left arrow shows.
  await rightArrow.click();
  await rightArrow.click();
  await rightArrow.click();
  await expect(leftArrow).toBeVisible();

  // Make sure that the page navigates to the corrent anchor on click.
  const testLink = page.locator(NAV_LINK_CLASS, {hasText: HEADINGS[2]});
  const anchor = await testLink.getAttribute('href');
  await testLink.click();
  const targetId = anchor.replace('#', '');
  await expect(page.locator(`[id="${targetId}"]`)).toBeInViewport();

  // On scroll down, the block should become sticky.
  await page.getByRole('heading', {name: HEADINGS.at(-1)}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // Make sure that the block remains sticky on scroll up.
  await page.getByRole('heading', {name: HEADINGS.at(-3)}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // On mobile, the block should be a dropdown menu.
  await page.setViewportSize({width: 320, height: 500});
  await expect(secondaryNavigationBlock.locator('.dropdown-btn')).toBeVisible();
});

