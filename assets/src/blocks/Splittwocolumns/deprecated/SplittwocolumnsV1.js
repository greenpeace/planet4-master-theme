export const splitTwoColumnsV1 = {
  attributes: {
    select_issue: {
      type: 'number',
      default: 0,
    },
    title: {
      type: 'string',
    },
    issue_description: {
      type: 'string',
    },
    issue_link_text: {
      type: 'string',
    },
    issue_link_path: {
      type: 'string',
    },
    issue_image: {
      type: 'number',
    },
    focus_issue_image: {
      type: 'string',
    },
    select_tag: {
      type: 'number',
      default: 0,
    },
    tag_description: {
      type: 'string',
    },
    button_text: {
      type: 'string',
    },
    button_link: {
      type: 'string',
    },
    tag_image: {
      type: 'number',
    },
    focus_tag_image: {
      type: 'string',
    },
  },
  isEligible(attributes) {
    return attributes.issue_image || attributes.tag_image;
  },
  migrate(attributes) {
    attributes.version = 1;
    attributes.issue_image_id = attributes.issue_image;
    attributes.tag_image_id = attributes.tag_image;

    delete attributes.issue_image;
    delete attributes.tag_image;

    return attributes;
  },
  save() {
    return null;
  },
};
