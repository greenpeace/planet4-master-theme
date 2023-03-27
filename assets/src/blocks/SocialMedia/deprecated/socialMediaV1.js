export const socialMediaV1 = {
  attributes: {
    title: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    embed_type: {
      type: 'string',
      default: 'oembed',
    },
    facebook_page_tab: {
      type: 'string',
      default: 'timeline',
    },
    social_media_url: {
      type: 'string',
      default: '',
    },
    alignment_class: {
      type: 'string',
      default: '',
    },
  },
  isEligible({facebook_page_url}) {
    return !!facebook_page_url;
  },
  // eslint-disable-next-line no-unused-vars
  migrate({facebook_page_url, ...attributes}) {
    return attributes;
  },
  save() {
    return null;
  },
};
