import {
  createNewPost,
  enablePageDialogAccept,
  getEditedPostContent,
  insertBlock,
  clickButton,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  openSidebarPanelWithTitle,
  selectStyleByName,
  typeInInputWithPlaceholderLabel,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const LEVELS = [
  {
    heading: '2',
    link: true,
    style: 'none',
  },
  {
    heading: '3',
    link: true,
    style: 'bullet',
  },
  {
    heading: '4',
    link: true,
    style: 'number',
  }
];

describe( 'Submenu block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions
    await enablePageDialogAccept();
  } );
  beforeEach( async () => {
    // Before running each test, go to the create post page
    await createNewPost({
      postType: 'page',
      title: 'Test Submenu block',
      showWelcomeGuide: false,
    });

    // Insert block by title
    await insertBlock( 'Submenu' );
    await page.waitForSelector( '.submenu-block' );
  } , 40000);

  // On the basis of level and position return the html element id.
  const getSubmenuElementId = async ( level, position ) => {
    let [ inputEl ] = await page.$x( `//p[contains(text(),"${ level }")]/following-sibling::div[${ position }]//select` );
    if (! inputEl ) {
      [ inputEl ] = await page.$x( `//p[contains(text(),"${ level }")]/following-sibling::div[${ position }]//input` );
    }
    const propertyHandle = await inputEl.getProperty('id');
    return await propertyHandle.jsonValue();
  };

  // This is the first test, tests starts with it().
  it( 'is inserted into the Editor', async () => {
    // Check if block was inserted
    expect( await page.$( '[data-type="planet4-blocks/submenu"]' ) ).not.toBeNull();

    expect( await getEditedPostContent() ).toMatchSnapshot();
  });

  it( 'should work with "Long full-width" style', async () => {
    await selectBlockByName( 'planet4-blocks/submenu' );

    await typeInInputWithPlaceholderLabel( 'Enter title', 'Submenu title' );

    // Select style.
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Long full-width' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );

    // Add 3 levels inputs of submenu.
    for (let i = 0; i < 3; i++) {
      let level = i + 1;
      let selectorId = await getSubmenuElementId( 'Level ' + level, 1 );
      await page.select('select#' + selectorId , LEVELS[i].heading);
      if ( LEVELS[i].link ) {
        selectorId = await getSubmenuElementId( 'Level ' + level, 2 );
        await page.click('input#' + selectorId);
      }
      selectorId = await getSubmenuElementId( 'Level ' + level, 3 );
      await page.select('select#' + selectorId , LEVELS[i].style);

      await clickButton( 'Add level' );
    }

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );

  it( 'should work with "Short full-width" style', async () => {
    await selectBlockByName( 'planet4-blocks/submenu' );

    await typeInInputWithPlaceholderLabel( 'Enter title', 'Submenu title' );

    // Select style.
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Short full-width' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );

    // Add 3 levels inputs of submenu.
    for (let i = 0; i < 3; i++) {
      let level = i + 1;
      let selectorId = await getSubmenuElementId( 'Level ' + level, 1 );
      await page.select('select#' + selectorId , LEVELS[i].heading);
      if ( LEVELS[i].link ) {
        selectorId = await getSubmenuElementId( 'Level ' + level, 2 );
        await page.click('input#' + selectorId);
      }
      selectorId = await getSubmenuElementId( 'Level ' + level, 3 );
      await page.select('select#' + selectorId , LEVELS[i].style);

      await clickButton( 'Add level' );
    }

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );

  it( 'should work with "Short sidebar" style', async () => {
    await selectBlockByName( 'planet4-blocks/submenu' );

    await typeInInputWithPlaceholderLabel( 'Enter title', 'Submenu title' );

    // Select style.
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Short sidebar' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );

    // Add 3 levels inputs of submenu.
    for (let i = 0; i < 3; i++) {
      let level = i + 1;
      let selectorId = await getSubmenuElementId( 'Level ' + level, 1 );
      await page.select('select#' + selectorId , LEVELS[i].heading);
      if ( LEVELS[i].link ) {
        selectorId = await getSubmenuElementId( 'Level ' + level, 2 );
        await page.click('input#' + selectorId);
      }
      selectorId = await getSubmenuElementId( 'Level ' + level, 3 );
      await page.select('select#' + selectorId , LEVELS[i].style);

      await clickButton( 'Add level' );
    }

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );
} );
