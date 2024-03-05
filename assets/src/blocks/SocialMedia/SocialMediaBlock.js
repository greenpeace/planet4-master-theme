import {SocialMediaEditor} from './SocialMediaEditorScript.js';
import {socialMediaV1} from './deprecated/socialMediaV1';
import {OEMBED_EMBED_TYPE, FACEBOOK_PAGE_TAB_TIMELINE} from './SocialMediaConstants.js';
import {SocialMediaFrontend} from './SocialMediaFrontend.js';

const {registerBlockType} = wp.blocks;

const BLOCK_NAME = 'planet4-blocks/social-media';

export const registerSocialMediaBlock = () => registerBlockType(BLOCK_NAME, {
  title: 'Social Media',
  icon: 'share',
  category: 'planet4-blocks',
  attributes: {
    title: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    embed_type: {
      type: 'string',
      default: OEMBED_EMBED_TYPE,
    },
    facebook_page_tab: {
      type: 'string',
      default: FACEBOOK_PAGE_TAB_TIMELINE,
    },
    social_media_url: {
      type: 'string',
      default: '',
    },
    alignment_class: {
      type: 'string',
      default: '',
    },
    embed_code: {
      type: 'string',
      default: '',
    },
  },
  edit: SocialMediaEditor,
  save: ({attributes}) => <SocialMediaFrontend {...attributes} />,
  deprecated: [
    socialMediaV1,
  ],
});
