import {ArticlesBlock} from './blocks/Articles/ArticlesBlock';
import {registerColumnsBlock} from './blocks/Columns/ColumnsBlock';
import {CookiesBlock} from './blocks/Cookies/CookiesBlock';
import {HappypointBlock} from './blocks/Happypoint/HappypointBlock';
import {registerMediaBlock} from './blocks/Media/MediaBlock';
import {registerSocialMediaBlock} from './blocks/SocialMedia/SocialMediaBlock';
import {SocialMediaCardsBlock} from './blocks/SocialMediaCards/SocialMediaCardsBlock';
import {registerSubmenuBlock} from './blocks/Submenu/SubmenuBlock';
import {registerTakeActionBoxoutBlock} from './blocks/TakeActionBoxout/TakeActionBoxoutBlock';
import {registerTimelineBlock} from './blocks/Timeline/TimelineBlock';
import {addBlockFilters} from './BlockFilters';
import {setupImageBlockExtension} from './ImageBlockExtension';
import {replaceTaxonomyTermSelectors} from './replaceTaxonomyTermSelectors';
import {registerSpreadsheetBlock} from './blocks/Spreadsheet/SpreadsheetBlock';
import {addButtonLinkPasteWarning} from './addButtonLinkPasteWarning';
import {setupCustomSidebar} from './setupCustomSidebar';
import {setUpCssVariables} from './connectCssVariables';
import {SubPagesBlock} from './blocks/SubPages/SubPagesBlock';
import {blockEditorValidation} from './BlockEditorValidation';
import {registerBlock as registerShareButtonsBlock} from './blocks/ShareButtons/ShareButtonsBlock';
import {registerPageHeaderBlock} from './blocks/PageHeader/PageHeaderBlock';
import {registerBlockTemplates} from './block-templates/register';

blockEditorValidation();
new ArticlesBlock();
registerColumnsBlock();
new CookiesBlock();
new HappypointBlock();
registerMediaBlock();
registerSocialMediaBlock();
new SocialMediaCardsBlock();
registerSpreadsheetBlock();
registerSubmenuBlock();
new SubPagesBlock();
registerTakeActionBoxoutBlock();
registerTimelineBlock();
registerShareButtonsBlock();
registerPageHeaderBlock();

addBlockFilters();
setupImageBlockExtension();
addButtonLinkPasteWarning();
replaceTaxonomyTermSelectors();
setupCustomSidebar();
setUpCssVariables();
blockEditorValidation();

const {registerBlockVariation} = wp.blocks;
const {__} = wp.i18n;

registerBlockVariation('core/group', {
  name: 'group-stretched-link',
  title: __('Stretched Link', 'planet4-blocks-backend'),
  description: __('Make the entire block contents clickable, using the first link inside.', 'planet4-blocks-backend'),
  attributes: {className: 'group-stretched-link'},
  scope: ['inserter', 'transform'],
  isActive: blockAttributes => {
    return blockAttributes.className === 'group-stretched-link';
  },
  icon: 'admin-links',
});

registerBlockTemplates();
