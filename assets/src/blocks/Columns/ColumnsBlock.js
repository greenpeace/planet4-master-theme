import { ColumnsEditor } from './ColumnsEditor.js';
import { Tooltip } from '@wordpress/components';
import { LAYOUT_NO_IMAGE } from './ColumnConstants.js';

const { __ } = wp.i18n;

const getStyleLabel = (label, help) => {
  if (help) {
    return (
      <Tooltip text={help}>
        <span>{label}</span>
      </Tooltip>
    );
  }
  return label;
};

export class ColumnsBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;

    registerBlockType('planet4-blocks/columns', {
      title: __('Columns', 'planet4-blocks-backend'),
      icon: 'grid-view',
      category: 'planet4-blocks',
      attributes: {
        columns_block_style: {
          type: 'string',
          default: LAYOUT_NO_IMAGE,
        },
        columns_title: {
          type: 'string'
        },
        columns_description: {
          type: 'string'
        },
        columns: {
          type: 'array',
          default: [],
          title: {
            type: 'string'
          },
          description: {
            type: 'string'
          },
          attachment: {
            type: 'integer',
            default: 0
          },
          cta_link: {
            type: 'string'
          },
          link_new_tab: {
            type: 'boolean'
          },
          cta_text: {
            type: 'string'
          },
        }
      },
      edit: ColumnsEditor,
      save() {
        return null;
      },
      styles: [
        {
          name: 'no_image',
          label: getStyleLabel(
            __('No Image', 'planet4-blocks-backend'),
            __('Optional headers, description text and buttons in a column display.', 'planet4-blocks-backend')
          ),
          isDefault: true,
        },
        {
          name: 'tasks',
          label: getStyleLabel(
            __('Tasks', 'planet4-blocks-backend'),
            __('Used on Take Action pages, this display has ordered tasks, and call to action buttons.', 'planet4-blocks-backend')
          ),
        },
        {
          name: 'icons',
          label: getStyleLabel(
            __('Icons', 'planet4-blocks-backend'),
            __('For more static content, this display has an icon, header, description and text link.', 'planet4-blocks-backend')
          ),
        },
        {
          name: 'image',
          label: getStyleLabel(
            __('Images', 'planet4-blocks-backend'),
            __('For more static content, this display has an image, header, description and text link.', 'planet4-blocks-backend')
          ),
        },
      ],
    });
  }
}
