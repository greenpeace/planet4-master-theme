import {expect, test} from '../tools/lib/test-utils.js';
import {publishPostAndVisit, createPostWithFeaturedImage} from '../tools/lib/post.js';
import {
  searchAndInsertBlock,
  searchAndInsertPattern,
  addContent,
} from '../tools/lib/editor.js';

const NAV_LINK_CLASS = '.secondary-navigation-link';

const PARAGRAPH_CONTENT = `Nulla in odio et augue aliquet dictum ac sit amet dolor.
  Aenean sed orci ac lectus dignissim commodo. Mauris fermentum orci sed faucibus feugiat.
  Curabitur sed eros et ex sodales lobortis sodales et est. Maecenas sit amet iaculis libero.
  Duis laoreet nisi lorem, eget convallis magna tristique nec. Nunc eu est risus.`;

const content = [
  {heading: 'Lorem ipsum dolor sit amet', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Suspendisse', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Nulla feugiat nibh', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Vivamus suscipit mattis', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Praesent ullamcorper libero', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Quisque accumsan', paragraph: PARAGRAPH_CONTENT},
  {heading: 'Donec tristique', paragraph: PARAGRAPH_CONTENT},
];

const SECONDARY_NAV = 'div[data-render="planet4-blocks/secondary-navigation"]';
const MAIN_NAV_ITEM_WITH_SUBMENU = '#nav-menu > ul > li.menu-item-has-children';

const filler = Array.from({length: 40}, (_, i) =>
  `<!-- wp:paragraph --><p>Paragraph ${i}. Lorem ipsum dolor sit amet.</p><!-- /wp:paragraph -->`
).join('');

test.useAdminLoggedIn();

test('Test Secondary Navigation block', async ({page, admin, editor}) => {
  test.slow();
  await createPostWithFeaturedImage({page, admin, editor}, {title: 'Test Secondary Navigation', postType: 'page'});

  // Add Page Header block.
  await searchAndInsertPattern({page}, 'p4/page-header-img-right');

  // Add Secondary Navigation block.
  await searchAndInsertBlock({editor, page}, 'planet4-blocks/secondary-navigation');

  // Make sure it displays the empty message at first.
  await expect(editor.canvas.locator('.EmptyMessage')).toBeVisible();

  // // Add content (headings and paragraphs).
  await addContent({editor, page}, content);

  // Publish page.
  await publishPostAndVisit({page, editor});

  // Test that the block is displayed as expected in the frontend:
  // The Secondary Navigation block is present.
  const secondaryNavigationBlock = page.locator('[data-render="planet4-blocks/secondary-navigation"]');
  await expect(secondaryNavigationBlock).toBeVisible();

  // The links in the block match all h2 elements present in the page content.
  for (let index = 0; index < content.length; index++) {
    await expect(secondaryNavigationBlock.locator(NAV_LINK_CLASS).nth(index)).toHaveText(content[index].heading);
  }

  // The Secondary Navigation block should have navigation arrows present for left or right scrolling.
  const rightArrow = secondaryNavigationBlock.locator('.nav-arrow.right');
  const leftArrow = secondaryNavigationBlock.locator('.nav-arrow.left');
  await expect(rightArrow).toBeVisible();
  // Click on right arrow a few times to make sure the left arrow shows.
  await rightArrow.click();
  await rightArrow.click();
  await expect(leftArrow).toBeVisible();

  // Make sure that the page navigates to the corrent anchor on click.
  const testLink = page.locator(NAV_LINK_CLASS, {hasText: content[2].heading});
  const anchor = await testLink.getAttribute('href');
  await testLink.click();
  const targetId = anchor.replace('#', '');
  await expect(page.locator(`[id="${targetId}"]`)).toBeInViewport();

  // On scroll down, the block should become sticky.
  await page.getByRole('heading', {name: content.at(-1).heading}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // Make sure that the block remains sticky on scroll up.
  await page.getByRole('heading', {name: content.at(-3).heading}).scrollIntoViewIfNeeded();
  await expect(secondaryNavigationBlock).toHaveCSS('position', 'sticky');

  // On mobile, the block should be a dropdown menu.
  await page.setViewportSize({width: 320, height: 500});
  await expect(secondaryNavigationBlock.locator('.dropdown-btn')).toBeVisible();
});

// Deliberately no Page Header pattern: the secondary nav must work without it.
test.describe('Secondary navigation without a Page Header', () => {
  let newPage;

  test.beforeAll(async ({requestUtils}) => {
    const sections = content.map(({heading, paragraph}) =>
      `<!-- wp:heading --><h2 class="wp-block-heading">${heading}</h2><!-- /wp:heading -->
     <!-- wp:paragraph --><p>${paragraph}</p><!-- /wp:paragraph -->`
    ).join('');

    newPage = await requestUtils.rest({
      path: '/wp/v2/pages',
      method: 'POST',
      data: {
        title: `Test secondary navigation ${Date.now()}`,
        status: 'publish',
        content: `<!-- wp:planet4-blocks/secondary-navigation /-->${sections}${filler}`,
      },
    });
  });

  test.afterAll(async ({requestUtils}) => {
    await requestUtils.rest({
      path: `/wp/v2/pages/${newPage.id}`,
      method: 'DELETE',
      data: {force: true},
    });
  });

  test('main navigation submenu stays above the secondary navigation', async ({page}) => {
    await page.goto(newPage.link, {waitUntil: 'domcontentloaded'});

    const secondaryNav = page.locator(SECONDARY_NAV);
    await expect(secondaryNav).toBeVisible();

    // Scroll a little so the secondary nav is stuck right under the header.
    await page.mouse.wheel(0, 300);

    const menuItem = page.locator(MAIN_NAV_ITEM_WITH_SUBMENU).first();
    const submenu = menuItem.locator('.nav-submenu');
    await menuItem.hover();
    await expect(submenu).toBeVisible();

    const submenuBox = await submenu.boundingBox();
    const navBox = await secondaryNav.boundingBox();

    expect(submenuBox.y + submenuBox.height).toBeGreaterThan(navBox.y);

    const submenuIsOnTop = await submenu.evaluate((el, point) => {
      return el.contains(document.elementFromPoint(point.x, point.y));
    }, {
      x: submenuBox.x + submenuBox.width / 2,
      y: submenuBox.y + submenuBox.height - 5,
    });

    expect(submenuIsOnTop).toBe(true);
  });

  test('secondary navigation stays visible when scrolling the page', async ({page}) => {
    await page.goto(newPage.link, {waitUntil: 'domcontentloaded'});

    await page.mouse.wheel(0, 1500);

    await expect(page.locator(SECONDARY_NAV)).toBeInViewport();
  });
});
