import {registerActionsListBlock} from './blocks/ActionsList';
import {registerPostsListBlock} from './blocks/PostsList';
import {registerTableOfContentsBlock} from './blocks/TableOfContents/TableOfContentsBlock';
import {registerTakeActionBoxoutBlock} from './blocks/TakeActionBoxout/TakeActionBoxoutBlock';
import {setupCustomSidebar} from './block-editor/setupCustomSidebar';
import {setupQueryLoopBlockExtension} from './block-editor/QueryLoopBlockExtension';
import {registerSocialMediaBlock} from './blocks/SocialMedia/SocialMediaBlock';
import {registerBlockTemplates} from './block-templates/register';
import {registerTimelineBlock} from './blocks/Timeline/TimelineBlock';
import {registerColumnsBlock} from './blocks/Columns/ColumnsBlock';
import {registerBlockStyles} from './block-styles';
import {registerBlockVariations} from './block-variations';
import {registerActionButtonTextBlock} from './blocks/ActionCustomButtonText';
import {setupBlockEditorValidations} from './block-editor/setupBlockEditorValidations';
import {addButtonLinkPasteWarning} from './block-editor/addButtonLinkPasteWarning';
import {addBlockFilters} from './block-editor/BlockFilters';
import {replaceTaxonomyTermSelectors} from './block-editor/replaceTaxonomyTermSelectors';
import {setupImageBlockExtension} from './block-editor/setupImageBlockExtension';
import {setupDetailsBlockExtension} from './block-editor/setupDetailsBlockExtension';
import {setupTaxonomyBreadcrumbBlock} from './block-editor/setupTaxonomyBreadcrumbBlock';
import {registerSecondaryNavigationBlock} from './blocks/SecondaryNavigation/SecondaryNavigationBlock';

wp.domReady(() => {
  // Blocks
  registerTableOfContentsBlock();
  registerColumnsBlock();
  registerTakeActionBoxoutBlock();
  registerSocialMediaBlock();
  registerTimelineBlock();
  registerPostsListBlock();
  registerActionButtonTextBlock();
  registerActionsListBlock();
  registerSecondaryNavigationBlock();

  // Block Templates
  registerBlockTemplates();

  // Custom block styles
  registerBlockStyles();

  // Block variations
  registerBlockVariations();
});

// Editor behaviour(It should be executed after the DOM is ready).
setupQueryLoopBlockExtension();
setupCustomSidebar();
addButtonLinkPasteWarning();
addBlockFilters();
replaceTaxonomyTermSelectors();
setupImageBlockExtension();
setupDetailsBlockExtension();
setupBlockEditorValidations();
setupTaxonomyBreadcrumbBlock();
