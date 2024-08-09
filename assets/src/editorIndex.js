import {registerActionsListBlock} from './blocks/ActionsList';
import {registerPostsListBlock} from './blocks/PostsList';
import {registerSubmenuBlock} from './blocks/Submenu/SubmenuBlock';
import {registerTakeActionBoxoutBlock} from './blocks/TakeActionBoxout/TakeActionBoxoutBlock';
import {registerHappypointBlock} from './blocks/Happypoint/HappypointBlock';
import {setupCustomSidebar} from './block-editor/setupCustomSidebar';
import {setupQueryLoopBlockExtension} from './block-editor/QueryLoopBlockExtension';
import {registerSocialMediaBlock} from './blocks/SocialMedia/SocialMediaBlock';
import {registerMediaBlock} from './blocks/Media/MediaBlock';
import {registerBlockTemplates} from './block-templates/register';
import {registerTimelineBlock} from './blocks/Timeline/TimelineBlock';
import {registerColumnsBlock} from './blocks/Columns/ColumnsBlock';
import {registerBlockStyles} from './block-styles';
import {registerBlockVariations} from './block-variations';
import {registerActionButtonTextBlock} from './blocks/ActionCustomButtonText';

wp.domReady(() => {
  // Make sure to unregister the posts-list native variation before registering planet4-blocks/posts-list
  wp.blocks.unregisterBlockVariation('core/query', 'posts-list');

  // Blocks
  registerSubmenuBlock();
  registerColumnsBlock();
  registerTakeActionBoxoutBlock();
  registerHappypointBlock();
  registerSocialMediaBlock();
  registerMediaBlock();
  registerTimelineBlock();

  // Block Templates
  registerBlockTemplates();

  // Beta blocks
  if (window.p4_vars.features.beta_blocks === 'on') {
    registerActionButtonTextBlock();
    registerActionsListBlock();
    registerPostsListBlock();
  }

  // Custom block styles
  registerBlockStyles();

  // Block variations
  registerBlockVariations();
});

setupCustomSidebar();

// Setup new attributes to the core/query.
// It should be executed after the DOM is ready
setupQueryLoopBlockExtension();
