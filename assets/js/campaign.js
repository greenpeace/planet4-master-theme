"use strict"

function urlParam(name) {
	var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
	if (results == null) {
		return null;
	}
	else {
		return decodeURI(results[1]) || null;
	}
}

var $switcher = $('#theme-switcher select');
$switcher.on('change', function () {
	var newTheme = $(this).val();
	var newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?theme=' + newTheme;
	window.history.pushState({path: newUrl}, '', newUrl);
	var body = $('body');
	$switcher.find('option').each(function () {
		body.removeClass('theme-' + $(this).val());
	});
	body.addClass('theme-' + newTheme);
});

// if defined in the query parameter, update the selected item
var theme = urlParam('theme');
if (theme) {
	$switcher.find('option').each(function () {
		var $option = $(this);
		if ($option.attr('value') === theme) {
			$option.attr('selected', 'selected');
		}
	})
}
