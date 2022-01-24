<?php

namespace P4\MasterTheme;

/**
 * Wrapper class for accessing feature settings and setting up the settings page.
 */
class Features {

	public const OPTIONS_KEY = 'planet4_features';

	/**
	 * @var string Media library refactored feature.
	 */
	public const IMAGE_ARCHIVE = 'feature_image_archive';

	public const ENGAGING_NETWORKS = 'feature_engaging_networks';

	public const CLOUDFLARE_DEPLOY_PURGE = 'cloudflare_deploy_purge';

	public const LAZY_YOUTUBE_PLAYER = 'lazy_youtube_player';

	public const THEME_EDITOR = 'theme_editor';

	public const THEME_EDITOR_NON_LOGGED_IN = 'theme_editor_non_logged_in';

	public const BETA_BLOCKS = 'beta_blocks';

	public const WP_TEMPLATE_EDITOR = 'wp_template_editor';

	public const NEW_DESIGN_COUNTRY_SELECTOR = 'new_design_country_selector';

	public const NEW_DESIGN_NAVIGATION_BAR = 'new_design_navigation_bar';

	public const PURGE_ON_FEATURE_CHANGES = 'purge_on_feature_changes';

	public const GOOGLE_SHEET_REPLACES_SMARTSHEET = 'google_sheet_replaces_smartsheet';

	public const CORE_BLOCK_PATTERNS = 'core_block_patterns';

	/**
	 * @var bool Purge Cloudflare cache on save
	 */
	public static $purge_cloudflare = false;

	/**
	 * Register current options status before processing, to detect any change later.
	 *
	 * @var array $preprocess_fields
	 */
	public static $preprocess_fields = [];

