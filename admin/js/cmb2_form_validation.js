jQuery(document).ready(function($) {

  $form       = $( document.getElementById( 'post' ) );
  $htmlbody   = $( 'html, body' );
  $toValidate = $( '[data-validation]' );

  if ( ! $toValidate.length ) {
    return;
  }

  function checkValidation( evt ) {
    var labels = [];
    var $first_error_row = null;
    var $row = null;

    function add_required( $row ) {
      $row.css({ 'background-color': 'rgb(255, 170, 170)' });
      $first_error_row = $first_error_row ? $first_error_row : $row;
      labels.push( $row.find( '.cmb-th label' ).text() );
    }

    function remove_required( $row ) {
      $row.css({ background: '' });
    }

    $toValidate.each( function() {
      var $this = $(this);
      var val   = $this.val();
      $row      = $this.parents( '.cmb-row' );

      if ( $this.is( '[type="button"]' ) || $this.is( '.cmb2-upload-file-id' ) ) {
        return true;
      }

      // Apply validation only for campaign post types.
      if ( 'campaign' === $( '#post_type' ).val() && 'required' === $this.data( 'validation' ) ) {
        if ( $row.is( '.cmb-type-file-list' ) ) {

          var has_LIs = $row.find( 'ul.cmb-attach-list li' ).length > 0;

          if ( ! has_LIs ) {
            add_required( $row );
          } else {
            remove_required( $row );
          }

        } else {
          if ( ! val || 'not set' === val ) {
            add_required( $row );
          } else {
            remove_required( $row );
          }
        }
      }

    });

    if ( $first_error_row ) {
      evt.preventDefault();
      // Open campaign fields postbox, if closed.
      var p4_postbox = $first_error_row.parents( '.cmb2-postbox' );
      if ( 'p4_campaign_fields' === p4_postbox.attr( 'id' ) && p4_postbox.hasClass( 'closed' ) ) {
        $htmlbody.find( '#p4_campaign_fields' ).removeClass( 'closed' );
      }
      $htmlbody.animate({
        scrollTop: ( $first_error_row.offset().top - 200 )
      }, 1000);

    }

  }

  $form.on( 'submit', checkValidation );
});
