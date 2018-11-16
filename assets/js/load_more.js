const loadMore = $('button.load-more');
loadMore.on('click', function (e) {
  e.preventDefault();

  const nextPage = parseInt(this.dataset.page) + 1;
  const totalPages = this.dataset.total;
  const url = this.dataset.url + `?page=${ nextPage }`;
  this.dataset.page = nextPage;

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
