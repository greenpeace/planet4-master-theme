<?php

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\CloudflarePurger;
use P4\MasterTheme\Feature;
use P4\MasterTheme\Features\CloudflareDeployPurge;
use P4\MasterTheme\Features\Dev\AllowAllBlocks;
use P4\MasterTheme\Features\Dev\BetaBlocks;
use P4\MasterTheme\Features\Dev\CoreBlockPatterns;
use P4\MasterTheme\Features\Dev\DisableDataSync;
use P4\MasterTheme\Features\Dev\ListingPageGridView;
use P4\MasterTheme\Features\Dev\WPTemplateEditor;
use P4\MasterTheme\Features\EngagingNetworks;
use P4\MasterTheme\Features\ImageArchive;
use P4\MasterTheme\Features\LazyYoutubePlayer;
use P4\MasterTheme\Features\PurgeOnFeatureChanges;
use P4\MasterTheme\Features\RedirectRedirectPages;
use P4\MasterTheme\Loader;
use P4\MasterTheme\Settings;

/**
 * Wrapper class for accessing feature settings and setting up the settings page.
 */
class Features
{
    public const OPTIONS_KEY = 'planet4_features';

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
    public static function get_options_page(): array
    {
        return [
            'title' => 'Features',
            'description' => self::get_description(),
            'root_option' => self::OPTIONS_KEY,
            'fields' => self::get_fields(),
            'add_scripts' => static function () {
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
            ImageArchive::class,
            EngagingNetworks::class,
            CloudflareDeployPurge::class,
            PurgeOnFeatureChanges::class,
            LazyYoutubePlayer::class,
            RedirectRedirectPages::class,

            // Dev only.
            DisableDataSync::class,
            BetaBlocks::class,
            WPTemplateEditor::class,
            CoreBlockPatterns::class,
            AllowAllBlocks::class,
            ListingPageGridView::class,
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
    public static function hooks()
    {
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
    public static function on_pre_process($cmb, $object_id)
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

    /**
     * Hook running after field is saved
     *
     * @param string     $field_id The current field id paramater.
     * @param bool       $updated  Whether the metadata update action occurred.
     * @param string     $action   Action performed. Could be "repeatable", "updated", or "removed".
     * @param CMB2_Field $field    This field object.
     */
    public static function on_field_save($field_id, $updated, $action, $field)
    {
        // This requires a toggle because we may be hitting a sort of rate limit from the deploy purge alone.
        // For now it's better to leave this off on test instances, to avoid purges failing on production because we hit
        // the rate limit.
        if (! PurgeOnFeatureChanges::is_active()) {
            return;
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
    public static function on_features_saved($object_id, $updated, $cmb)
    {
        if (self::$purge_cloudflare) {
            is_plugin_active('cloudflare/cloudflare.php') && ( new CloudflarePurger() )->purge_all();
        }
    }
}
