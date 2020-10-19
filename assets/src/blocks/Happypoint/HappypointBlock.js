import { HappypointEditor } from './HappypointEditor';

export class HappypointBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;

    registerBlockType('planet4-blocks/happypoint', {
      title: __('Happypoint', 'planet4-blocks-backend'),
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
          default: 30
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
          default: false
        }
      },
      edit: HappypointEditor,
      save() {
        return null;
      }
    });
  }
}

