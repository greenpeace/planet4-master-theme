import { ShareButtonsEditor } from './ShareButtonsEditor';
import { ShareButtonsFrontend } from './ShareButtonsFrontend';

const { registerBlockType } = wp.blocks;
const { __ } = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/share-buttons';

export const registerBlock = () => {
  return registerBlockType(BLOCK_NAME, {
    title: 'Share Buttons (beta)',
    icon: 'feedback',
    category: 'planet4-blocks',
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
      facebook: {
        type: 'object',
        default: {
          showInMenu: true,
        },
      },
      twitter: {
        type: 'object',
        default: {
          showInMenu: true,
          text: '',
          description: '',
          account: '',
        },
      },
      whatsapp: {
        type: 'object',
        default: {
          showInMenu: true,
        },
      },
      email: {
        type: 'object',
        default: {
          title: '',
          body: '',
          showInMenu: true,
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
