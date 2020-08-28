import {
  createNewPost,
  enablePageDialogAccept,
  getEditedPostContent,
  insertBlock,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  openSidebarPanelWithTitle,
  typeInInputWithLabel,
  selectStyleByName,
  typeInInputWithPlaceholderLabel,
  typeInTextareaWithLabel,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const COUNTER_API_URL = 'https://global-petition-counter.appspot.com/counter/palmoil';

describe( 'Counter block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions
    await enablePageDialogAccept();
  } );
  beforeEach( async () => {
    // Before running each test, go to the create post page
    await createNewPost();

    // Insert block by title
    await insertBlock( 'Counter' );
  } );

  // This is the first test, tests starts with it().
  it( 'is inserted into the Editor', async () => {
    // Check if block was inserted
    expect( await page.$( '[data-type="planet4-blocks/counter"]' ) ).not.toBeNull();

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 20000);

  it( 'with text only style', async () => {
    await selectBlockByName( 'planet4-blocks/counter' );

    // Add richtext inputs.
    await typeInInputWithPlaceholderLabel( 'Enter title', 'Text only counter block' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Test description' );

    // Select the Text only counter style
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Text Only' );

    // Select the Text only counter style
    await openSidebarPanelWithTitle( 'Setting' );
    await typeInInputWithLabel( 'Completed', '60' );
    await typeInInputWithLabel( 'Target', '100' );
    await typeInTextareaWithLabel( 'Text', '%completed% out of %target%, %remaining% remaining' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 50000 );

  it( 'with Progress Bar', async () => {
    await selectBlockByName( 'planet4-blocks/counter' );

    // Add richtext inputs.
    await typeInInputWithPlaceholderLabel( 'Enter title', 'Progress Bar counter block' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Test description' );

    // Select the Progress Bar counter style
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Progress Bar' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );
    await typeInInputWithLabel( 'Completed', '70' );
    await typeInInputWithLabel( 'Target', '100' );
    await typeInTextareaWithLabel( 'Text', '%completed% out of %target%, %remaining% remaining' );

    // The Progress Bar component should appear.
    await expect( page ).toMatchElement( '.progress-bar' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 50000 );

  it( 'with Progress Dial', async () => {
    await selectBlockByName( 'planet4-blocks/counter' );

    // Add richtext inputs.
    await typeInInputWithPlaceholderLabel( 'Enter title', 'Progress Dial counter block' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Test description' );

    // Select the Progress Dial counter style
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Progress Dial' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );
    await typeInInputWithLabel( 'Completed', '75' );
    await typeInInputWithLabel( 'Target', '100' );
    await typeInTextareaWithLabel( 'Text', '%completed% out of %target%, %remaining% remaining' );

    // The Progress Dial component should appear.
    await expect( page ).toMatchElement( '.progress-arc' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 50000 );

  it( 'with API input', async () => {
    await selectBlockByName( 'planet4-blocks/counter' );

    // Add richtext inputs.
    await typeInInputWithPlaceholderLabel( 'Enter title', 'Counter with API input' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Test description' );

    // Select the Text only counter style
    await openSidebarPanelWithTitle( 'Styles' );
    await selectStyleByName( 'Progress Dial' );

    // Add Setting inputs
    await openSidebarPanelWithTitle( 'Setting' );
    await typeInInputWithLabel( 'Completed API URL', COUNTER_API_URL );

    await typeInInputWithLabel( 'Target', '5000000' );
    await typeInTextareaWithLabel( 'Text', '%completed% out of %target%, %remaining% remaining' );

    // The Progress Bar component should appear.
    await expect( page ).toMatchElement( '.progress-arc' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } , 60000 );
} );
