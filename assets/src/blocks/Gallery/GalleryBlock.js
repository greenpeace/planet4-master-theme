import { GalleryEditor } from './GalleryEditor';
import { Tooltip } from '@wordpress/components';
import { frontendRendered } from '../frontendRendered';

const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/gallery';

const getStyleLabel = (label, help) => {
  if (help) {
    return (
      <Tooltip text={__(help, 'planet4-blocks-backend')}>
        <span>{__(label, 'planet4-blocks-backend')}</span>
      </Tooltip>
    );
  }
  return label;
}

export class GalleryBlock {
  constructor() {
    const { registerBlockType, registerBlockStyle } = wp.blocks;
    const { withSelect } = wp.data;

    const attributes = {
      gallery_block_title: {
        type: 'string',
      },
      gallery_block_description: {
        type: 'string',
      },
      multiple_image: {
        type: 'string',
      },
      gallery_block_focus_points: {
        type: 'string',
      },
      image_data: {
        type: 'array',
        default: []
      },
    };

    registerBlockType(BLOCK_NAME, {
      title: __('Gallery', 'planet4-blocks-backend'),
      icon: 'format-gallery',
      category: 'planet4-blocks',
      attributes,
      edit: GalleryEditor,
      save: frontendRendered(BLOCK_NAME),
      deprecated: [
        {
          attributes: {
            gallery_block_style: {
              type: 'number',
              default: 0
            },
            ...attributes
          },
          save() {
            return null;
          }
        }
      ],
    });

    // Add our custom styles
    registerBlockStyle(
      BLOCK_NAME,
      [
        {
          name: 'slider',
          label: getStyleLabel(
            'Slider',
            'The slider is a carousel of images. For more than 5 images, consider using a grid.'
          ),
          isDefault: true
        },
        {
          name: 'three-columns',
          label: getStyleLabel(
            '3 Column',
            'The 3 column image display is great for accentuating text, and telling a visual story.'
          ),
        },
        {
          name: 'grid',
          label: getStyleLabel(
            'Grid',
            'The grid shows thumbnails of lots of images. Good to use when showing lots of activity.'
          ),
        }
      ]
    );
  };
}
