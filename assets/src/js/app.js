import {setupCookies} from './cookies';
import {setupHeader} from './header';
import {setupLoadMore} from './load_more';
import {setupPDFIcon} from './pdf_icon';
import {setupSearch} from './search';
import {setupExternalLinks} from './external_links';
import {setupListingPages} from './listing_pages';
import {setupCountrySelector} from './country_selector';

import 'bootstrap';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../images/icons/', true, /\.svg$/));

setupCookies();
setupHeader();
setupLoadMore();
setupPDFIcon();
setupSearch();
setupExternalLinks();
setupListingPages();
setupCountrySelector();
