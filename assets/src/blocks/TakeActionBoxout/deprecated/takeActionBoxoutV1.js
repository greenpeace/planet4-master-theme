export const takeActionBoxoutV1 = {
  attributes: {
    take_action_page: {
      type: 'number',
    },
    custom_title: {
      type: 'string',
    },
    custom_excerpt: {
      type: 'string',
    },
    custom_link: {
      type: 'string',
    },
    custom_link_text: {
      type: 'string',
    },
    custom_link_new_tab: {
      type: 'boolean',
      default: false
    },
    tag_ids: {
      type: 'array',
      default: []
    },
    background_image: {
      type: 'number',
      default: ''
    },
  },
  isEligible({ custom_excerpt, custom_link, custom_link_text, custom_link_new_tab, custom_title, background_image }) {
    return custom_link || custom_excerpt || custom_link_new_tab || custom_link_text || custom_title || background_image;
  },
  migrate({ custom_excerpt, custom_link, custom_link_text, custom_link_new_tab, custom_title, background_image, ...attributes }) {
    attributes.title = custom_title;
    attributes.link = custom_link;
    attributes.linkText = custom_link_text;
    attributes.newTab = custom_link_new_tab;
    attributes.excerpt = custom_excerpt;
    attributes.imageId = background_image;

    return attributes;
  },
  save() {
    return null;
  }
};
