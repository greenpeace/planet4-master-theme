import {CookiesEditor} from './CookiesEditor.js';

export const registerCookiesBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {__} = wp.i18n;

  registerBlockType('planet4-blocks/cookies', {
    title: 'Cookies',
    description: __('Display real time signatures being collected worldwide on petitions.', 'planet4-blocks-backend'),
    icon: 'welcome-view-site',
    category: 'planet4-blocks',
    supports: {
      multiple: false, // Use the block just once per post.
    },
    attributes: {
      title: {
        type: 'string',
        default: '',
      },
      description: {
        type: 'string',
        default: '',
      },
      necessary_cookies_name: {
        type: 'string',
      },
      necessary_cookies_description: {
        type: 'string',
      },
      all_cookies_name: {
        type: 'string',
      },
      all_cookies_description: {
        type: 'string',
      },
      analytical_cookies_name: {
        type: 'string',
      },
      analytical_cookies_description: {
        type: 'string',
      },
    },
    edit: CookiesEditor,
    save: () => {
      return null;
    },
  });
};
