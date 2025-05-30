import 'bootstrap';

import {setupCookies} from './cookies';
import {setupHeader} from './header';
import {setupLoadMore} from './load_more';
import {setupPDFIcon} from './pdf_icon';
import {setupSearch} from './search';
import {setupExternalLinks} from './external_links';
import {setupQueryLoopCarousel} from './query_loop_carousel';
import {setupClickabelActionsListCards} from './actions_list_clickable_cards';
import {removeNoPostText} from './query-no-posts';
import {removeRelatedPostsSection} from './remove_related_section_no_posts';
import {setupCountrySelector} from './country_selector';
import {setupTransparentNavHomepage} from './homepage_transparent_nav';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../images/icons/', true, /\.svg$/));

document.addEventListener('DOMContentLoaded', () => {
  setupCookies();
  setupHeader();
  setupLoadMore();
  setupPDFIcon();
  setupSearch();
  setupExternalLinks();
  setupQueryLoopCarousel();
  removeNoPostText();
  removeRelatedPostsSection();
  setupClickabelActionsListCards();
  setupCountrySelector();
  setupTransparentNavHomepage();
});
