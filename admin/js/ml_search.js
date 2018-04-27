/* global localizations */

jQuery(document).ready(function () {

    $ = jQuery; //eslint-disable-line no-global-assign

    $('.ml-search').on('keyup', function() {
        if (this.value.length > 3) {
            var reset_page = 1;
            $(this).data( 'current_page', reset_page );

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action:          'get_paged_medias',
                    'search-action': 'get_searched_medias',
                    'paged':         reset_page,
                    'query-string':  $( this ).val()
                },
                dataType: 'html'
            }).done(function ( response ) {
                // Show the search query response.
                $( '.ml-media-list' ).html( response );
            }).fail(function ( jqXHR, textStatus, errorThrown ) {
                console.log(errorThrown); //eslint-disable-line no-console
            });

        }
    });
});
