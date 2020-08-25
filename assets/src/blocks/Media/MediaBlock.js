import { MediaEditor } from './MediaEditor';
import { MediaFrontend } from './MediaFrontend';

const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/media-video';

const attributes = {
  video_title: {
    type: 'string'
  },
  description: {
    type: 'string'
  },
  video_poster_img: {
    type: 'integer'
  },
};

export const registerMediaBlock = () => {
  const {registerBlockType} = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: __('Media block', 'planet4-blocks-backend'),
    icon: 'format-video',
    category: 'planet4-blocks',
    attributes: {
      ...attributes,
      embed_html: {
        type: 'string',
        default: ''
      },
      media_url: {
        type: 'string'
      },
      poster_url: {
        type: 'string',
        default: ''
      },
    },
    save: ({ attributes }) => {
      return <MediaFrontend { ...attributes } />
    },
    edit: MediaEditor,
    deprecated: [{
      attributes: {
        ...attributes,
        youtube_id: {
          type: 'string',
          default: ''
        },
      },
      migrate( { youtube_id, ...attributes } ) {
        return {
          ...attributes,
          media_url: youtube_id
        };
      },
      save: () => {
        return null
      }
    }],
  });
};
