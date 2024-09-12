import {TableOfContentsEditor} from './TableOfContentsEditor.js';
import {example} from './example.js';
import {getStyleLabel} from '../../functions/getStyleLabel.js';
import {BLOCK_NAME, P4_BLOCK_PREFIX} from '../Constants/Constants.js';

const {__} = wp.i18n;

export const registerTableOfContentsBlock = () => {
  const {registerBlockType} = wp.blocks;

  registerBlockType(BLOCK_NAME.tableOfContents.name, {
    title: BLOCK_NAME.tableOfContents.title,
    description: __('Insert text, media, and other content in an ordered list of clickable headings corresponding to the content sections on the page.', 'planet4-blocks-backend'),
    icon: 'welcome-widgets-menus',
    category: P4_BLOCK_PREFIX,
    attributes: {
      title: {
        type: 'string',
        default: '',
      },
      submenu_style: { // Needed for old blocks conversion
        type: 'integer',
        default: 0,
      },
      levels: {
        type: 'array',
        default: [{heading: 2, link: false, style: 'none'}],
      },
      isExample: {
        type: 'boolean',
        default: false,
      },
      exampleMenuItems: { // Used for the block's preview, which can't extract items from anything.
        type: 'array',
      },
    },
    supports: {
      multiple: false, // Use the block just once per post.
      html: false,
    },
    styles: [
      {
        name: 'long',
        label: getStyleLabel(
          __('Long full-width', 'planet4-blocks-backend'),
          __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words). No max items recommended.', 'planet4-blocks-backend')
        ),
        isDefault: true,
      },
      {
        name: 'short',
        label: getStyleLabel(
          __('Short full-width', 'planet4-blocks-backend'),
          __('Use: on long pages (more than 5 screens) when list items are short (up to 5 words). No max items recommended.', 'planet4-blocks-backend')
        ),
      },
      {
        name: 'sidebar',
        label: getStyleLabel(
          __('Short sidebar', 'planet4-blocks-backend'),
          __('Use: on long pages (more than 5 screens) when list items are short (up to 10 words). Max items recommended: 9', 'planet4-blocks-backend')
        ),
      },
    ],
    edit: TableOfContentsEditor,
    save() {
      return null;
    },
    example,
  });
};
