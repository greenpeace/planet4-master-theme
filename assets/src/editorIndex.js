import { ArticlesBlock } from './blocks/Articles/ArticlesBlock';
import { CarouselHeaderBlock } from './blocks/Carouselheader/CarouselHeaderBlock';
import { ColumnsBlock } from './blocks/Columns/ColumnsBlock';
import { CookiesBlock } from './blocks/Cookies/CookiesBlock';
import { CounterBlock } from './blocks/Counter/CounterBlock';
import { CoversBlock } from './blocks/Covers/CoversBlock';
import { GalleryBlock } from './blocks/Gallery/GalleryBlock';
import { HappypointBlock } from './blocks/Happypoint/HappypointBlock';
import { MediaBlock } from './blocks/Media/MediaBlock';
import { SocialmediaBlock } from './blocks/Socialmedia/SocialmediaBlock';
import { SocialMediaCardsBlock } from './blocks/SocialMediaCards/SocialMediaCardsBlock';
import { SplittwocolumnsBlock } from './blocks/Splittwocolumns/SplittwocolumnsBlock';
import { SubmenuBlock } from './blocks/Submenu/SubmenuBlock';
import { TakeactionboxoutBlock } from './blocks/Takeactionboxout/TakeactionboxoutBlock';
import { TimelineBlock } from './blocks/Timeline/TimelineBlock';
import { addBlockFilters } from './BlockFilters';
import { setupImageBlockExtension } from './ImageBlockExtension';
import { replaceTaxonomyTermSelectors } from "./replaceTaxonomyTermSelectors";
import { addSubAndSuperscript } from './RichTextEnhancements';
import { SpreadsheetBlock } from "./blocks/Spreadsheet/SpreadsheetBlock"
import { addButtonLinkPasteWarning } from './addButtonLinkPasteWarning';
import { setupCustomSidebar } from "./setupCustomSidebar";
import { setUpCssVariables } from './connectCssVariables';
import { SubPagesBlock } from './blocks/SubPages/SubPagesBlock';
import { blockEditorValidation } from './BlockEditorValidation';
import { ENFormBlock } from './blocks/ENForm/ENFormBlock';

blockEditorValidation();
new ArticlesBlock();
new CarouselHeaderBlock();
new ColumnsBlock();
new CookiesBlock();
new CounterBlock();
new CoversBlock();
new GalleryBlock();
new HappypointBlock();
new MediaBlock();
new SocialmediaBlock();
new SocialMediaCardsBlock();
new SplittwocolumnsBlock();
new SpreadsheetBlock()
new SubmenuBlock();
new SubPagesBlock();
new TakeactionboxoutBlock();
new TimelineBlock();
new ENFormBlock()

addBlockFilters();
addSubAndSuperscript( window.wp );
setupImageBlockExtension();
addButtonLinkPasteWarning();
replaceTaxonomyTermSelectors();
setupCustomSidebar();
setUpCssVariables();
blockEditorValidation();
