$ = jQuery;

// Dashboard page.
$(function() {
	$('.btn-cp-action').off('click').on('click', function (event) {
		event.preventDefault();
		var $btn      = $(this);
		var $response = $( '.cp-subitem-response', $btn.parent() );

		$.ajax({
			url: ajaxurl,
			type: 'GET',
			data: {
				action: $btn.data( 'action' ),
				'cp-action': $btn.data( 'action' ),
				'_wpnonce': $( '#_wpnonce' ).val()
			},
			dataType: 'json'
		})
		.done(function ( response ) {
			if ( response.message ) {
				$response.hide().removeClass( 'cp-error cp-success' );
				$response.text( response.message );
				if ( response.class ) {
					$response.addClass( response.class );
				}
				$response.show( 'slow' );
			}
		});
	});
});
