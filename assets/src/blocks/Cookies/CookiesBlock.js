import { CookiesEditor } from './CookiesEditor.js';

const BLOCK_NAME = 'planet4-blocks/cookies';

export class CookiesBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;

    registerBlockType(BLOCK_NAME, {
      title: 'Cookies',
      icon: 'welcome-view-site',
      category: 'planet4-blocks',
      supports: {
        multiple: false, // Use the block just once per post.
      },
      attributes: {
        title: {
          type: 'string',
          default: ''
        },
        description: {
          type: 'string',
          default: ''
        },
        necessary_cookies_name: {
          type: 'string',
          default: ''
        },
        necessary_cookies_description: {
          type: 'string',
          default: ''
        },
        all_cookies_name: {
          type: 'string',
          default: ''
        },
        all_cookies_description: {
          type: 'string',
          default: ''
        },
      },
      edit: CookiesEditor,
      save() {
        return null;
      },
    });
  }
}
