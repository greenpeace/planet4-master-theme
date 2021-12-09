<?php

namespace P4\MasterTheme;

/**
 * Wrapper class for the enqueue function because we can't autoload functions.
 */
final class PublicAssets {
	/**
	 * Load styling and behaviour on website pages.
	 */
	public static function enqueue(): void {
		$theme_dir = get_template_directory_uri();
		// master-theme assets.
		$css_creation = filectime( get_template_directory() . '/assets/build/style.min.css' );
		$js_creation  = filectime( get_template_directory() . '/assets/build/index.js' );

		// CSS files.
		wp_enqueue_style(
			'bootstrap',
			$theme_dir . '/assets/build/bootstrap.min.css',
			[],
			Loader::theme_file_ver( 'assets/build/bootstrap.min.css' )
		);

		// This loads a linked style file since the relative images paths are outside the build directory.
		wp_enqueue_style(
			'parent-style',
			$theme_dir . '/assets/build/style.min.css',
			[ 'bootstrap' ],
			$css_creation
		);
		self::conditionally_load_partials();

		$jquery_should_wait = is_plugin_active( 'planet4-plugin-gutenberg-blocks/planet4-gutenberg-blocks.php' ) && ! is_user_logged_in();

		$jquery_deps = $jquery_should_wait ? [ 'planet4-blocks-script' ] : [];

		// JS files.
		wp_deregister_script( 'jquery' );
		wp_register_script(
			'jquery',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js',
			$jquery_deps,
			'3.3.1',
			true
		);

		// Variables reflected from PHP to the JS side.
		$localized_variables = [
			// The ajaxurl variable is a global js variable defined by WP itself but only for the WP admin
			// For the frontend we need to define it ourselves and pass it to js.
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'show_scroll_times' => Search::SHOW_SCROLL_TIMES,
		];

		wp_register_script(
			'main',
			$theme_dir . '/assets/build/index.js',
			[ 'jquery' ],
			$js_creation,
			true
		);
		wp_localize_script( 'main', 'localizations', $localized_variables );
		wp_enqueue_script( 'main' );
		wp_enqueue_script(
			'youtube',
			$theme_dir . '/assets/build/lite-yt-embed.js',
			[],
			1,
			true
		);
	}

	/**
	 * Load any conditional CSS partials.
	 * We can further split partials for other things:
	 * - CSS per post type
	 * - CSS only loaded based on options (e.g. minimal navigation)
	 * - CSS specific to a particular page (e.g. search)
	 */
	private static function conditionally_load_partials(): void {
		$navbar_version = Features::is_active( Features::NEW_DESIGN_NAVIGATION_BAR )
			? planet4_get_option( 'website_navigation_style', 'dark' )
			: 'old';
		$navbar_file    = '/assets/build/navigation-bar-' . $navbar_version . '.min.css';
		Loader::enqueue_versioned_style( $navbar_file, 'navigation-bar', [ 'parent-style' ] );

		$country_selector_version = Features::is_active( Features::NEW_DESIGN_COUNTRY_SELECTOR ) ? 'new' : 'old';
		$country_selector_file    = '/assets/build/country-selector-' . $country_selector_version . '.min.css';
		Loader::enqueue_versioned_style( $country_selector_file, 'country-selector', [ 'parent-style' ] );
	}
}
