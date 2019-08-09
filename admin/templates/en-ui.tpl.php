<!--// phpcs:ignoreFile -- this is not a php file -->
<script type="text/html" id="tmpl-shortcode-ui-field-p4-en-radio">
	<div class="shortcode-ui-field-radio shortcode-ui-attribute-{{ data.attr }}">
		<h3>{{{ data.label }}}</h3>
		<div class="row">
			<# _.each( data.options, function( option ) { #>

				<div class="shortcake-p4-radio-div">
					<label style="display: inline;">
						<input type="radio" name="{{ data.attr }}" value="{{ option.value }}"
						<# if ( option.value == data.value ) { print('checked'); } #> />
							{{ option.label }}
					</label>
					<p>
						<img src="{{ option.image }}" alt="submenu">
					</p>
					<p class="description" style="display: inline">{{{ option.desc }}}</p>
				</div>
			<# }); #>
			<br>
		</div>
	</div>
</script>
