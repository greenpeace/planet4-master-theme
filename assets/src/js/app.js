import { setupCookies } from './cookies';
import { setupCountrySelect } from './country_select';
import { setupHeader } from './header';
import { setupLoadMore } from './load_more';
import { setupPDFIcon } from './pdf_icon';
import { setupSearch } from './search';
import { setupExternalLinks } from './external_links';

import 'bootstrap';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../images/icons/', true, /\.svg$/));

window.$ = $ || jQuery;

jQuery(($) => {
  setupCookies();
  setupCountrySelect($);
  setupHeader();
  setupLoadMore($);
  setupPDFIcon($);
  setupSearch();
  setupExternalLinks($);
});
