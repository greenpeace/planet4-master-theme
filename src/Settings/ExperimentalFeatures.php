<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Features\NewIdentityStyles;
use P4\MasterTheme\Features\PurgeOnFeatureChanges;
use P4\MasterTheme\Loader;
use P4\MasterTheme\Settings;
use P4\MasterTheme\CloudflarePurger;
use CMB2;

/**
 * Experimental features.
 */
class ExperimentalFeatures
{
    public const OPTIONS_KEY = 'planet4_experimental_features';

    /**
     * @var bool Purge Cloudflare cache on save
     */
    public static bool $purge_cloudflare = false;

    /**
     * Register current options status before processing, to detect any change later.
     *
     * @var array $preprocess_fields
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
            'title' => 'Experimental features',
            'description' => __('These features are experiments or work in progress.', 'planet4-master-theme-backend'),
            'root_option' => self::OPTIONS_KEY,
            'fields' => self::get_fields(),
            'add_scripts' => static function (): void {
                Loader::enqueue_versioned_script('/admin/js/features_save_redirect.js');
            },
        ];
    }

    /**
     * Add hooks related to Experimental Features activation
     */
    public static function hooks(): void
    {
        // On field save.
        add_action(
            'cmb2_options-page_process_fields_' . Settings::METABOX_ID,
            [self::class, 'on_pre_process'],
            10,
            2
        );

        add_action(
            'cmb2_save_field',
            [self::class, 'on_field_save'],
            10,
            4
        );

        // After all fields are saved.
        add_action(
            'cmb2_save_options-page_fields_' . Settings::METABOX_ID,
            [self::class, 'on_features_saved'],
            10,
            4
        );
    }

    /**
     * Get form fields.
     *
     * @return array  The fields.
     */
    public static function get_fields(): array
    {
        return [
            NewIdentityStyles::get_cmb_field(),
        ];
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

                    if (!$cmb_field) {
                        return [];
                    }

                    return [$f['id'] => $cmb_field->value()];
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
    public static function on_field_save(string $field_id, bool $updated, string $action, object $field): void
    {
        // This requires a toggle because we may be hitting a sort of rate limit from the deploy purge alone.
        // For now it's better to leave this off on test instances, to avoid purges failing on production because we hit
        // the rate limit.
        if (
            !PurgeOnFeatureChanges::is_active() ||
            !in_array($field_id, [NewIdentityStyles::id()], true) ||
            $field->value() === self::$preprocess_fields[$field_id]
        ) {
            return;
        }

        self::$purge_cloudflare = true;
    }

    /**
     * Hook running after all features are saved
     */
    public static function on_features_saved(): void
    {
        if (!self::$purge_cloudflare) {
            return;
        }

        is_plugin_active('cloudflare/cloudflare.php') && (new CloudflarePurger())->purge_all();
    }
}
