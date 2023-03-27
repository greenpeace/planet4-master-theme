export const mediaV1 = {
  attributes: {
    video_title: {
      type: 'string',
    },
    description: {
      type: 'string',
    },
    video_poster_img: {
      type: 'integer',
    },
    youtube_id: {
      type: 'string',
      default: '',
    },
  },
  isEligible({youtube_id}) {
    return !!youtube_id;
  },
  migrate({youtube_id, ...attributes}) {
    return {
      ...attributes,
      media_url: youtube_id,
    };
  },
  save: () => null,
};
