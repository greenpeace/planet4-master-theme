import { CoversEditor } from './CoversEditor.js';
import { frontendRendered } from '../frontendRendered';
import { Tooltip } from '@wordpress/components';

const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/covers';

const getStyleLabel = (label, help) => {
  if (help) {
    return (
      <Tooltip text={help}>
        <span>{label}</span>
      </Tooltip>
    );
  }
  return label;
};

const attributes = {
  title: {
    type: 'string',
  },
  description: {
    type: 'string',
  },
  tags: {
    type: 'array',
    default: []
  },
  posts: {
    type: 'array',
    default: []
  },
  post_types: {
    type: 'array',
    default: []
  },
  covers_view: {
    type: 'string',
    default: '1'
  },
  cover_type: {
    type: 'string',
    default: '3',
  },
};

export const registerCoversBlock = () => {
  const { registerBlockType } = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: 'Covers',
    icon: 'slides',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: CoversEditor,
    save: frontendRendered(BLOCK_NAME),
    // Add our custom styles
    styles: [
      {
        name: 'content',
        label: getStyleLabel(
          __('Content covers', 'planet4-blocks-backend'),
          __('Content covers pull the image from the post', 'planet4-blocks-backend'),
        ),
        isDefault: true
      },
      {
        name: 'take-action',
        label: getStyleLabel(
          __('Take Action covers', 'planet4-blocks-backend'),
          __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button', 'planet4-blocks-backend'),
        ),
      },
      {
        name: 'campaign',
        label: getStyleLabel(
          __('Campaign covers', 'planet4-blocks-backend'),
          __('Campaign covers pull the associated image and hashtag from the system tag definitions', 'planet4-blocks-backend'),
        ),
      }
    ],
    deprecated: [
      {
        attributes,
        save() {
          return null;
        }
      }
    ]
  });
}

