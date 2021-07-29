import {
  createNewPost,
  enablePageDialogAccept,
  getEditedPostContent,
  insertBlock,
  createURLMatcher,
  createJSONResponse,
  setUpResponseMocking,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  openSidebarPanelWithTitle,
  selectColorByName,
  typeInInputWithLabel,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

const SPREADSHEET_NO_URL_WARNING = 'No URL has been specified. Please edit the block and provide a Spreadsheet URL using the sidebar.';
const SPREADSHEET_INVALID_URL_WARNING = 'The Spreadsheet URL appears to be invalid.';

const SPREADSHEET_ID = '2P';
const SPREADSHEET_VALID_URL_EXAMPLE = `https://docs.google.com/spreadsheets/d/e/${ SPREADSHEET_ID }/pubhtml`;
const SPREADSHEET_API_URL = `get-spreadsheet-data?sheet_id=${ SPREADSHEET_ID }&_locale=user`;

const SPREADSHEET_MOCK_DATA = {
  'header': [ 'Name', 'Species', 'Legs','Fur'],
  'rows': [
    [ 'Garfield', 'Cat', '4', 'Yes' ],
    [ 'Pluto', 'Dog', '4', 'Yes' ],
    [ 'Mr. Ed', 'Horse', '4', 'Yes' ],
    [ 'Willy', 'Whale', '0', 'No' ],
    [ 'Donald', 'Duck', '2', 'No']
  ]
};

const SPREADSHEET_MOCK_RESPONSES = [
  {
    match:  createURLMatcher( SPREADSHEET_API_URL ),
    onRequestMatch: createJSONResponse( SPREADSHEET_MOCK_DATA ),
  },
];

describe( 'Spreadsheet block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions
    await enablePageDialogAccept();
  } );
  beforeEach( async () => {
    // Before running each test, go to the create post page
    await createNewPost();

    // Insert block by title
    await insertBlock( 'Spreadsheet' );
  } );

  // This is the first test, tests starts with it().
  it( 'is inserted into the Editor', async () => {
    // Check if block was inserted
    expect( await page.$( '[data-type="planet4-blocks/spreadsheet"]' ) ).not.toBeNull();

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );

  it( 'applies the background color correctly', async () => {
    await selectBlockByName( 'planet4-blocks/spreadsheet' );

    // Change background color
    await openSidebarPanelWithTitle( 'Setting' );
    await selectColorByName( 'green' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  } );

  it ( 'should show a warning if the URL is empty', async () => {
    await typeInInputWithLabel( 'Spreadsheet URL', '' );

    // The warning component should appear.
    await expect( page ).toMatchElement( '.block-editor .block-edit-mode-warning.is-warning' );

    // The warning text should appear.
    await expect( page ).toMatch( SPREADSHEET_NO_URL_WARNING );
  } );

  it ( 'should show a warning if the URL is wrong', async () => {
    await typeInInputWithLabel( 'Spreadsheet URL', 'this is not a URL' );

    // The warning component should appear.
    await expect( page ).toMatchElement( '.block-editor .block-edit-mode-warning.is-error' );

    // The warning text should appear.
    await expect( page ).toMatch( SPREADSHEET_INVALID_URL_WARNING );
  } );

  it ( 'should not show a warning if the URL is correct', async () => {
    await setUpResponseMocking( SPREADSHEET_MOCK_RESPONSES );

    await typeInInputWithLabel( 'Spreadsheet URL', SPREADSHEET_VALID_URL_EXAMPLE );

    // The warning component should not appear.
    await expect( page ).not.toMatchElement( '.block-editor .block-edit-mode-warning.is-warning' );

    // The error component should not appear.
    await expect( page ).not.toMatchElement( '.block-editor .block-edit-mode-warning.is-error' );
  } );

} );

