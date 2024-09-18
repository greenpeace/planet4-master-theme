import {USE_IFRAME_URL, USE_NONE} from '../HappyPointConstants';

export const HappyPointBlock = {
  attributes: {
    focus_image: {type: 'string'},
    opacity: {type: 'number', default: 30},
    mailing_list_iframe: {type: 'boolean'},
    iframe_url: {type: 'string'},
    id: {type: 'number'},
    load_iframe: {type: 'boolean', default: false},
    use_embed_code: {type: 'boolean'},
    embed_code: {type: 'string'},
  },
  isEligible(attributes) {
    return typeof attributes.override_default_content === 'undefined';
  },
  migrate(attributes) {
    // Case 1: Load iframe and local url configured -> block with form from local url
    // Case 2: Unchecked mailing_list_iframe -> empty block
    // The case of load mailing_list_iframe + default content url is handled only by default content in next version
    const override_default_content = true;
    const local_content_provider = attributes.mailing_list_iframe ? USE_IFRAME_URL : USE_NONE;

    delete attributes.load_iframe;
    delete attributes.mailing_list_iframe;

    return {
      ...attributes,
      override_default_content,
      local_content_provider,
    };
  },
  save() {
    return null;
  },
};
