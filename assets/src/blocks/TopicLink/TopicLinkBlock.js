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
    description: __('A versatile horizontal card featuring an image, description, and Call to Action, designed to link to Take Action pages or any custom link such as external petitions or donation pages.', 'planet4-blocks-backend'),
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
    edit: TopicLinkEditor,
    save() {
      return null;
    },
  });
};
