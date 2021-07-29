import {
  createNewPost,
  enablePageDialogAccept,
  setPostContent,
  publishPost,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  openSidebarPanelWithTitle,
  clickElementByText,
  typeInDropdownWithLabel,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const ARTICLES_BLOCK = `
<!-- wp:planet4-blocks/articles {"article_count":2} -->
<div class="wp-block-planet4-blocks-articles" data-render="planet4-blocks/articles" data-attributes="{&quot;attributes&quot;:{&quot;article_heading&quot;:&quot;Related Articles&quot;,&quot;article_count&quot;:2,&quot;tags&quot;:[],&quot;posts&quot;:[],&quot;post_types&quot;:[],&quot;read_more_text&quot;:&quot;Load more&quot;,&quot;read_more_link&quot;:&quot;&quot;,&quot;button_link_new_tab&quot;:false,&quot;ignore_categories&quot;:false},&quot;innerBlocks&quot;:[]}"></div>
<!-- /wp:planet4-blocks/articles -->
`;

describe( 'Articles block frontend', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions.
    await enablePageDialogAccept();
  } );

  it ( 'test post thumbnail behaviour', async () => {
    // Create new post.
    await createNewPost({
      title: 'Test Articles with no thumbnail',
      content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
      showWelcomeGuide: false,
    });
    await publishPost();
    await page.waitForSelector( '.post-publish-panel__postpublish' );

    //Create a new page and add newly created post in it.
    await createNewPost({
      postType: 'page',
      title: 'Test Articles block thumbnail',
      showWelcomeGuide: false,
    });

    // Add articles block in page.
    await setPostContent( ARTICLES_BLOCK );

    await selectBlockByName( 'planet4-blocks/articles' );

    await openSidebarPanelWithTitle( 'Setting' );

    // Manually add post(without thumbnail img) in articles blocks.
    await typeInDropdownWithLabel( 'Manual override', 'Test Articles with no thumbnail' );

    // Manually add post(post with image inside post content, which was set as a feature image
    // & display as a thumbnail in articles block) in articles blocks.
    await typeInDropdownWithLabel( 'Manual override', 'Lorem ipsum dolor sit amet' );

    // Publish the page.
    await publishPost();

    await new Promise((r) => setTimeout(r, 500));

    await page.waitForSelector( '.post-publish-panel__postpublish' );

    // Test articles block on frontend.
    await clickElementByText( '*[@class="editor-post-publish-panel"]//a', 'View Page' );

    await page.waitForNavigation();

    await page.waitForSelector( '.page-template' );

    // The "Articles Block" should appear on page.
    await expect( page ).toMatchElement( '.block.articles-block' );

    // If there is no image in the description of the post(also no featured image set),
    // the blank space display as a thumbnail photo in articles block.
    await expect(page).toMatchElement('article:nth-child(1) img', { src: ''} );
    // If no Featured Image set, first image of post is set as a Featured Image &
    // appear as a thumbnail in articles block.
    const img_src = await page.$eval('article:nth-child(2) img', el => el.src);
    expect( img_src ).not.toBeNull();
  } , 50000 );

  it ( 'should load the right amount of posts when clicking the load more button', async ( done ) => {
    await createNewPost({
      postType: 'page',
      title: 'Test Articles block frontend',
      showWelcomeGuide: false,
    });

    // Add the default page content and articles block.
    await setPostContent( ARTICLES_BLOCK );

    // Publish the page.
    await publishPost();

    await page.waitForSelector( '.post-publish-panel__postpublish' );

    // Test articles block on frontend.
    await clickElementByText( '*[@class="editor-post-publish-panel"]//a', 'View Page' );

    await page.waitForNavigation();

    await page.waitForSelector( '.page-template' );

    // Wait for articles block to be loaded.
    await new Promise((r) => setTimeout(r, 400));

    // The "Articles Block" should appear on page.
    await expect( page ).toMatchElement( '.block.articles-block' );

    // Number of post in articles block.
    const articlesCount = await page.evaluate(
      () =>
        document.querySelectorAll( '.block.articles-block article' )
          .length
    );
    expect( articlesCount ).toEqual(2);

    await clickElementByText( 'button', 'Load more' );

    // Wait for load more response.
    await new Promise((r) => setTimeout(r, 300));

    await page.waitForSelector( '.article-load-more' );

    // Number of post in articles block after load more.
    const updatedArticlesCount = await page.evaluate(
      () =>
        document.querySelectorAll( '.block.articles-block article' )
          .length
    );
    expect( updatedArticlesCount ).toEqual(4);

    done();
  } );

  it ( 'should load current post tags and categories articles on post page', async () => {
    // Create new post.
    await createNewPost({
      title: 'Lorem Ipsum - tags:Oil and Forests, Categories:Nature',
      content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
      showWelcomeGuide: false,
    });

    await openSidebarPanelWithTitle( 'Tags' );
    await typeInDropdownWithLabel( 'Add New Tag', 'Oil' );
    await typeInDropdownWithLabel( 'Add New Tag', 'Forests' );

    await openSidebarPanelWithTitle( 'Categories' );
    // Wait for panel details.
    await new Promise((r) => setTimeout(r, 300));
    await clickElementByText( 'label', 'Nature' );

    // Publish the post.
    await publishPost();
    await page.waitForSelector( '.post-publish-panel__postpublish' );

    //Create a new post and add same tags & categories in it.
    await createNewPost({
      title: 'Test Articles block on Post',
      content: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
      showWelcomeGuide: false,
    });

    await openSidebarPanelWithTitle( 'Tags' );
    await typeInDropdownWithLabel( 'Add New Tag', 'Oil' );
    await typeInDropdownWithLabel( 'Add New Tag', 'Forests' );

    await openSidebarPanelWithTitle( 'Categories' );
    // Wait for panel details.
    await new Promise((r) => setTimeout(r, 300));
    await clickElementByText( 'label', 'Nature' );

    // Publish the post.
    await publishPost();

    await page.waitForSelector( '.components-snackbar__action' );

    // Wait for post update.
    await new Promise((r) => setTimeout(r, 300));

    // Test articles block on frontend.
    await clickElementByText( 'a', 'View Post' );

    await page.waitForNavigation();

    // The "Articles Block" should appear on post.
    await expect( page ).toMatchElement( '.block.articles-block' );

    await page.waitForSelector( '.article-list-item' );

    await expect(page).toMatchElement('article:nth-child(1) h4 a', { text: 'Lorem Ipsum - tags:Oil and Forests, Categories:Nature' });
  } , 90000 );
} );
