<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\M001EnableEnFormFeature;
use P4\MasterTheme\Migrations\M002EnableLazyYoutube;
use P4\MasterTheme\Migrations\M004UpdateMissingMediaPath;
use P4\MasterTheme\Migrations\M003UpdateArticlesBlockAttribute;
use P4\MasterTheme\Migrations\M005TurnBoxoutSettingIntoBlock;
use P4\MasterTheme\Migrations\M006MoveFeaturesToSeparateOption;
use P4\MasterTheme\Migrations\M007RemoveEnhancedDonateButtonOption;
use P4\MasterTheme\Migrations\M008RemoveArticlesDefaultOptions;

/**
 * Run any new migration scripts and record results in the log.
 */
class Migrator {

	/**
	 * Run any new migration scripts and record results in the log.
	 */
	public static function migrate(): void {

		// Fetch migration script ids that have run from WP option.
		$log = MigrationLog::from_wp_options();

		/**
		 * @var MigrationScript[] $scripts
		 */
		$scripts = [
			M001EnableEnFormFeature::class,
			M002EnableLazyYoutube::class,
			M004UpdateMissingMediaPath::class,
			M003UpdateArticlesBlockAttribute::class,
			M005TurnBoxoutSettingIntoBlock::class,
			M006MoveFeaturesToSeparateOption::class,
			M007RemoveEnhancedDonateButtonOption::class,
			M008RemoveArticlesDefaultOptions::class,
		];

		// Loop migrations and run those that haven't run yet.
		foreach ( $scripts as $script ) {
			if ( $log->already_ran( $script::get_id() ) ) {
				continue;
			}

			$record = $script::run();
			$log->add( $record );
		}

		$log->persist();
	}
}
