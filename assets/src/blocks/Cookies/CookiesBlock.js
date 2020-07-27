import {CookiesEditor} from './CookiesEditor.js';
import { frontendRendered } from '../frontendRendered';

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
        },
        description: {
          type: 'string',
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
      },
      edit: CookiesEditor,
      save: frontendRendered( BLOCK_NAME ),
      deprecated: [
        {
          attributes: {
            title: {
              type: 'string',
            },
            description: {
              type: 'string',
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
          },
          save() {
            return null;
          },
        }
      ]
    });
  }
}
