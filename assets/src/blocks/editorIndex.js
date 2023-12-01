import {registerPostsListBlock} from './PostsList';
import {registerActionsList} from './ActionsList';
import {registerSubmenuBlock} from './Submenu/SubmenuBlock';
import {registerTakeActionBoxoutBlock} from './TakeActionBoxout/TakeActionBoxoutBlock';
import {registerHappypointBlock} from './Happypoint/HappypointBlock';
import {setupQueryBlockExtension} from '../block-editor/QueryBlockExtension';

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

// Setup new attributes to the core/query.
// It should be executed after the DOM is ready
setupQueryBlockExtension();
