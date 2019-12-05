/* global localizations */

// Search page.
export const setupSearch = function($) {
  const $search_form            = $( '#search_form' );
  const $load_more_button       = $( '.btn-load-more-click-scroll' );
  let load_more_count           = 0;
  let current_page              = 1;
  let total_posts               = 0;
  let loaded_more               = false;
  let show_archive_button       = false;
  let load_archive              = false;
  let only_archived_loads       = 0;
  let archive_and_live_loads    = 0;

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
    let $checkbox;
    if ( 0 === $('.filter-modal.show').length ) {
      $( 'input[name^="f["]:not(.modal-checkbox):checked' ).each( function() {
        $checkbox = $( this ).clone( true );
        $checkbox.css('display', 'none');
        $search_form.append( $checkbox );
      } );
    } else {
      $( 'input[name^="f["].modal-checkbox:checked').each( function() {
        $checkbox = $( this ).clone( true );
        $checkbox.css('display', 'none');
        $search_form.append( $checkbox );
      } );
    }
  });

  let $search_results = $( '.multiple-search-result' );
  // Add filter by clicking on the page type label inside a result item.
  // Delegate event handler to the dynamically created descendant elements.
  $search_results.off( 'click', '.search-result-item-head' ).on( 'click', '.search-result-item-head', function() {
    window.location.href = $( this ).data( 'href' );
  });

  // Navigate to the page of the search result item when clicking on it's thumbnail image.
  // Delegate event handler to the dynamically created descendant elements.
  $search_results.off( 'click', '.search-result-item-image').on( 'click', '.search-result-item-image', function() {
    window.location.href = $( '.search-result-item-headline', $( this ).parent() ).attr( 'href' );
  });

  // Underline headline on thumbnail hover.
  $('.search-result-item-image').hover(
    function() {
      $('.search-result-item-headline', $(this).parent()).addClass('search-hover');
    },
    function() {
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
      total_posts    = $(this).data('total_posts');
      const posts_per_load = $(this).data('posts_per_load');
      const archives_per_load = $(this).data('archives_per_load');
      const next_page      = current_page + 1;
      current_page         = next_page;
      $(this).data( 'current_page', next_page );

      let action = load_archive ? 'get_archived_posts' : 'get_paged_posts';

      $.ajax({
        url: localizations.ajaxurl,
        type: 'GET',
        data: {
          action:                       'get_paged_posts',
          'search-action':              action,
          'search_query':               $( '#search_input' ).val().trim(),
          'total_posts':                total_posts,
          'archived_loads':             only_archived_loads,
          'archive_and_live_loads':     archive_and_live_loads,
          'paged':                      next_page,
          'query-string':               decodeURIComponent( location.search ).substr( 1 ) // Ignore the ? in the search url (first char).
        },
        dataType: 'html'
      }).done(function ( response ) {
        // Append the response at the bottom of the results and then show it.
        $( '.multiple-search-result .list-unstyled' ).append( response );
        $( '.row-hidden:last' ).removeClass( 'row-hidden' ).show( 'fast' );
        // if (posts_per_load * next_page > total_posts) {
        //   $load_more_button.hide();
        // }

        let loads_with_only_live_posts = ( next_page - 1 ) - archive_and_live_loads;
        let loaded_posts = ( archive_and_live_loads * posts_per_load - archives_per_load ) + ( loads_with_only_live_posts * posts_per_load );
        let loaded_more_live_posts = total_posts > loaded_posts;

        if ( load_archive && loaded_more_live_posts ) {
          archive_and_live_loads++;
        } else if ( load_archive ) {
          only_archived_loads++;
        }

        checkShowLoadArchive( next_page );

      }).fail(function ( jqXHR, textStatus, errorThrown ) {
        console.log(errorThrown); //eslint-disable-line no-console
      });
    } else {
      const $row = $( '.row-hidden', $load_more_button.closest( '.container' ) );

      if ( 1 === $row.length ) {
        $load_more_button.closest( '.load-more-button-div' ).hide( 'fast' );
      }
      $row.first().show( 'fast' ).removeClass( 'row-hidden' );
    }
  });

  $('.toggle-label').off( 'click' ).on( 'click', function() {
    const $show_archive_toggle = $( '.show-archive-toggle' )[0];

    if ( !$show_archive_toggle.checked ) {
      $( '.toggle-label-include' ).hide();
      $( '.toggle-label-exclude' ).show();

      load_archive = true;
    } else {
      $( '.toggle-label-include' ).show();
      $( '.toggle-label-exclude' ).hide();

      load_archive = false;
    }
  });

  const checkShowLoadArchive = ( current_page = 1 ) => {

    if ( show_archive_button ) {
      return;
    }

    const page_archive_available_after = $load_more_button.data('page_archive_available_after');

    if ( current_page >= page_archive_available_after ) {
      $( '.toggle-wrapper' ).show();
      show_archive_button = true;
    } else {
      $( '.toggle-wrapper' ).hide();
      $( '.toggle-label-exclude' ).hide();
      load_archive = false;
    }
  };

  checkShowLoadArchive();

  // Reveal more results just by scrolling down the first 'show_scroll_times' times.
  $( window ).scroll(function() {
    if ($load_more_button.length > 0) {
      let element_top       = $load_more_button.offset().top,
        element_height      = $load_more_button.outerHeight(),
        window_height       = $(window).height(),
        window_scroll       = $(this).scrollTop(),
        load_earlier_offset = 250;

      if ( load_more_count < localizations.show_scroll_times ) {
        // If next page has not loaded then load next page as soon as scrolling
        // reaches 'load_earlier_offset' pixels before the Load more button.
        if ( ! loaded_more
            && window_scroll > ( element_top + element_height - window_height - load_earlier_offset )
            && ( ( load_more_count + 1 ) * $load_more_button.data('posts_per_load') ) < $load_more_button.data('total_posts' ) ) {

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
};
