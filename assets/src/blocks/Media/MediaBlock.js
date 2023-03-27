import {MediaEditor} from './MediaEditor';
import {MediaFrontend} from './MediaFrontend';
import {mediaV1} from './deprecated/mediaV1';

const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/media-video';

// Old content didn't store the poster url and embed html in the props. For the frontend this is caught in the block's
// backend render method, where we setup a div to be frontend rendered by the same MediaFrontend component as is used for
// save.
export const lacksAttributes = attributes => {
  const lacksEmbedHtml = attributes.media_url && !attributes.media_url.endsWith('.mp4') && !attributes.embed_html;
  const lacksPosterUrl = attributes.video_poster_img && !attributes.poster_url;

  return lacksEmbedHtml || lacksPosterUrl;
};

export const registerMediaBlock = () => {
  const {registerBlockType} = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: __('Media block', 'planet4-blocks-backend'),
    icon: 'format-video',
    category: 'planet4-blocks',
    attributes: {
      video_title: {
        type: 'string',
      },
      description: {
        type: 'string',
      },
      video_poster_img: {
        type: 'integer',
      },
      embed_html: {
        type: 'string',
        default: '',
      },
      media_url: {
        type: 'string',
        validation: media_url => {
          const isValid = media_url ? 1 : 0;
          const messages = media_url ? [] : [__('The Media Block video URL could not be empty.', 'planet4-blocks-backend')];

          return {isValid, messages};
        },
      },
      poster_url: {
        type: 'string',
        default: '',
      },
    },
    save: ({attributes}) => {
      if (lacksAttributes(attributes)) {
        return null;
      }

      return <MediaFrontend {...attributes} />;
    },
    edit: MediaEditor,
    deprecated: [
      mediaV1,
    ],
  });
};
