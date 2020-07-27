import {CookiesEditor} from './CookiesEditor.js';
import { frontendRendered } from '../frontendRendered';

const BLOCK_NAME = 'planet4-blocks/cookies';

export class CookiesBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;
    const attributes = {
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
    };

    registerBlockType(BLOCK_NAME, {
      title: 'Cookies',
      icon: 'welcome-view-site',
      category: 'planet4-blocks',
      supports: {
        multiple: false, // Use the block just once per post.
      },
      attributes,
      deprecated: [
        {
          attributes,
          save() {
            return null;
          },
        }
      ],
      edit: ({ isSelected, attributes, setAttributes }) => {
        return <CookiesEditor
          attributes={ attributes }
          setAttributes={ setAttributes }
          isSelected={ isSelected }
        />
      },
      save: frontendRendered( BLOCK_NAME )
    });
  };
}
