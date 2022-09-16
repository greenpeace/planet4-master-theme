export const setupLoadMore = function() {
  const load_more = document.querySelectorAll('button.load-more-mt');

  load_more.forEach(elt => {
    elt.addEventListener( 'mousedown', handler );
    elt.addEventListener( 'touchstart', handler );
  });
};

function handler(e) {
  e.preventDefault();
  const load_more_elt = e.currentTarget;

  const content_elt = document.querySelector( this.dataset.content );
  const next_page = parseInt( this.dataset.page, 10 ) + 1;
  const total_pages = parseInt( this.dataset.total, 10 );
  const url = this.dataset.url + `?page=${ next_page }&page_num=${ next_page }`;
  this.dataset.page = next_page;

  fetch(url)
    .then(response => response.text())
    .then(html => {
      content_elt.innerHTML += html;
      if (next_page === total_pages) {
        load_more_elt.classList.add( 'hide-load-more' );
      }
    })
    .catch(err => {
      console.log(err); //eslint-disable-line no-console
    });
}
