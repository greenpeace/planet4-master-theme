$ = jQuery; //eslint-disable-line no-global-assign

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
  // Add click event for load more button in Content Four Column block.
  $( '.btn-load-more-posts-click' ).off( 'click' ).on( 'click', function () {
    var $row = $( '.row-hidden', $( this ).closest( '.container' ) );

    if ( 1 === $row.size() ) {
      $( this ).closest( '.load-more-posts-button-div' ).hide('fast');
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

  $('.covers-block').each( function() {
    var visible_covers = $('.cover-card-column:visible', $(this)).length;
    if ( 0 === visible_covers % 3 ) {
      $(this).attr('data-covers_per_row', 3);
    } else if ( 0 === visible_covers % 2 ) {
      $(this).attr('data-covers_per_row', 2);
    }
  });

  // Add click event for load more button in Covers blocks.
  $('.btn-load-more-covers-click').off('click').on('click', function () {
    var $row = $('.cover-card-column:hidden', $(this).closest('.container'));
    var covers_per_row = $(this).closest('.covers-block').data('covers_per_row');

    $(this).blur();
    if ($row.length > 0) {
      $row.slice( 0, covers_per_row ).show('slow');
    }
    if ( $row.length <= covers_per_row ) {
      $(this).closest('.load-more-covers-button-div').hide('fast');
    }
  });
});
