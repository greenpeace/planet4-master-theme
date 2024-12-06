import {registerActionsListBlock} from './blocks/ActionsList';
import {registerPostsListBlock} from './blocks/PostsList';
import {registerTableOfContentsBlock} from './blocks/TableOfContents/TableOfContentsBlock';
import {registerTakeActionBoxoutBlock} from './blocks/TakeActionBoxout/TakeActionBoxoutBlock';
import {registerHappyPointBlock} from './blocks/HappyPoint/HappyPointBlock';
import {setupCustomSidebar} from './block-editor/setupCustomSidebar';
import {setupQueryLoopBlockExtension} from './block-editor/QueryLoopBlockExtension';
import {registerSocialMediaBlock} from './blocks/SocialMedia/SocialMediaBlock';
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
  registerTableOfContentsBlock();
  registerColumnsBlock();
  registerTakeActionBoxoutBlock();
  registerHappyPointBlock();
  registerSocialMediaBlock();
  registerTimelineBlock();
  registerPostsListBlock();

  // Block Templates
  registerBlockTemplates();

  // Beta blocks
  if (window.p4_vars.features.beta_blocks === 'on') {
    registerActionButtonTextBlock();
    registerActionsListBlock();
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
