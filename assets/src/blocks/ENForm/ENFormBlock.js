import {ENFormEditor} from './ENFormEditor';
import {ENFormFrontend} from './ENFormFrontend';
import {ENFormV1} from './deprecated/ENFormV1.js';
import {ENFormV2} from './deprecated/ENFormV2.js';

const {__} = wp.i18n;

export const BLOCK_NAME = 'planet4-blocks/enform';

export const registerENForm = () => {
  const {registerBlockType} = wp.blocks;
  const {RawHTML, renderToString} = wp.element;

  registerBlockType(BLOCK_NAME, {
    title: 'EN Form',
    icon: 'feedback',
    category: 'planet4-blocks',
    supports: {
      multiple: false,
    },
    styles: [
      {name: 'full-width-bg', label: 'Full page width with background'},
      {name: 'full-width', label: 'Page body/text size width'},
      {name: 'side-style', label: 'Form on the side', isDefault: true},
    ],
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
    edit: ENFormEditor,
    save: props => {
      // Sort attributes in a predictable order
      const orderedAttributes = Object.fromEntries(Object.entries(props.attributes).sort());

      const markup = renderToString(<div
        data-hydrate={BLOCK_NAME}
        data-attributes={JSON.stringify(orderedAttributes)}
      >
        <ENFormFrontend attributes={orderedAttributes} />
      </div>);
      return <RawHTML>{markup}</RawHTML>;
    },
    deprecated: [
      ENFormV2,
      ENFormV1,
    ],
  });
};
