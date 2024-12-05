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
      take_action_page: {
        type: 'number',
      },
      title: {
        type: 'string',
      },
      imageId: {
        type: 'number',
        default: '',
      },
      focal_points: {},
    },
    edit: TopicLinkEditor,
    save() {
      return null;
    },
  });
};
