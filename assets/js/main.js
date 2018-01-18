$ = jQuery;

// convert an element to slider using slick js
function slickify(element) {
    $(element).slick({
        infinite:       false,
        mobileFirst:    true,
        slidesToShow:   2.2,
        slidesToScroll: 1,
        arrows:         false,
        dots:           false,
        responsive: [
            {
                breakpoint: 992,
                settings: { slidesToShow: 4 }
            },
            {
                breakpoint: 768,
                settings: { slidesToShow: 3 }
            },
            {
                breakpoint: 576,
                settings: { slidesToShow: 2 }
            }
        ]
    });
}

$(document).ready(function () {
    // Add click event for load more button in blocks.
    $( '.btn-load-more-click' ).off( 'click' ).on( 'click', function () {
        var $row = $( '.row-hidden', $( this ).closest( '.container' ) );

        if ( 1 === $row.size() ) {
            $( this ).closest( '.load-more-button-div' ).hide('fast');
        }

        var row_id = $row.attr( 'id' );
        if ( row_id !== undefined && row_id.indexOf( 'publications-row' ) !== -1 ) {
            $row.first().removeClass( 'row-hidden' ).show( 'slow', function () {
                slickify( '#' + row_id );
            });
        } else {
            $row.first().show( 'fast' ).removeClass( 'row-hidden' );
        }
    });

    // Add click event for load more button in covers blocks.
    $('.btn-load-more-covers-click').off('click').on('click', function () {
        var $row = $('.limit-visibility', $(this).closest('.container'));

        if ($row.size() > 0) {
            $row.first().removeClass('limit-visibility');
            $(this).closest('.load-more-covers-button-div').remove();
        }
    });
});

