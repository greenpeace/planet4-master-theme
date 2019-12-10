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

//Filters
import { addBlockFilters } from './BlockFilters';
import { setupImageBlockExtension } from './ImageBlockExtension';
import { replaceTaxonomyTermSelectors } from "./replaceTaxonomyTermSelectors"
import { addSubAndSuperscript } from './RichTextEnhancements';

const articlesBlock = new ArticlesBlock();
const carouselHeaderBlock = new CarouselHeaderBlock();
const columnsBlock = new ColumnsBlock();
const cookiesBlock = new CookiesBlock();
const counterBlock = new CounterBlock();
const coversBlock = new CoversBlock();
const galleryBlock = new GalleryBlock();
const happypointBlock = new HappypointBlock();
const mediaBlock = new MediaBlock();
const socialmediaBlock = new SocialmediaBlock();
const socialMediaCardsBlock = new SocialMediaCardsBlock();
const splittwocolumnsBlock = new SplittwocolumnsBlock();
const submenuBlock = new SubmenuBlock();
const takeActionBoxoutBlock = new TakeactionboxoutBlock();
const timelineBlock = new TimelineBlock();

addBlockFilters();
addSubAndSuperscript(window.wp);
setupImageBlockExtension();
replaceTaxonomyTermSelectors()
