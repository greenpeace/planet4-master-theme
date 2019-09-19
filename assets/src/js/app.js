// global jQuery

import { setupCookies } from './cookies';
import { setupAuthor } from './author';
import { setupCommentsAnchor } from './comments_anchor';
import { setupCountrySelect } from './country_select';
import { setImageTitlesFromAltText } from './global';
import { setupHeader } from './header';
import { setupImageAlign } from './img_align';
import { setupLoadMore } from './load_more';
import { setupPDFIcon } from './pdf_icon';
import { setupSearch } from './search';
import { setupImageZoomer } from './single';

import 'bootstrap';

window.$ = $ || jQuery;

console.log("Loaded"); // eslint-disable-line

console.log(window.$); // eslint-disable-line

jQuery(function($) {
  setupCookies($);
  setupAuthor($);
  setupCommentsAnchor($);
  setupCountrySelect($);
  setImageTitlesFromAltText($);
  setupHeader($);
  setupImageAlign($);
  setupLoadMore($);
  setupPDFIcon($);
  setupSearch($);
  setupImageZoomer($);
});
