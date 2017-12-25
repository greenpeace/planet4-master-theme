$ = jQuery;

// Dashboard page.
$(function() {
	$('.btn-cp-action').off('click').on('click', function (event) {
		event.preventDefault();

		$.ajax({
			url: ajaxurl,
			type: 'GET',
			data: {
				action: $(this).data( 'action' ),
				'cp-action': $(this).data( 'action' ),
				'_wpnonce': $( '#_wpnonce' ).val()
			}
		})
		.done(function ( response ) {
			console.log( response );
		});
	});
});