import 'bootstrap';

import {setupCookies} from './cookies';
import {setupHeader} from './header';
import {setupCountrySelector} from './country_selector';

function requireAll(r) {
  r.keys().forEach(r);
}

requireAll(require.context('../images/icons/', true, /\.svg$/));

document.addEventListener('DOMContentLoaded', () => {
  setupCookies();
  setupHeader();
  setupCountrySelector();

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

  if(document.querySelectorAll('.actions-list.is-custom-layout-grid').length) {
    import('./actions_list_load_more').then(({setupActionsListLoadMore}) => {
      setupActionsListLoadMore();
      window.addEventListener('resize', setupActionsListLoadMore);
    });
  }
});
