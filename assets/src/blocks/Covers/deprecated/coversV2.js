import {COVERS_LAYOUTS} from '../CoversConstants';

const {__} = wp.i18n;

export const coversV2 = {
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
      default: [],
    },
    post_types: {
      type: 'array',
      default: [],
    },
    posts: {
      type: 'array',
      default: [],
    },
    version: {
      type: 'integer',
      default: 2,
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
  },
  isEligible({readMoreText}) {
    return !readMoreText;
  },
  migrate({className, ...attributes}) {
    return {
      ...attributes,
      readMoreText: __('Load more', 'planet4-blocks'),
      className,
    };
  },
  save: () => null,
};
