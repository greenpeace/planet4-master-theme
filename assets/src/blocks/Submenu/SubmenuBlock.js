import {SubmenuEditor} from './SubmenuEditor.js';
import {example} from './example';
import {getStyleLabel} from '../../functions/getStyleLabel';

const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/submenu';

export const registerSubmenuBlock = () => {
  const {registerBlockType, getBlockTypes} = wp.blocks;

  const blockAlreadyExists = getBlockTypes().find(block => block.name === BLOCK_NAME);

  if (blockAlreadyExists) {
    return;
  }

  registerBlockType(BLOCK_NAME, {
    title: 'Submenu',
    description: 'Insert text, media, and other content in an ordered list of clickable headings corresponding to the content sections on the page.',
    icon: 'welcome-widgets-menus',
    category: 'planet4-blocks',
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
    edit: SubmenuEditor,
    save() {
      return null;
    },
    example,
  });
};
