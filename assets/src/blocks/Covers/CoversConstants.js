export const COVERS_TYPES = {
  takeAction: 'take-action',
  campaign: 'campaign',
  content: 'content',
};

export const COVERS_LAYOUTS = {
  carousel: 'carousel',
  grid: 'grid',
};

export const CAROUSEL_LAYOUT_COVERS_LIMIT = 12;

export const BLOCK_NAME = 'planet4-blocks/covers';

export const COMMON_ATTRIBUTES = {
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
};

export const ATTRIBUTES = {
  ...COMMON_ATTRIBUTES,
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
};
