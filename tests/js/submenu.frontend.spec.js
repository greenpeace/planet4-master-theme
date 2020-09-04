import {
  createNewPost,
  enablePageDialogAccept,
  setPostContent,
  publishPost,
} from '@wordpress/e2e-test-utils';

import {
  clickElementByText,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const PAGE_CONTENT = `
  <!-- wp:heading {"level":2} -->
  <h2>H2 no 1</h2>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>This is the text of \`H2 no 1\`<br>
  <br>This is quite a long text so that stuff below is definitely out of the viewport without scrolling</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":3} -->
  <h3>H2.H3 no 1</h3>
  <!-- /wp:heading -->
  <!-- wp:heading {"level":3} -->
  <p>This is the text of \`H2.H3 no 1\`</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":3} -->
  <h3>H2.H3 no 2</h3>
  <!-- /wp:heading -->
  <!-- wp:heading {"level":3} -->
  <p>This is the text of \`H2.H3 no 2\`</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":4} -->
  <h4>H2.H3.H4 no 1</h4>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>This is the text of \`H2.H3.H4 no 1\`</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading {"level":4} -->
  <h4>H2.H3.H4 no 2</h4>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>This is the text of \`H2.H3.H4 no 2\`</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading -->
  <h2>H2 no 2</h2>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>This is the text of \`H2 no 2\`<br>H2 no 2<br>H2 no 2</p>
  <!-- /wp:paragraph -->
  <!-- wp:heading -->
  <h2>H2 no 3</h2>
  <!-- /wp:heading -->
  <!-- wp:paragraph -->
  <p>This is the text of \`H2 no 3\`</p>
  <!-- /wp:paragraph -->
`;

const SUBMENU_BLOCK = `
<!-- wp:planet4-blocks/submenu {"title":"Submenu block title","levels":[{"heading":2,"link":true,"style":"none"},{"heading":3,"link":true,"style":"bullet"},{"heading":4,"link":true,"style":"number"}]} /-->
`;

describe( 'Submenu block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions
    await enablePageDialogAccept();
  } );

  it( 'should work on frontend', async ( done ) => {
    // Create a new page.
    await createNewPost({
      postType: 'page',
      title: 'Test Submenu block on frontend',
      showWelcomeGuide: false,
    });

    // Add the default page content and submenu block.
    await setPostContent( SUBMENU_BLOCK + PAGE_CONTENT );

    // Publish the page.
    await publishPost();

    await page.waitForSelector( '.components-snackbar__action' );

    // Wait for page update.
    await new Promise((r) => setTimeout(r, 300));

    // Test submenu block on frontend.
    await clickElementByText( 'a', 'View Page' );

    await page.waitForNavigation();

    // Wait for page load.
    await new Promise((r) => setTimeout(r, 300));

    // The "Submenu Block" should appear on page.
    await expect( page ).toMatchElement( '.block.submenu-block' );

    // Check the block title
    await expect(page).toMatchElement('.submenu-block h2', { text: 'Submenu block title' });

    // Check the submenu links for 3 levels and their corresponding list styles.
    await expect(page).toMatchElement('li.list-style-none a[href=\\#h2-no-1]', { text: 'H2 no 1' });
    await expect(page).toMatchElement('li.list-style-bullet a[href=\\#h2\\.h3-no-2]', { text: 'H2.H3 no 2' });
    await expect(page).toMatchElement('li.list-style-number a[href=\\#h2\\.h3\\.h4-no-1]', { text: 'H2.H3.H4 no 1' });

    // On click of submenu link, Move focus to actual header text.
    await clickElementByText( 'a', 'H2.H3 no 1' );

    const heightFromTop = await page.evaluate(
      () => document.getElementById('h2.h3-no-1').getBoundingClientRect().top
    );

    // Test the height of element from browser top.
    expect( heightFromTop ).toBe( 100 );

    const heightFromTopBeforeClick = await page.evaluate(
      () => document.getElementById('h2-no-3').getBoundingClientRect().top
    );

    // On click of submenu link, Move focus to actual header.
    await clickElementByText( 'a', 'H2 no 3' );

    const heightFromTopAfterClick = await page.evaluate(
      () => document.getElementById('h2-no-3').getBoundingClientRect().top
    );

    expect( heightFromTopBeforeClick ).toBeGreaterThan( heightFromTopAfterClick );

    done();
  }, 80000 );
} );
