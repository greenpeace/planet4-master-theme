const load_more = $('button.load-more-mt');
load_more.off('mousedown').on('mousedown', function (e) {
  e.preventDefault();

  const $content = $( this.dataset.content );
  const next_page = parseInt(this.dataset.page) + 1;
  const total_pages = parseInt(this.dataset.total);
  const url = this.dataset.url + `?page=${ next_page }`;
  this.dataset.page = next_page;

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

  if (next_page === total_pages) {
    load_more.fadeOut();
  }
});
