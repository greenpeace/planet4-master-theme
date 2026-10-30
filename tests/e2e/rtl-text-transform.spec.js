import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

// Arabic and Hebrew have no letter case, so RTL pages must not transform text.
test('RTL pages do not use text-transform', async ({page, requestUtils}) => {
  let pageId;

  try {
    const newPage = await requestUtils.rest({
      path: '/wp/v2/pages',
      method: 'POST',
      data: {
        title: `Test RTL text transform ${Date.now()}`,
        status: 'publish',
        content: `
          <!-- wp:heading {"level":2} --><h2>Heading two</h2><!-- /wp:heading -->
          <!-- wp:heading {"level":3} --><h3>Heading three</h3><!-- /wp:heading -->
          <!-- wp:paragraph --><p>A paragraph with a <a href="#">link</a>.</p><!-- /wp:paragraph -->
          <!-- wp:list --><ul><li>List item</li></ul><!-- /wp:list -->
          <!-- wp:quote --><blockquote class="wp-block-quote"><p>A quote</p></blockquote><!-- /wp:quote -->
          <!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link">A button</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
        `,
      },
    });
    pageId = newPage.id;

    await page.goto(newPage.link, {waitUntil: 'domcontentloaded'});

    // Force RTL so `html[dir="rtl"]` selectors apply.
    await page.evaluate(() => document.documentElement.setAttribute('dir', 'rtl'));

    // Collect every element whose computed text-transform is not "none".
    const offenders = await page.evaluate(() => {
      const found = [];
      for (const el of document.querySelectorAll('body *')) {
        const transform = getComputedStyle(el).textTransform;
        if (transform !== 'none') {
          found.push(`${el.tagName.toLowerCase()}.${el.className} -> ${transform}`);
        }
      }
      return found;
    });

    expect(offenders, `Elements with text-transform in RTL:\n${offenders.join('\n')}`).toEqual([]);
  } finally {
    if (pageId) {
      await requestUtils.rest({
        path: `/wp/v2/pages/${pageId}`,
        method: 'DELETE',
        data: {force: true},
      });
    }
  }
});
