import {frontendRendered} from '../frontendRendered';
const {registerBlockType} = wp.blocks;
const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/guestbook';

export const registerGuestBookBlock = () =>
  registerBlockType(BLOCK_NAME, {
    title: '50 Years GuestBook',
    icon: 'admin-site-alt2',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
      multiple: false, // Use the block just once per post.
    },
    edit: () => (
      <p className="EmptyMessage">
        {__('This block only renders in the frontend', 'planet4-blocks-backend')}
      </p>
    ),
    save: frontendRendered(BLOCK_NAME),
  });
