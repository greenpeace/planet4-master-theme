<?php

namespace P4\MasterTheme;

/**
 * Base class for toggleable features.
 */
abstract class Feature
{
    public const OPTIONS_KEY = 'planet4_features';

    /**
     * @return string ID primarily used for storing in WP options.
     */
    abstract public static function id(): string;

    /**
     * Get the translated name. It's important the class includes the translation function call. This is used by
     * automation to extract translatable strings from the source code.
     *
     * @return string A human readable name.
     */
    abstract protected static function name(): string;

    /**
     * Get the translated description. It's important the class includes the translation function call. This is used by
     * automation to extract translatable strings from the source code.
     *
     * @return string A description shown on the feature settings page. This should provide enough context so that it's
     * clear what effect turning on the feature will have.
     */
    abstract protected static function description(): string;

    /**
     * @return string The options key the feature is stored in.
     */
    protected static function options_key(): string
    {
        return self::OPTIONS_KEY;
    }

    /**
     * This determines whether to include the toggle on the feature settings page. It doesn't prevent a feature from
     * being active if enabled already.
     *
     * @return bool Whether the feature toggle can be used on production.
     */
    public static function show_toggle_production(): bool
    {
        return false;
    }

    /**
     * Not added as abstract as this function will only return true when we decided to remove the feature.
     * So we can avoid having to declare it most of the time.
     *
     * @return bool Whether the feature has become generally available, meaning it's always on for everyone.
     */
    protected static function is_generally_available(): bool
    {
        return false;
    }

    /**
     * Whether the feature is currently active.
     *
     * @return bool Whether the feature is currently active.
     */
    public static function is_active(): bool
    {
        if (static::is_generally_available()) {
            return true;
        }

        $features = get_option(static::options_key());
        $id = static::id();
        $active = isset($features[ $id ]) && $features[ $id ];

        // Filter to allow setting a feature from code, to avoid chicken and egg problem when releasing adaptions to a
        // new feature.
        return (bool) apply_filters("planet4_feature__$id", $active);
    }

    /**
     * The config for CMB2 options page.
     *
     * @return string[] The config.
     */
    public static function get_cmb_field(): array
    {
        return [
            'id' => static::id(),
            'name' => self::dev_prefix('👷') . static::name(),
            'desc' => static::description(),
            'type' => 'checkbox',
        ];
    }

    /**
     * @param string $prefix Prefix to add in case of dev env.
     *
     * @return string A prefix in case this is a dev only toggle.
     */
    private static function dev_prefix(string $prefix): string
    {
        return static::show_toggle_production() ? '' : "$prefix ";
    }

    /**
     * Enable the feature.
     *
     */
    public static function enable(): void
    {
        $settings = get_option(static::options_key(), []);

        $settings[ static::id() ] = 'on';
        update_option(static::options_key(), $settings);
    }
}
