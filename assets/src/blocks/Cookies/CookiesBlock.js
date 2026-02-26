import {CookiesEditor} from './CookiesEditor.js';

export const registerCookiesBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {__} = wp.i18n;
  const {useBlockProps} = wp.blockEditor;

  registerBlockType('planet4-blocks/cookies', {
    title: 'Cookies',
    description: __('Displays the cookies settings and control panel within P4 for editors to control their level of compliance regarding data collection.', 'planet4-master-theme-backend'),
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
    edit: props => (
      <div {...useBlockProps()}>
        <CookiesEditor {...props} />
      </div>
    ),
    save: () => {
      return null;
    },
  });
};
