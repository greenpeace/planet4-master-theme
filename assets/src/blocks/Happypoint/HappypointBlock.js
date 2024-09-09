import {HappypointEditor} from './HappypointEditor';
import {HappypointBlock as HappypointBlockV1} from './deprecated/HappypointBlocklV1.js';

const {registerBlockType, getBlockType} = wp.blocks;

export const registerHappypointBlock = () => {
  if (!getBlockType('planet4-blocks/happypoint')) {
    registerBlockType('planet4-blocks/happypoint', {
      title: 'Happypoint',
      description: 'The happy point block embeds (via iFrame) a “Subscribe” or engagement form on top of a full-width background image.',
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
      edit: HappypointEditor,
      save() {
        return null;
      },
      deprecated: [
        HappypointBlockV1,
      ],
    });
  }
};
