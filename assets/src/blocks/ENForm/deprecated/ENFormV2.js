import {BLOCK_NAME} from '../ENFormBlock';
import {frontendRendered} from '../../../functions/frontendRendered';

const {__} = wp.i18n;

export const ENFormV2 = {
  attributes: {
    en_page_id: {type: 'integer'},
    enform_goal: {type: 'string'},
    en_form_style: {type: 'string', default: 'side-style'},
    title: {type: 'string'},
    description: {type: 'string'},
    campaign_logo: {type: 'boolean'},
    content_title: {type: 'string'},
    content_title_size: {type: 'string', default: 'h1'},
    content_description: {type: 'string'},
    button_text: {type: 'string'},
    text_below_button: {type: 'string'},
    thankyou_title: {type: 'string'},
    thankyou_subtitle: {type: 'string'},
    thankyou_donate_message: {type: 'string'},
    thankyou_social_media_message: {type: 'string'},
    donate_button_checkbox: {type: 'boolean'},
    donate_text: {type: 'string', default: __('Donate', 'planet4-engagingnetworks')},
    thankyou_url: {type: 'string'},
    custom_donate_url: {type: 'string'},
    background: {type: 'integer'},
    background_image_src: {type: 'string', default: ''},
    background_image_srcset: {type: 'string'},
    background_image_sizes: {type: 'string'},
    background_image_focus: {type: 'string', default: '50% 50%'},
    en_form_id: {type: 'integer'},
    en_form_fields: {type: 'array', default: []},
    social: {type: 'object', default: {}},
    social_accounts: {type: 'object', default: {}},
  },
  save: props => {
    // Sort attributes in a predictable order
    const ordered_attrs = Object.fromEntries(Object.entries(props.attributes).sort());

    return frontendRendered(BLOCK_NAME)(ordered_attrs, props?.className);
  },
};
