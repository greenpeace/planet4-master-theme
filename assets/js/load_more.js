const load_more = $('button.load-more-mt');
load_more.off('click').on('click', function (e) {
  e.preventDefault();

  const $content = $( this.dataset.content );
  const nextPage = parseInt(this.dataset.page) + 1;
  const totalPages = parseInt(this.dataset.total);
  const url = this.dataset.url + `?page=${ nextPage }`;
  this.dataset.page = nextPage;

  $.ajax({
    url: url,
    type: 'GET',
    dataType: 'html'
  }).done(function ( response ) {
    // Append the response at the bottom of the results and then show it.
    $content.append( response );
  }).fail(function ( jqXHR, textStatus, errorThrown ) {
    console.log(errorThrown); //eslint-disable-line no-console
  });

  if (nextPage === totalPages) {
	  load_more.fadeOut();
  }
});
