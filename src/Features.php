<?php

namespace P4\MasterTheme;

/**
 * Wrapper class for accessing feature settings and setting up the settings page.
 */
class Features {

	/**
	 * @var string Media library refactored feature.
	 */
	public const IMAGE_ARCHIVE = 'feature_image_archive';

	public const ENGAGING_NETWORKS = 'feature_engaging_networks';

	public const CLOUDFLARE_DEPLOY_PURGE = 'cloudflare_deploy_purge';

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
				'name' => __( 'Greenpeace Image Archive (beta, name subject to change)', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Beta test the new Image Archive. This will replace the GPI Media Library plugin. We are renaming it to avoid confusion with the WordPress Media Library.',
					'planet4-master-theme-backend'
				),
				'id'   => self::IMAGE_ARCHIVE,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Engaging Networks integration', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable the Engaging Networks integration. If turned on you will be able to use the EN Form block, as well as the "Progress Bar inside EN Form" Counter block style.',
					'planet4-master-theme-backend'
				),
				'id'   => self::ENGAGING_NETWORKS,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Purge HTML from Cloudflare on deploy.', 'planet4-master-theme-backend' ),
				'desc' => __(
					'WARNING: Do not change this setting without checking with Planet4 team. This will purge all URLs from Cloudflare after a deploy. We are still experimenting with the effects of that on Cloudflare performance.',
					'planet4-master-theme-backend'
				),
				'id'   => self::CLOUDFLARE_DEPLOY_PURGE,
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
