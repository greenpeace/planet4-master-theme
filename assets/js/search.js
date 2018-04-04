/* global localizations */

$ = jQuery; //eslint-disable-line no-global-assign

// Search page.
$(function() {
  var $search_form      = $( '#search_form' );
  var $load_more_button = $( '.btn-load-more-click-scroll' );
  var load_more_count   = 0;
  var loaded_more       = false;

  $( '#search-type button' ).click(function() {
    $( '#search-type button' ).removeClass( 'active' );
    $( this ).addClass( 'active' );
  });

  $( '.btn-filter:not( .disabled )' ).click(function() {
    $( '#filtermodal' ).modal( 'show' );
  });

  // Submit form on Sort change event.
  $( '#select_order' ).off( 'change' ).on( 'change', function() {
    $( '#orderby', $search_form ).val( $( this ).val() ).parent().submit();
    return false;
  });

  // Submit form on Filter click event or on Apply button click event.
  $( 'input[name^="f["]:not(.modal-checkbox), .applybtn' ).off( 'click' ).on( 'click', function() {
    $search_form.submit();
  });

  // Add all selected filters to the form submit.
  $search_form.on( 'submit', function() {
    if ( 0 === $('.filter-modal.show').length ) {
      $( 'input[name^="f["]:not(.modal-checkbox):checked' ).each( function() {
        var $checkbox = $( this ).clone( true );
        $checkbox.css('display', 'none');
        $search_form.append( $checkbox );
      } );
    } else {
      $( 'input[name^="f["].modal-checkbox:checked').each( function() {
        var $checkbox = $( this ).clone( true );
        $checkbox.css('display', 'none');
        $search_form.append( $checkbox );
      } );
    }
  });

  // Perform search on Elasticsearch if user does Right mouse click on the Search buttons.
  $('.top-nav-search-btn, .search-btn').off('mouseup').on('mouseup', function( event ) {
    if ( 3 === event.which ) {
      event.preventDefault();
	  $search_form.append('<input type="hidden" name="es" value="true" />');
      $search_form.submit();
    }
  }).on('contextmenu', function() {
    return false;
  });

  // Add filter by clicking on the page type label inside a result item.
  // Delegate event handler to the dynamically created descendant elements.
  $( '.multiple-search-result' ).off( 'click', '.search-result-item-head' ).on( 'click', '.search-result-item-head', function() {
    $( '.p4-custom-control-input[value=' + $( this ).data( 'term_id' ) + ']' ).prop( 'checked', true);
    $search_form.submit();
  });

  // Navigate to the page of the search result item when clicking on it's thumbnail image.
  // Delegate event handler to the dynamically created descendant elements.
  $( '.multiple-search-result' ).off( 'click', '.search-result-item-image').on( 'click', '.search-result-item-image', function() {
    window.location.href = $( '.search-result-item-headline', $( this ).parent() ).attr( 'href' );
  });

  // Underline headline on thumbnail hover.
  $('.search-result-item-image').hover(
    function() {
      $('.search-result-item-headline', $(this).parent()).addClass('search-hover');
    }, function() {
      $('.search-result-item-headline', $(this).parent()).removeClass('search-hover');
    }
  );

  // Clear single selected filter.
  $( '.activefilter-tag' ).off( 'click' ).on( 'click', function() {
    $( '.p4-custom-control-input[value=' + $( this ).data( 'id' ) + ']' ).prop('checked', false );
    $search_form.submit();
  });

  // Clear all selected filters.
  $( '.clearall' ).off( 'click' ).on( 'click', function() {
    $( 'input[name^="f["]' ).prop( 'checked', false );
    $search_form.submit();
  });

  // Add click event for load more button in blocks.
  $load_more_button.off( 'click' ).on( 'click', function() {
    // If this button has this class then Lazy-loading is enabled.
    if ( $(this).hasClass( 'btn-load-more-async' ) ) {
      var next_page = $(this).data( 'current_page' ) + 1;
      $(this).data( 'current_page', next_page );

      $.ajax({
        url: localizations.ajaxurl,
        type: 'GET',
        data: {
          action:          'get_paged_posts',
          'search-action': 'get_paged_posts',
          'paged':         next_page,
          'query-string':  decodeURIComponent( location.search ).substr( 1 )		// Ignore the ? in the search url (first char).
        },
        dataType: 'html'
      }).done(function ( response ) {
        // Append the response at the bottom of the results and then show it.
        $( '.multiple-search-result .list-unstyled' ).append( response );
        $( '.row-hidden:last' ).removeClass( 'row-hidden' ).show( 'fast' );
      }).fail(function ( jqXHR, textStatus, errorThrown ) {
        console.log(errorThrown); //eslint-disable-line no-console
      });
    } else {
      var $row = $( '.row-hidden', $load_more_button.closest( '.container' ) );

      if ( 1 === $row.length ) {
        $load_more_button.closest( '.load-more-button-div' ).hide( 'fast' );
      }
      $row.first().show( 'fast' ).removeClass( 'row-hidden' );
    }
  });

  // Reveal more results just by scrolling down the first 2 times.
  $( window ).scroll(function() {
    if ($load_more_button.length > 0) {
      var element_top    = $load_more_button.offset().top,
        element_height = $load_more_button.outerHeight(),
        window_height  = $(window).height(),
        window_scroll  = $(this).scrollTop(),
        load_earlier_offset = 250;

      if ( load_more_count < localizations.show_scroll_times ) {
        // If next page has not loaded then load next page as soon as scrolling
        // reaches 'load_earlier_offset' pixels before the Load more button.
        if ( ! loaded_more && window_scroll > (element_top + element_height - window_height - load_earlier_offset)) {
          load_more_count++;
          $load_more_button.click();
          loaded_more = true;

          // Add a throttle to avoid multiple scroll events from firing together.
          setTimeout(function () {
            loaded_more = false;
          }, 500);
        }
        if (window_scroll > (element_top + element_height - window_height)) {
          $('.row-hidden').removeClass('row-hidden').show('fast');
        }
      }
      return false;
    }
  });
});
