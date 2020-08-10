<?php

namespace P4\MasterTheme;

/**
 * Wrapper class for accessing feature settings and setting up the settings page.
 */
class Features {

	/**
	 * @var string Cloudflare image optimization feature.
	 */
	public const CLOUDFLARE_IMAGE_OPTIMIZATION = 'cloudflare_img_opt';

	/**
	 * @var string Media library refactored feature.
	 */
	public const IMAGE_ARCHIVE = 'feature_image_archive';

	/**
	 * Get the features options page settings.
	 *
	 * @return array Settings for the options page.
	 */
	public static function get_options_page(): array {

		return [
			'title'       => 'Features',
			'fields'      => self::get_fields(),
			'add_scripts' => static function () {
				Loader::enqueue_versioned_script( '/admin/js/features_save_redirect.js' );
			},
		];
	}

	/**
	 * Get the fields for each feature.
	 *
	 * @return array[] The fields for each feature.
	 */
	private static function get_fields(): array {
		return [
			[
				'name' => __( 'Enable Cloudflare Image Optimization', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable Cloudflare Image Optimization option for images which uses a "cf_img_url" twig filter. for more info',
					'planet4-master-theme-backend'
				) . ' <a href="https://developers.cloudflare.com/images/about">' . __(
					'click here',
					'planet4-master-theme-backend'
				) . '</a>.',
				'id'   => self::CLOUDFLARE_IMAGE_OPTIMIZATION,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Cloudflare Image Optimization Options', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Add Cloudflare image optimization url "options" value',
					'planet4-master-theme-backend'
				) . '[Comma-separated text].(https://zone/cdn-cgi/image/options/source-image)<br />e.g. width=80,quality=75,fit=cover',
				'id'   => 'cloudflare_options_txt',
				'type' => 'text',
			],
			[
				'name' => __( 'Image Archive (beta, name subject to change)', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Try out the new Image Archive (formerly known as "GPI Media Library").',
					'planet4-master-theme-backend'
				),
				'id'   => self::IMAGE_ARCHIVE,
				'type' => 'checkbox',
			],
		];
	}

	/**
	 * Check whether a feature is active.
	 *
	 * @param string $name The name of the feature we're checking.
	 *
	 * @return bool Whether the feature is active.
	 */
	public static function is_active( string $name ): bool {
		$features = get_option( Settings::KEY );

		return isset( $features[ $name ] ) && $features[ $name ];
	}
}
