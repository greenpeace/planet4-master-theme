<?php

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Features\CloudflareDeployPurge;
use P4\MasterTheme\Features\Dev\AllowAllBlocks;
use P4\MasterTheme\Features\Dev\BetaBlocks;
use P4\MasterTheme\Features\Dev\CoreBlockPatterns;
use P4\MasterTheme\Features\Dev\DisableDataSync;
use P4\MasterTheme\Features\LazyYoutubePlayer;
use P4\MasterTheme\Features\Planet4Blocks;
use P4\MasterTheme\Features\OldPostsArchiveNotice;
use P4\MasterTheme\Features\ActionsTaskType;
use P4\MasterTheme\Features\ActionsDeadline;
use P4\MasterTheme\Features\ActionsUserPersonalization;
use P4\MasterTheme\Loader;
use P4\MasterTheme\Settings;
use CMB2;

/**
 * Wrapper class for accessing feature settings and setting up the settings page.
 */
class Features
{
    public const OPTIONS_KEY = 'planet4_features';

    /**
     * Register current options status before processing, to detect any change later.
     *
     */
    public static array $preprocess_fields = [];

    /**
     * Get the features options page settings.
     *
     * @return array Settings for the options page.
     */
    public static function get_options_page(): array
    {
        return [
            'title' => 'Features',
            'description' => self::get_description(),
            'root_option' => self::OPTIONS_KEY,
            'fields' => self::get_fields(),
            'add_scripts' => static function (): void {
                Loader::enqueue_versioned_script('/admin/js/features_save_redirect.js');
            },
        ];
    }

    /**
     * Get description based on environment.
     *
     * @return string description string.
     */
    public static function get_description(): string
    {
        $description = 'Enable or disable specific Planet 4 features.';
        $dev_flags = '<br>Options with the 👷 icon are only available in dev sites.';

        $dev_site = defined('WP_APP_ENV') && in_array(WP_APP_ENV, [ 'local', 'development' ], true);

        return $dev_site
            ? $description . $dev_flags
            : $description;
    }

    /**
     * Get the fields for each feature.
     *
     * @return array[] The fields for each feature.
     */
    public static function get_fields(): array
    {
        $include_all = defined('WP_APP_ENV') && in_array(WP_APP_ENV, [ 'local', 'development' ], true);

        $features = $include_all
            ? self::all_features()
            : array_filter(
                self::all_features(),
                fn(string $feature): bool => $feature::show_toggle_production()
            );

        return array_map(
            fn(string $feature): array => $feature::get_cmb_field(),
            $features
        );
    }

    /**
     * @return Feature[]|string[] Actually just a string with the class name, gimme the type hint.
     */
    public static function all_features(): array
    {
        // Todo, check a good way to manage menu order.
        // Perhaps an alphabetical order within a group would make most sense?
        // That way controlling whether the feature is live is in one place.
        return [
            CloudflareDeployPurge::class,
            LazyYoutubePlayer::class,
            Planet4Blocks::class,
            OldPostsArchiveNotice::class,
            ActionsTaskType::class,
            ActionsDeadline::class,
            ActionsUserPersonalization::class,

            // Dev only.
            DisableDataSync::class,
            BetaBlocks::class,
            CoreBlockPatterns::class,
            AllowAllBlocks::class,
        ];
    }

    /**
     * Planet 4 options sitting outside of the planet4_options entry
     */
    public static function external_settings(): array
    {
        return [
            CommentsGdpr::class,
            DefaultPostType::class,
            ReadingTime::class,
        ];
    }

    /**
     * Check whether a feature is active.
     *
     * @param string $name The name of the feature we're checking.
     *
     * @return bool Whether the feature is active.
     */
    public static function is_active(string $name): bool
    {
        $features = get_option(self::OPTIONS_KEY);

        $active = isset($features[ $name ]) && $features[ $name ];

        // Filter to allow setting a feature from code, to avoid chicken and egg problem when releasing adaptions to a
        // new feature.
        return (bool) apply_filters("planet4_feature__$name", $active);
    }

    /**
     * Add hooks related to Features activation
     */
    public static function hooks(): void
    {
        add_action(
            'cmb2_options-page_process_fields_' . Settings::METABOX_ID,
            [ self::class, 'on_pre_process' ],
            10,
            2
        );
    }

    /**
     * Save options status on preprocess, to be compared later
     *
     * @param CMB2   $cmb       This CMB2 object.
     * @param string $object_id The ID of the current object.
     */
    public static function on_pre_process(CMB2 $cmb, string $object_id): void
    {
        if (self::OPTIONS_KEY !== $object_id) {
            return;
        }

        self::$preprocess_fields = array_merge(
            ...array_map(
                function ($f) use ($cmb) {
                    /**
                     * @var \CMB2_Field|bool $cmb_field
                     */
                    $cmb_field = $cmb->get_field($f['id']);

                    if (! $cmb_field) {
                        return [];
                    }

                    return [ $f['id'] => $cmb_field->value() ];
                },
                self::get_fields()
            )
        );
    }
}
