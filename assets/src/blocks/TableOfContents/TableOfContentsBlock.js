import {TableOfContentsEditor} from './TableOfContentsEditor';
import {example} from './example';
import {getStyleLabel} from '../../functions/getStyleLabel';

const BLOCK_NAME = 'planet4-blocks/submenu';

export const registerTableOfContentsBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {useBlockProps} = wp.blockEditor;
  const {__} = wp.i18n;

  registerBlockType(BLOCK_NAME, {
    title: 'Table of Contents',
    description: __('Insert text, media, and other content in an ordered list of clickable headings corresponding to the content sections on the page.', 'planet4-master-theme-backend'),
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
        default: [{heading: 2, link: true, style: 'none'}],
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
          __('Long full-width', 'planet4-master-theme-backend'),
          __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words). No max items recommended.', 'planet4-master-theme-backend')
        ),
        isDefault: true,
      },
      {
        name: 'short',
        label: getStyleLabel(
          __('Short full-width', 'planet4-master-theme-backend'),
          __('Use: on long pages (more than 5 screens) when list items are short (up to 5 words). No max items recommended.', 'planet4-master-theme-backend')
        ),
      },
      {
        name: 'sidebar',
        label: getStyleLabel(
          __('Short sidebar', 'planet4-master-theme-backend'),
          __('Use: on long pages (more than 5 screens) when list items are short (up to 10 words). Max items recommended: 9', 'planet4-master-theme-backend')
        ),
      },
    ],
    edit: props => (
      <div {...useBlockProps()}>
        <TableOfContentsEditor {...props} />
      </div>
    ),
    save() {
      return null;
    },
    example,
  });
};
