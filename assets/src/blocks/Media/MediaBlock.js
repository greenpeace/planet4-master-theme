import { MediaEditor } from './MediaEditor';
import { MediaFrontend } from './MediaFrontend';
import { mediaV1 } from './deprecated/mediaV1';

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
};

export const registerMediaBlock = () => {
  const {registerBlockType} = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: __('Media block', 'planet4-blocks-backend'),
    icon: 'format-video',
    category: 'planet4-blocks',
    attributes,
    save: MediaFrontend,
    edit: MediaEditor,
    mediaV1,
  });
};
