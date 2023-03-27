const {__} = wp.i18n;

export const ENFormV1 = {
  attributes: {
    en_page_id: {type: 'integer'},
    enform_goal: {type: 'string'},
    en_form_style: {type: 'string'},
    title: {type: 'string'},
    description: {type: 'string'},
    campaign_logo: {type: 'boolean'},
    content_title: {type: 'string'},
    content_title_size: {type: 'string'},
    content_description: {type: 'string'},
    button_text: {type: 'string'},
    text_below_button: {type: 'string'},
    thankyou_title: {type: 'string'},
    thankyou_subtitle: {type: 'string'},
    thankyou_donate_message: {type: 'string'},
    thankyou_social_media_message: {type: 'string'},
    donate_button_checkbox: {type: 'boolean'},
    custom_donate_url: {type: 'string'},
    thankyou_url: {type: 'string'},
    background: {type: 'integer'},
    en_form_id: {type: 'integer'},
  },
  isEligible(attributes) {
    return typeof attributes.social === 'undefined';
  },
  migrate(attributes) {
    return {
      ...attributes,
      background_image_src: '',
      background_image_srcset: null,
      background_image_sizes: null,
      background_image_focus: '50% 50%',
      donate_text: __('Donate', 'planet4-engagingnetworks'),
      en_form_fields: [],
      social: {},
      social_accounts: {},
    };
  },
  save() {
    return null;
  },
};
