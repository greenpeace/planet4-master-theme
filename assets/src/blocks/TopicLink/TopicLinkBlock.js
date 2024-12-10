import {TopicLinkEditor} from './TopicLinkEditor.js';

const {registerBlockType, getBlockTypes} = wp.blocks;
const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/topic-link';

export const registerTopicLinkBlock = () => {
  const blockAlreadyExists = getBlockTypes().find(block => block.name === BLOCK_NAME);

  if (blockAlreadyExists) {
    return;
  }

  registerBlockType(BLOCK_NAME, {
    title: 'Topic Link',
    description: __('', 'planet4-blocks-backend'),
    icon: 'welcome-widgets-menus',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    // This attributes definition mimics the one in the PHP side.
    attributes: {
      categoryId: {
        type: 'number',
        default: '',
      },
      categoryLink: {
        type: 'string',
        default: '',
      },
      selectedCategory: {
        type: 'string',
        default: '',
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
      focal_points: {
        type: 'string',
        default: '50% 50%',
      },
    },
    edit: TopicLinkEditor,
    save() {
      return null;
    },
  });
};
