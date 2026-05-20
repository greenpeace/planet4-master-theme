import 'bootstrap';

import {setupCookies} from './cookies';
import {setupHeader} from './header';
import {setupCountrySelector} from './country_selector';
import {setupActionsListLoadMore} from './actions_list_load_more';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../images/icons/', true, /\.svg$/));

document.addEventListener('DOMContentLoaded', () => {
  setupCookies();
  setupHeader();
  setupCountrySelector();
  setupActionsListLoadMore();
  window.addEventListener('resize', setupActionsListLoadMore);

  if(!!document.querySelector('body.search')) {
    import('./search').then(({setupSearch}) => {
      setupSearch();
    });
  }

  if(document.querySelectorAll('[class*="is-custom-layout-"]').length) {
    import('./query_loop_carousel').then(({setupQueryLoopCarousel}) => {
      setupQueryLoopCarousel();
    });
  }
});
