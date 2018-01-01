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
			if ( response[0] ) {
				$response.hide().removeClass( 'cp-error cp-success' );

				$response.text( response[0] );
				if ( response[1] ) {
					$response.addClass( response[1] );
				}
				$response.show( 'slow' );
			}
		});
	});
});
