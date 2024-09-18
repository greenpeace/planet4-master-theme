import {HappyPointEditor} from './HappyPointEditor.js';
import {HappyPointBlock as HappyPointBlockV1} from './deprecated/HappyPointBlockV1.js';

const {registerBlockType, getBlockType} = wp.blocks;
const {__} = wp.i18n;

export const registerHappyPointBlock = () => {
  if (!getBlockType('planet4-blocks/happypoint')) {
    registerBlockType('planet4-blocks/happypoint', {
      title: 'Happy Point',
      description: __('The Happy Point block embeds (via iFrame) a “Subscribe” or engagement form on top of a full-width background image.', 'planet4-blocks-backend'),
      icon: 'format-image',
      category: 'planet4-blocks',
      supports: {
        multiple: false, // Use the block just once per post.
        html: false, // Disable "Edit as HTMl" block option.
      },
      attributes: {
        focus_image: {
          type: 'string',
        },
        opacity: {
          type: 'number',
          default: 30,
        },
        mailing_list_iframe: {
          type: 'boolean',
        },
        iframe_url: {
          type: 'string',
        },
        id: {
          type: 'number',
        },
        load_iframe: {
          type: 'boolean',
        },
        use_embed_code: {
          type: 'boolean',
        },
        embed_code: {
          type: 'string',
        },
        override_default_content: {
          type: 'boolean',
          default: false,
        },
        local_content_provider: {
          type: 'string',
          default: 'none',
        },
      },
      edit: HappyPointEditor,
      save() {
        return null;
      },
      deprecated: [
        HappyPointBlockV1,
      ],
    });
  }
};
