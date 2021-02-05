export const coversV1 = {
  attributes: {
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
    },
  },
  save: () => null
};
