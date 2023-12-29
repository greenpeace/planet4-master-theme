import {registerPostsListBlock} from './PostsList';
import {registerActionsList} from './ActionsList';
import {registerSubmenuBlock} from './Submenu/SubmenuBlock';
<<<<<<< HEAD
import {registerTakeActionBoxoutBlock} from './TakeActionBoxout/TakeActionBoxoutBlock';
=======
import {HappypointBlock} from './Happypoint/HappypointBlock';
>>>>>>> 57c3568f (PLANET-7378: Move the Happypoint block into master-theme)

wp.domReady(() => {
  // Make sure to unregister the posts-list native variation before registering planet4-blocks/posts-list-block
  wp.blocks.unregisterBlockVariation('core/query', 'posts-list');

  // Blocks
  registerSubmenuBlock();
  registerTakeActionBoxoutBlock();

  // Beta blocks
  registerActionsList();
  registerPostsListBlock();
  new HappypointBlock();
});

