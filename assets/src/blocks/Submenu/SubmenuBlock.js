import { SubmenuEditor } from './SubmenuEditor.js';
import { Tooltip } from '@wordpress/components';

const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/submenu';

const getStyleLabel = (label, help) => {
  if (help) {
    return (
      <Tooltip text={__(help, 'planet4-blocks-backend')}>
        <span>{__(label, 'planet4-blocks-backend')}</span>
      </Tooltip>
    );
  }
  return label;
}

export class SubmenuBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;

    registerBlockType(BLOCK_NAME, {
      title: 'Submenu',
      icon: 'welcome-widgets-menus',
      category: 'planet4-blocks',
      attributes: {
        title: {
          type: 'string',
          default: ''
        },
        submenu_style: { // Needed for old blocks conversion
          type: 'integer',
          default: 0
        },
        levels: {
          type: 'array',
          default: [{ heading: 0, link: false, style: 'none' }]
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
            'Long full-width',
            'Use: on long pages (more than 5 screens) when list items are long (+ 10 words). No max items recommended.'
          ),
          isDefault: true
        },
        {
          name: 'short',
          label: getStyleLabel(
            'Short full-width',
            'Use: on long pages (more than 5 screens) when list items are short (up to 5 words). No max items recommended.'
          )
        },
        {
          name: 'sidebar',
          label: getStyleLabel(
            'Short sidebar',
            'Use: on long pages (more than 5 screens) when list items are short (up to 10 words). Max items recommended: 9'
          )
        }
      ],
      edit: SubmenuEditor,
      save() {
        return null;
      }
    });
  };
}

