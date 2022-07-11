import { CoversEditor } from './CoversEditor.js';
import { frontendRendered } from '../frontendRendered';
import { coversV1 } from './deprecated/coversV1';
import { coversV2 } from './deprecated/coversV2';
import { COVERS_TYPES, COVERS_LAYOUTS } from './CoversConstants';
import { example } from './example.js';
import { getStyleLabel } from '../../functions/getStyleLabel';

const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/covers';
const VERSION = 2;

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
        default: COVERS_LAYOUTS.grid,
      },
      isExample: {
        type: 'boolean',
        default: false,
      },
      exampleCovers: { // Used for the block's preview, which can't extract items from anything.
        type: 'object',
      },
      readMoreText: {
        type: 'string',
        default: __('Load more', 'planet4-blocks')
      },
    },
    edit: CoversEditor,
    save: frontendRendered(BLOCK_NAME),
    // Add our custom styles
    styles: [
      {
        name: COVERS_TYPES.content,
        label: getStyleLabel(
          __('Content covers', 'planet4-blocks-backend'),
          __('Content covers pull the image from the post', 'planet4-blocks-backend'),
        ),
        isDefault: true
      },
      {
        name: COVERS_TYPES.takeAction,
        label: getStyleLabel(
          __('Take Action covers', 'planet4-blocks-backend'),
          __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button', 'planet4-blocks-backend'),
        ),
      },
      {
        name: COVERS_TYPES.campaign,
        label: getStyleLabel(
          __('Campaign covers', 'planet4-blocks-backend'),
          __('Campaign covers pull the associated image and hashtag from the system tag definitions', 'planet4-blocks-backend'),
        ),
      }
    ],
    deprecated: [
      coversV1,
      coversV2,
    ],
    example,
  });
}

registerCoversBlock();
