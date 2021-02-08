import { setupCookies } from './cookies';
import { setupCountrySelect } from './country_select';
import { setImageTitlesFromAltText } from './global';
import { setupHeader } from './header';
import { setupLoadMore } from './load_more';
import { setupPDFIcon } from './pdf_icon';
import { setupSearch } from './search';
import { setupExternalLinks } from './external_links';
import { setupCSSVarsPonyfill } from './cssvarsponyfill';
import { setupEnhancedDonateButton } from './enhancedDonateButton';

import 'bootstrap';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../scss/styleguide/src/icons/', true, /\.svg$/));

window.$ = $ || jQuery;

jQuery(function($) {
  setupCookies($);
  setupCountrySelect($);
  setImageTitlesFromAltText($);
  setupHeader($);
  setupLoadMore($);
  setupPDFIcon($);
  setupSearch($);
  setupExternalLinks($);
  setupCSSVarsPonyfill();
  setupEnhancedDonateButton();
});
