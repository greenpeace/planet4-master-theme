<?php

namespace P4\MasterTheme\ImageArchive;

use P4\MasterTheme\Features;

class UiIntegration {
	public function __construct() {
		self::hooks();
	}

	private static function hooks() {
		if ( ! Features::is_active( Features::IMAGE_ARCHIVE ) ) {
			return;
		}
		add_action( 'admin_menu', [ self::class, 'create_admin_menu' ] );
		add_filter( 'media_upload_tabs', [ self::class, 'image_archive_tab' ] );
		add_action( 'media_upload_image_archive', [ self::class, 'output_image_picker' ] );
		add_action( 'post-upload-ui', [ self::class, 'media_library_post_upload_ui' ] );
	}
	/**
	 * Add GPI Media Library upload button in WP media popup upload UI.
	 */
	public static function media_library_post_upload_ui() {
		global $pagenow;
		$classes = 'button media-button button-primary button-large add_media switchtoml';

		// Add the insert media class only when not in the editor, i.e. when on the "Media > Add New" page.
		if ( ( 'post.php' !== $pagenow ) && ( ! in_array( get_post_type(), [ 'post', 'page', 'campaign' ], true ) ) ) {
			$classes .= ' insert-media';
		}
		print '<button id="db-upload-btn" class="' . $classes . '">' . esc_html__( 'Upload From GPI Media Library', 'planet4-medialibrary' ) . '</button>';
	}

	public static function image_archive_tab( $tabs ): array {
		$tabs['image_archive'] = __('GPI Image Archive', 'planet4-master-theme-backend');

		return $tabs;
	}

	public static function output_image_picker(): void {
		wp_iframe( [ self::class, 'output_picker' ] );
	}

	public static function output_picker(): void {
		wp_enqueue_style( 'picker',
			get_template_directory_uri() . '/admin/css/picker.css',
			[] );
		wp_enqueue_script( 'pickerui',
			get_template_directory_uri() . '/assets/build/archive_picker.js',
			[
				'wp-element',
				'wp-compose',
				'wp-components',
				'wp-url',
				'wp-api-fetch',
			] );
		echo '<div id="archive-picker-root"></div>';
	}

	public static function create_admin_menu(): void {
		$current_user = wp_get_current_user();

		if ( ! in_array( 'administrator', $current_user->roles, true )
		     && ! in_array( 'editor',
				$current_user->roles,
				true )
		) {
			return;
		}

		add_menu_page( __( 'GPI Media Library', 'planet4-medialibrary' ),
			__( 'GPI Image Picker', 'planet4-medialibrary' ),
			'manage_options',
			'gpi-image-picker',
			[ self::class, 'output_picker' ],
			P4ML_ADMIN_DIR . 'images/logo_menu_page_16x16.png',
		3
		);
	}
}
