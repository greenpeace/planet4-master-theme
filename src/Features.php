<?php

namespace P4\MasterTheme;

class Features {
	private const PAGE_SLUG = 'planet4_features';
	private const PAGE_TITLE = 'Features';
	private const METABOX_ID = 'planet4_features';

	public const IMAGE_ARCHIVE = 'feature_image_archive';

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
	}

	public static function is_active( string $name ): bool {
		$features = get_option( self::METABOX_ID );

		return isset( $features[ $name ] ) && $features[ $name ];
	}

	public static function add_settings_page(): void {
		add_options_page( self::PAGE_TITLE,
				self::PAGE_TITLE,
				'manage_options',
				self::PAGE_SLUG,
				[ self::class, 'admin_page_display' ] );

	}

	private static function get_metabox_settings(): array {
		$fields = [
				[
						'name' => __( 'New image archive', 'planet4-master-theme-backend' ),
						'id'   => self::IMAGE_ARCHIVE,
						'type' => 'checkbox',
				],

		];

		return [
				'id'         => self::METABOX_ID,
				'show_on'    => [
						'key'   => 'options-page',
						'value' => [
								self::PAGE_SLUG,
						],
				],
				'show_names' => true,
				'fields'     => $fields,
		];

	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 */
	public static function admin_page_display(): void {
		?>
		<div class="wrap ">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php cmb2_metabox_form( self::get_metabox_settings(), self::METABOX_ID ); ?>
		</div>
		<?php
	}
}
