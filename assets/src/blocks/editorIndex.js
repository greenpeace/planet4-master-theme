import {registerPostsListBlock} from './PostsList';
import {registerActionsList} from './ActionsList';

wp.domReady(() => {
  // Make sure to unregister the posts-list native variation before registering planet4-blocks/posts-list-block
  wp.blocks.unregisterBlockVariation('core/query', 'posts-list');

  // Beta blocks
  registerActionsList();
  registerPostsListBlock();
});
