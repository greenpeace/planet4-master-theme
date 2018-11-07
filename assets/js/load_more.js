var $ = jQuery;
const loadMore = $('button.load-more');
loadMore.on('click', (e) => {
  e.preventDefault();

  const nextPage = parseInt(loadMore[0].dataset.page) + 1;
  const totalPages = loadMore[0].dataset.total;
  const url = loadMore[0].dataset.url + `?page=${ nextPage }`;
  loadMore[0].dataset.page = nextPage;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'html'
  }).done(function ( response ) {
    // Append the response at the bottom of the results and then show it.
    $( '.multiple-search-result .list-unstyled' ).append( response );
  }).fail(function ( jqXHR, textStatus, errorThrown ) {
    console.log(errorThrown); //eslint-disable-line no-console
  });

  if (nextPage == totalPages) {
    loadMore.fadeOut();
  }
});
