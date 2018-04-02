var media_domain = "https://www.media.greenpeace.org";

jQuery(document).ready(function () {


	jQuery("#search_gpi_img_btn").on('click', function () {
		var server_id = $(this).parent().attr('rel');


	});

	function authenticate(username, password) {
		$.ajax({
			url: media_domain + '/API/Authentication/v1.0/Login',
			cache: false,
			dataType: "json",
			type: 'get',
			data: {
				username: username,
				password: password,
				format: 'json'
			},
			beforeSend: function () {
				$('#server_details').animate({opacity: 0.2}, 200);
				//$('#server_details').html('');
			},
			success: function (result) {
				alert(result);
				var source = $("#server-info-template").html();
				var template = Handlebars.compile(source);
				var html = template(result);
				$('#server_details').html(html);
			},
			error: function () {
			}
		}).always(function () {
			$('#server_details').animate({opacity: 1}, 200);
		});
	}

	function search(keyword, token) {
		token = 'CortexD7lmwOLHwyWdw43UiFjfmLRYoCbpCoCmBz0fepX44KI*';
		$.ajax({
			url: media_domain + '/API/search/v3.0/search',
			cache: false,
			dataType: "json",
			type: 'get',
			data: {
				query: 'Keyword:flowers',
				fields: 'Title',
				token: token,
				format: 'json'
			},
			beforeSend: function () {
				$('#server_details').animate({opacity: 0.2}, 200);
			},
			success: function (result) {
				alert(result);
				var source = $("#server-info-template").html();
				var template = Handlebars.compile(source);
				var html = template(result);
				$('#server_details').html(html);
			},
			error: function () {
			}
		}).always(function () {
			$('#server_details').animate({opacity: 1}, 200);
		});
	}


});