import {registerPostsListBlock} from './PostsList';
import {registerActionsList} from './ActionsList';
import {registerSubmenuBlock} from './Submenu/SubmenuBlock';

wp.domReady(() => {
  // Make sure to unregister the posts-list native variation before registering planet4-blocks/posts-list-block
  wp.blocks.unregisterBlockVariation('core/query', 'posts-list');

  // Blocks
  registerSubmenuBlock();

  // Beta blocks
  registerActionsList();
  registerPostsListBlock();
});
