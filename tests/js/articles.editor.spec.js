import {
  createNewPost,
  enablePageDialogAccept,
  getEditedPostContent,
  insertBlock,
  publishPost,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  openSidebarPanelWithTitle,
  typeInInputWithLabel,
  typeInInputWithPlaceholderLabel,
  clickElementByText,
  typeInDropdownWithLabel,
  clearPreviousTextWithLabel,
  clearPreviousTextWithPlaceholder,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const ARTICLES_INVALID_URL_WARNING = 'The URL must start with "https://"';

describe( 'Articles block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions.
    await enablePageDialogAccept();

    // Create post data.
    await createNewPost({
      title: 'First post',
      showWelcomeGuide: false,
    });
    await page.waitForSelector( '.editor-post-publish-button__button' );
    await publishPost();

    await createNewPost({
      title: 'Second post',
      showWelcomeGuide: false,
    });
    await page.waitForSelector( '.editor-post-publish-button__button' );
    await publishPost();

    await createNewPost({
      title: 'Third post',
      showWelcomeGuide: false,
    });
    await page.waitForSelector( '.editor-post-publish-button__button' );
    await publishPost();

  }, 50000 );

  beforeEach( async () => {
    // Before running each test, go to the create post page.
    await createNewPost({
      postType: 'page',
      title: 'Test Articles block',
      showWelcomeGuide: false,
    } );

    // Insert block by title.
    await insertBlock( 'Articles' );
    await page.waitForSelector( '.articles-title-container' );

  }, 40000);

  // This is the first test, tests starts with it().
  it ( 'is inserted into the Editor', async () => {
    // Check if block was inserted
    expect( await page.$( '[data-type="planet4-blocks/articles"]' ) ).not.toBeNull();

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );

  it ( 'should work with all editor inputs', async () => {
    await selectBlockByName( 'planet4-blocks/articles' );

    // Add richtext inputs.
    await clearPreviousTextWithPlaceholder( 'Enter title' );
    await typeInInputWithPlaceholderLabel( 'Enter title', 'News' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Latest news details' );

    // Add loadmore button text in wysiwyg editor.
    await clearPreviousTextWithLabel( 'Button Text' );
    await typeInInputWithPlaceholderLabel( 'Enter text', 'More News' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Settings' );
    await clearPreviousTextWithLabel( 'Button Text' );
    await typeInInputWithLabel( 'Button Text', 'Read more' );
    await typeInInputWithLabel( 'Button Link', 'https://www.greenpeace.org' );
    await clickElementByText( 'label', 'Open in a new Tab' );
    await clearPreviousTextWithLabel( 'Articles count' );
    await typeInInputWithLabel( 'Articles count', '4' );
    await typeInDropdownWithLabel( 'Select Tags', 'Climate' );
    await typeInDropdownWithLabel( 'Post Types', 'Story' );
    await clickElementByText( 'label', 'Ignore categories' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 50000 );

  it ( 'should show a warning if the URL is wrong', async () => {
    await typeInInputWithLabel( 'Button Link', 'this is not a URL' );

    // The warning component should appear.
    await expect( page ).toMatchElement( '.edit-post-sidebar .input_error' );

    // The warning text should appear.
    await expect( page ).toMatch( ARTICLES_INVALID_URL_WARNING );
  } );

  it ( 'should use only the manually chosen posts if any are entered', async () => {
    await openSidebarPanelWithTitle( 'Settings' );

    // The below posts are added randomly in "Manual override" field,
    // but on frontend it should appear order by publication date.
    await typeInDropdownWithLabel( 'Manual override', 'Second post' );
    await typeInDropdownWithLabel( 'Manual override', 'First post' );
    await typeInDropdownWithLabel( 'Manual override', 'Third post' );

    // The only 3 manually override post should appear in articles block.
    await expect(page).toMatchElement('article:nth-child(1) h4 a', { text: 'Third post' });
    await expect(page).toMatchElement('article:nth-child(2) h4 a', { text: 'Second post' });
    await expect(page).toMatchElement('article:nth-child(3) h4 a', { text: 'First post' });

    // The "Load more" should not appear.
    await expect( page ).not.toMatchElement( '.block-editor .article-load-more' );
  } , 50000 );
} );