	/**
	 * Get the features options page settings.
	 *
	 * @return array Settings for the options page.
	 */
	public static function get_options_page(): array {
		return [
			'title'       => 'Features',
			'root_option' => self::OPTIONS_KEY,
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
	public static function get_fields(): array {
		$fields = [
			[
				'name' => __( 'New Image Archive (Beta)', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Beta test the new Image Archive. This will replace the GPI Media Library plugin.',
					'planet4-master-theme-backend'
				),
				'id'   => self::IMAGE_ARCHIVE,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Engaging Networks integration', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable the Engaging Networks integration.<br>If turned on you will be able to use the EN Form block, as well as the "Progress Bar inside EN Form" Counter block style.',
					'planet4-master-theme-backend'
				),
				'id'   => self::ENGAGING_NETWORKS,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Purge HTML from Cloudflare on deploy', 'planet4-master-theme-backend' ),
				'desc' => __(
					'WARNING: Do not change this setting without checking with the Planet 4 team.<br>This will purge all URLs from Cloudflare cache after a deploy.',
					'planet4-master-theme-backend'
				),
				'id'   => self::CLOUDFLARE_DEPLOY_PURGE,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Purge all HTML on feature changes', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Whether to purge all pages from Cloudflare cache when changing features.<br>Only enable on production, on test instances it results in too many purge requests.',
					'planet4-master-theme-backend'
				),
				'id'   => self::PURGE_ON_FEATURE_CHANGES,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Lazy YouTube player', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Only load the YouTube player after clicking a video.',
					'planet4-master-theme-backend'
				),
				'id'   => self::LAZY_YOUTUBE_PLAYER,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Enable new Country selector design', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable the new Country selector design as described in the <a href="https://p4-designsystem.greenpeace.org/05f6e9516/v/0/p/16a899-footer" target="_blank">design system</a>.<br/>This might break your child theme, depending on how you extended the main templates and CSS.<br/>Changing this option will take a bit of time, as it also attempts to clear the Cloudflare cache.',
					'planet4-master-theme-backend'
				),
				'id'   => self::NEW_DESIGN_COUNTRY_SELECTOR,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Enable new Navigation bar design', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable the new Navigation bar design as described in the <a href="https://p4-designsystem.greenpeace.org/05f6e9516/p/106cdb-navigation-bar" target="_blank">design system</a>.<br/>This might break your child theme, depending on how you extended the main templates and CSS.<br/>Changing this option will take a bit of time, as it also attempts to clear the Cloudflare cache.',
					'planet4-master-theme-backend'
				),
				'id'   => self::NEW_DESIGN_NAVIGATION_BAR,
				'type' => 'checkbox',
			],
			[
				'name' => __( 'Google Sheets replaces Smartsheet', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Toggle whether to use Google Sheets to fetch the list of analytics options.',
					'planet4-master-theme-backend'
				),
				'id'   => self::GOOGLE_SHEET_REPLACES_SMARTSHEET,
				'type' => 'checkbox',
			],

		];

		if ( defined( 'WP_APP_ENV' ) && ( WP_APP_ENV === 'development' || WP_APP_ENV === 'local' ) ) {
			$fields[] = [
				'name' => __( 'Allow Beta Blocks in post editor', 'planet4-master-theme-backend' ),
				'desc' => __(
					'If enabled, you can use early or unstable versions of blocks in the post editor.<br>These will be in the "Planet 4 Blocks - BETA" category.',
					'planet4-master-theme-backend'
				),
				'id'   => self::BETA_BLOCKS,
				'type' => 'checkbox',
			];
			$fields[] = [
				'name' => __( 'Theme editor (experimental)', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable CSS variables based theme editor for logged in users.',
					'planet4-master-theme-backend'
				),
				'id'   => self::THEME_EDITOR,
				'type' => 'checkbox',
			];
			$fields[] = [
				'name' => __( 'Enable WordPress template editor', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable the WordPress "template editor" to allow Full Site Editiong.',
					'planet4-master-theme-backend'
				),
				'id'   => self::WP_TEMPLATE_EDITOR,
				'type' => 'checkbox',
			];
			$fields[] = [
				'name' => __( 'Enable core block patterns', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Allows using the default block patterns that come with WordPress.',
					'planet4-master-theme-backend'
				),
				'id'   => self::CORE_BLOCK_PATTERNS,
				'type' => 'checkbox',
			];
		}

		if ( defined( 'WP_APP_ENV' ) && WP_APP_ENV === 'local' ) {
			$fields[] = [
				'name' => __( 'Theme editor (experimental): non-logged in', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Enable CSS variables based theme editor without requiring login.',
					'planet4-master-theme-backend'
				),
				'id'   => self::THEME_EDITOR_NON_LOGGED_IN,
				'type' => 'checkbox',
			];
		}

		return $fields;
	}

	/**
	 * Check whether a feature is active.
	 *
	 * @param string $name The name of the feature we're checking.
	 *
	 * @return bool Whether the feature is active.
	 */
	public static function is_active( string $name ): bool {
		$features = get_option( self::OPTIONS_KEY );

		// Temporary fallback to ensure it works before migration runs.
		if ( ! $features ) {
			$features = get_option( Settings::KEY );
		}

		$active = isset( $features[ $name ] ) && $features[ $name ];

		// Filter to allow setting a feature from code, to avoid chicken and egg problem when releasing adaptions to a
		// new feature.
		return (bool) apply_filters( "planet4_feature__$name", $active );
	}

	/**
	 * Add hooks related to Features activation
	 */
	public static function hooks() {
		// On field save.
		add_action(
			'cmb2_options-page_process_fields_' . Settings::METABOX_ID,
			[ self::class, 'on_pre_process' ],
			10,
			2
		);

		add_action(
			'cmb2_save_field',
			[ self::class, 'on_field_save' ],
			10,
			4
		);
		// After all fields are saved.
		add_action(
			'cmb2_save_options-page_fields_' . Settings::METABOX_ID,
			[ self::class, 'on_features_saved' ],
			10,
			4
		);
	}

	/**
	 * Save options status on preprocess, to be compared later
	 *
	 * @param array $cmb       This CMB2 object.
	 * @param int   $object_id The ID of the current object.
	 */
	public static function on_pre_process( $cmb, $object_id ) {
		if ( self::OPTIONS_KEY !== $object_id ) {
			return;
		}

		self::$preprocess_fields = array_merge(
			...array_map(
				function ( $f ) use ( $cmb ) {
					/**
					 * @var \CMB2_Field|bool $cmb_field
					 */
					$cmb_field = $cmb->get_field( $f['id'] );

					if ( ! $cmb_field ) {
						return [];
					}

					return [ $f['id'] => $cmb_field->value() ];
				},
				self::get_fields()
			)
		);
	}

	/**
	 * Hook running after field is saved
	 *
	 * @param string     $field_id The current field id paramater.
	 * @param bool       $updated  Whether the metadata update action occurred.
	 * @param string     $action   Action performed. Could be "repeatable", "updated", or "removed".
	 * @param CMB2_Field $field    This field object.
	 */
	public static function on_field_save( $field_id, $updated, $action, $field ) {
		// This requires a toggle because we may be hitting a sort of rate limit from the deploy purge alone.
		// For now it's better to leave this off on test instances, to avoid purges failing on production because we hit
		// the rate limit.
		if ( ! self::is_active( self::PURGE_ON_FEATURE_CHANGES ) ) {
			return;
		}

		if ( in_array( $field_id, [ self::NEW_DESIGN_COUNTRY_SELECTOR, self::NEW_DESIGN_NAVIGATION_BAR ], true ) ) {
			if ( $field->value() !== self::$preprocess_fields[ $field_id ] ) {
				self::$purge_cloudflare = true;
			}
		}
	}

	/**
	 * Hook running after all features are saved
	 *
	 * @param int    $object_id   The ID of the current object.
	 * @param string $updated     Array of field ids that were updated.
	 *                            Will only include field ids that had values change.
	 * @param array  $cmb         This CMB2 object.
	 */
	public static function on_features_saved( $object_id, $updated, $cmb ) {
		if ( self::$purge_cloudflare ) {
			is_plugin_active( 'cloudflare/cloudflare.php' ) && ( new CloudflarePurger() )->purge_all();
		}
	}
}
