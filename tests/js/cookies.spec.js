import {
  createNewPost,
  enablePageDialogAccept,
  getEditedPostContent,
  insertBlock,
} from '@wordpress/e2e-test-utils';

import {
  selectBlockByName,
  typeInInputWithPlaceholderLabel,
  typeInInputWithCheckbox,
} from './e2e-tests-helpers';

import 'expect-puppeteer';

describe( 'Cookies block', () => {
  beforeAll( async () => {
    // This helps in overriding Wordpress dialogs preventing actions
    await enablePageDialogAccept();
  } );
  beforeEach( async () => {
    // Before running each test, go to the create post page
    await createNewPost( {
      postType: 'page',
      title: 'Test Cookies block',
    } );

    // Insert block by title
    await insertBlock( 'Cookies' );
  } );

  // This is the first test, tests starts with it().
  it( 'is inserted into the Editor', async () => {
    // Check if block was inserted
    expect( await page.$( '[data-type="planet4-blocks/cookies"]' ) ).not.toBeNull();

    expect( await getEditedPostContent() ).toMatchSnapshot();
  }, 25000);

  it( 'should work with all inputs', async () => {
    await selectBlockByName( 'planet4-blocks/cookies' );

    // Add richtext inputs.
    await typeInInputWithPlaceholderLabel( 'Enter title', 'Cookies block' );
    await typeInInputWithPlaceholderLabel( 'Enter description', 'Test description' );

    await typeInInputWithCheckbox( 'necessary_cookies', 'Necessary cookies' );
    await typeInInputWithPlaceholderLabel( 'Enter necessary cookies description', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.' );

    await typeInInputWithCheckbox( 'all_cookies', 'Other Cookies' );
    await typeInInputWithPlaceholderLabel( 'Enter all cookies description', 'Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.' );

    expect( await getEditedPostContent() ).toMatchSnapshot();
  });
} );
