<?php
/**
 * Class UIIntegration.
 *
 * @package P4\MasterTheme\ImageArchive
 */

namespace P4\MasterTheme\ImageArchive;

use P4\MasterTheme\Capability;
use P4\MasterTheme\Features;
use P4\MasterTheme\Loader;

/**
 * Add some WordPress UI elements if the feature is active.
 */
class UiIntegration {
	/**
	 * UiIntegration constructor.
	 */
	public function __construct() {
		self::hooks();
	}

	/**
	 * Hook up to WordPress.
	 */
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
	 *
	 * @todo: This is preserved from the original plugin, but can probably be done in a better way.
	 */
	public static function media_library_post_upload_ui() {
		global $pagenow;
		$classes = 'button media-button button-primary button-large add_media switchtoml';

		// Add the insert media class only when not in the editor, i.e. when on the "Media > Add New" page.
		if ( ( 'post.php' !== $pagenow ) && ( ! in_array( get_post_type(), [ 'post', 'page', 'campaign' ], true ) ) ) {
			$classes .= ' insert-media';
		}
		echo '<button id="db-upload-btn" class="' . $classes . '">' . esc_html__( //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'Upload From GPI Media Library',
			'planet4-master-theme-backend'
		) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param string[] $tabs Existing tabs passed by the filter.
	 *
	 * @return string[] Same tabs with image archive tab added to them.
	 */
	public static function image_archive_tab( $tabs ): array {
		$tabs['image_archive'] = __('GPI Image Archive', 'planet4-master-theme-backend');

		return $tabs;
	}

	/**
	 * Output the iframe for the media library tab.
	 */
	public static function output_image_picker(): void {
		wp_iframe( [ self::class, 'output_picker' ] );
	}

	/**
	 * Register js and output picker root element.
	 */
	public static function output_picker(): void {
		Loader::enqueue_versioned_style( '/admin/css/picker.css' );
		Loader::enqueue_versioned_script(
			'/assets/build/archive_picker.js',
			[
				'wp-element',
				'wp-compose',
				'wp-components',
				'wp-url',
				'wp-api-fetch',
			]
		);
		echo '<div id="archive-picker-root"></div>';
	}

	/**
	 * Create a page with only the picker.
	 */
	public static function create_admin_menu(): void {
		if ( ! current_user_can( Capability::USE_IMAGE_ARCHIVE_PICKER ) ) {
			return;
		}

		add_menu_page(
			__( 'GPI Media Library', 'planet4-master-theme-backend' ),
			__( 'GPI Image Picker', 'planet4-master-theme-backend' ),
			'manage_options',
			'gpi-image-picker',
			[ self::class, 'output_picker' ],
			P4ML_ADMIN_DIR . 'images/logo_menu_page_16x16.png',
			3
		);
	}
}
