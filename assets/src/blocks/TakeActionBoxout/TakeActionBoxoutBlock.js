import {TakeActionBoxoutEditor} from './TakeActionBoxoutEditor.js';
import {takeActionBoxoutV1} from './deprecated/takeActionBoxoutV1';

const {registerBlockType, getBlockTypes} = wp.blocks;

const BLOCK_NAME = 'planet4-blocks/take-action-boxout';

export const registerTakeActionBoxoutBlock = () => {
  const blockAlreadyExists = getBlockTypes().find(block => block.name === BLOCK_NAME);

  if (blockAlreadyExists) {
    return;
  }

  registerBlockType(BLOCK_NAME, {
    title: 'Take Action Boxout',
    icon: 'welcome-widgets-menus',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    // This attributes definition mimics the one in the PHP side.
    attributes: {
      take_action_page: {
        type: 'number',
      },
      title: {
        type: 'string',
      },
      excerpt: {
        type: 'string',
      },
      link: {
        type: 'string',
      },
      linkText: {
        type: 'string',
      },
      newTab: {
        type: 'boolean',
        default: false,
      },
      tag_ids: {
        type: 'array',
        default: [],
      },
      imageId: {
        type: 'number',
        default: '',
      },
      imageUrl: {
        type: 'string',
        default: '',
      },
      imageAlt: {
        type: 'string',
        default: '',
      },
      stickyOnMobile: {
        type: 'boolean',
        default: false,
      },
    },
    edit: TakeActionBoxoutEditor,
    save() {
      return null;
    },
    deprecated: [
      takeActionBoxoutV1,
    ],
  });
};
