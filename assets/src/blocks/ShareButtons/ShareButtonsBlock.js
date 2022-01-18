import { ShareButtonsEditor } from './ShareButtonsEditor';
import { ShareButtonsFrontend } from './ShareButtonsFrontend';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/share-buttons';

export const registerBlock = () => {
  return registerBlockType(BLOCK_NAME, {
    title: 'Share Buttons (beta)',
    icon: 'feedback',
    category: 'planet4-blocks-beta',
    supports: {
      html: false,
    },
    attributes: {
      url: {
        type: 'string',
        default: document.URL,
      },
      openInNewTab: {
        type: 'boolean',
        default: true,
      },
      gaCategory: {
        type: 'string',
      },
      gaAction: {
        type: 'string',
      },
      gaLabel: {
        type: 'string',
        default: 'n/a',
      },
      utmMedium: {
        type: 'string',
      },
      utmContent: {
        type: 'string',
      },
      utmCampaign: {
        type: 'string',
      },
      buttons: {
        type: 'object',
        default: {
          facebook: {
            type: 'facebook',
            iconName: 'facebook-f',
            hiddenText: __( 'Share on Facebook', 'planet4-master-theme' ),
            showInMenu: true,
          },
          twitter: {
            type: 'twitter',
            iconName: 'twitter',
            hiddenText: __( 'Share on Twitter', 'planet4-master-theme' ),
            showInMenu: true,
            text: '',
            description: '',
            account: '',
          },
          whatsapp: {
            type: 'whatsapp',
            iconName: 'whatsapp',
            hiddenText: __( 'Share on Whatsapp', 'planet4-master-theme' ),
            showInMenu: true,
          },
          email: {
            type: 'email',
            iconName: 'envelope',
            hiddenText: __( 'Share via Email', 'planet4-master-theme' ),
            showInMenu: true,
            title: '',
            body: '',
          },
        },
      },
      version: {
        type: 'integer',
        default: 1,
      },
    },
    edit: ShareButtonsEditor,
    save: ({ attributes }) => <ShareButtonsFrontend {...attributes} />,
  })
}
