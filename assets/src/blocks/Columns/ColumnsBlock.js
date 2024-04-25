import {ColumnsEditor} from './ColumnsEditor.js';
import {LAYOUT_NO_IMAGE, LAYOUT_IMAGES, LAYOUT_ICONS, LAYOUT_TASKS} from './ColumnConstants.js';
import {example} from './example';
import {getStyleLabel} from '../../functions/getStyleLabel';

export const registerColumnsBlock = () => {
  const {__} = wp.i18n;
  const {registerBlockType, getBlockTypes} = wp.blocks;

  const BLOCK_NAME = 'planet4-blocks/columns';

  const blockAlreadyExists = getBlockTypes().find(block => block.name === BLOCK_NAME);

  if (blockAlreadyExists) {
    return;
  }

  registerBlockType(BLOCK_NAME, {
    title: 'Planet 4 Columns',
    icon: 'grid-view',
    category: 'planet4-blocks',
    attributes: {
      columns_block_style: {
        type: 'string',
        default: LAYOUT_NO_IMAGE,
      },
      columns_title: {
        type: 'string',
      },
      columns_description: {
        type: 'string',
      },
      columns: {
        type: 'array',
        default: [{}, {}, {}],
        title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        attachment: {
          type: 'integer',
          default: 0,
        },
        cta_link: {
          type: 'string',
        },
        link_new_tab: {
          type: 'boolean',
        },
        cta_text: {
          type: 'string',
        },
      },
      isExample: {
        type: 'boolean',
        default: false,
      },
      exampleColumns: { // Used for the block's preview
        type: 'array',
      },
    },
    edit: ColumnsEditor,
    save() {
      return null;
    },
    styles: [
      {
        name: LAYOUT_NO_IMAGE,
        label: getStyleLabel(
          'No Image',
          __('Optional headers, description text and buttons in a column display.', 'planet4-blocks-backend')
        ),
        isDefault: true,
      },
      {
        name: LAYOUT_TASKS,
        label: getStyleLabel(
          'Tasks',
          __(
            'Used on Take Action pages, this display has ordered tasks, and call to action buttons.',
            'planet4-blocks-backend'
          )
        ),
      },
      {
        name: LAYOUT_ICONS,
        label: getStyleLabel(
          'Icons',
          __(
            'For more static content, this display has an icon, header, description and text link.',
            'planet4-blocks-backend'
          )
        ),
      },
      {
        name: LAYOUT_IMAGES,
        label: getStyleLabel(
          'Images',
          __(
            'For more static content, this display has an image, header, description and text link.',
            'planet4-blocks-backend'
          )
        ),
      },
    ],
    example,
  });
};
