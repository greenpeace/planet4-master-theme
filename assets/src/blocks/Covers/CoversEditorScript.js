import { CoversEditor } from './CoversEditor.js';
import { frontendRendered } from '../frontendRendered';
import { Tooltip } from '@wordpress/components';
import { coversV1 } from './deprecated/coversV1';
import { COVER_TYPES } from './Covers';
import { COVER_LAYOUTS } from './Covers';

const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/covers';
const VERSION = 2;

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

const registerCoversBlock = () => {
  const { registerBlockType } = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: 'Covers',
    icon: 'slides',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes: {
      cover_type: {
        type: 'string',
        default: 'content',
      },
      initialRowsLimit: {
        type: 'integer',
        default: 1,
      },
      title: {
        type: 'string',
        default: '',
      },
      description: {
        type: 'string',
        default: '',
      },
      tags: {
        type: 'array',
        default: []
      },
      post_types: {
        type: 'array',
        default: []
      },
      posts: {
        type: 'array',
        default: []
      },
      version: {
        type: 'integer',
        default: VERSION,
      },
      layout: {
        type: 'string',
        default: COVER_LAYOUTS.carousel,
      },
    },
    edit: CoversEditor,
    save: frontendRendered(BLOCK_NAME),
    // Add our custom styles
    styles: [
      {
        name: COVER_TYPES.content,
        label: getStyleLabel(
          __('Content covers', 'planet4-blocks-backend'),
          __('Content covers pull the image from the post', 'planet4-blocks-backend'),
        ),
        isDefault: true
      },
      {
        name: COVER_TYPES.takeAction,
        label: getStyleLabel(
          __('Take Action covers', 'planet4-blocks-backend'),
          __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button', 'planet4-blocks-backend'),
        ),
      },
      {
        name: COVER_TYPES.campaign,
        label: getStyleLabel(
          __('Campaign covers', 'planet4-blocks-backend'),
          __('Campaign covers pull the associated image and hashtag from the system tag definitions', 'planet4-blocks-backend'),
        ),
      }
    ],
    deprecated: [
      coversV1,
    ]
  });
}

registerCoversBlock();
