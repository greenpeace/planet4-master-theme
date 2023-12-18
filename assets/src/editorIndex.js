import {registerPostsListBlock} from './blocks/PostsList';
import {registerActionsList} from './blocks/ActionsList';
import {registerSubmenuBlock} from './blocks/Submenu/SubmenuBlock';
import {registerTakeActionBoxoutBlock} from './blocks/TakeActionBoxout/TakeActionBoxoutBlock';
import {registerHappypointBlock} from './blocks/Happypoint/HappypointBlock';
import {setupCustomSidebar} from './block-editor/setupCustomSidebar';

wp.domReady(() => {
  // Make sure to unregister the posts-list native variation before registering planet4-blocks/posts-list-block
  wp.blocks.unregisterBlockVariation('core/query', 'posts-list');

  // Blocks
  registerSubmenuBlock();
  registerTakeActionBoxoutBlock();
  registerHappypointBlock();

  // Beta blocks
  registerActionsList();
  registerPostsListBlock();
});

setupCustomSidebar();
