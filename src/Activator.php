<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\M001EnableEnFormFeature;

/**
 * Class Activator.
 * The main class that has activation/deactivation hooks for planet4 master-theme.
 */
class Activator {

	/**
	 * Activator constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks the activator functions.
	 */
	protected function hooks() {
		add_action( 'after_switch_theme', [ self::class, 'run' ] );
	}

	/**
	 * Run activation functions.
	 */
	public static function run(): void {
		Campaigner::register_role_and_add_capabilities();
		self::do_migrations();
	}

	/**
	 * Run any new migrations and record them in the log.
	 */
	private static function do_migrations(): void {
		// Fetch migration ids that have run from WP option.
		$log = MigrationLog::from_wp_options();

		/**
		 * @var Migration[] $migrations
		 */
		$migrations = [
			M001EnableEnFormFeature::class,
		];

		// Loop migrations and run those that haven't run yet.
		foreach ( $migrations as $migration ) {
			if ( $log->already_ran( $migration::get_id() ) ) {
				continue;
			}

			$migration::run();
			$log->add( $migration::get_id() );
		}

		$log->persist();
	}
}
