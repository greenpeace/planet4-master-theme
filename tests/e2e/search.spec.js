import {test, expect} from './tools/lib/test-utils.js';

test.useAdminLoggedIn();

test('check search works', async ({page, requestUtils}) => {
  const testId = `testsearch-${Math.floor(Math.random() * 10000)}`;//NOSONAR
  const tagName = `Tag ${testId}`;
  const tagPageTitle = `#Tag ${testId}`;
  const postTitle = `Test Post ${testId}`;

  const tagPage = await requestUtils.rest({
    path: '/wp/v2/pages',
    method: 'POST',
    data: {
      title: tagPageTitle,
      content: '<!-- wp:paragraph --><p>The redirect page for the new tag</p><!-- /wp:paragraph -->',
      status: 'publish',
      featured_media: 357,
    },
  });

  const tag = await requestUtils.rest({
    path: '/wp/v2/tags',
    method: 'POST',
    data: {
      slug: `tag-${testId}`,
      name: tagName,
      description: `Description of the tag ${testId}`,
      meta: {
        redirect_page: tagPage.id,
      },
    },
  });

  await requestUtils.rest({
    path: '/wp/v2/posts',
    method: 'POST',
    data: {
      title: postTitle,
      content: '<!-- wp:paragraph --><p>This is a search test post</p><!-- /wp:paragraph -->',
      status: 'publish',
      featured_media: 357,
      tags: [tag.id],
    },
  });

  await page.goto('./');

  const searchBox = page.getByPlaceholder('Search');
  await searchBox.click();
  await searchBox.fill(testId);
  await page.keyboard.press('Enter');

  const searchResult = await page.innerHTML('.result-statement');
  const searchPage = await page.locator('.search-result-item-headline').allInnerTexts();
  const searchTags = await page.locator('.search-result-item-tag').allInnerTexts();

  if (searchResult) {
    expect(searchResult).toContain(testId);
  }
  if (searchPage) {
    expect(searchPage).toContain(tagPageTitle);
    expect(searchPage).toContain(postTitle);
  }
  if (searchTags) {
    expect(searchTags).toContain(`#${tagName}`);
  }
});
