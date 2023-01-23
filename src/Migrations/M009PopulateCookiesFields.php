<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;
use P4GBKS\Search\BlockSearch;
use WP_Block_Parser;

/**
 * Copy of Cookies block data to Cookies settings in Planet4 > Cookies.
 */
class M009PopulateCookiesFields extends MigrationScript
{
    /**
     * Extract cookies data from Cookis block to update planet4_options settings.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     * @return void
     */
    public static function execute(MigrationRecord $record): void
    {
        if (! class_exists('P4GBKS\Search\BlockSearch')) {
            echo 'P4 Gutenberg plugin is not available.';
            return;
        }

        $search = new BlockSearch();
        $parser = new WP_Block_Parser();
        $block_name = 'planet4-blocks/cookies';

        $site_locale = get_locale();
        $multilingual = is_plugin_active('sitepress-multilingual-cms/sitepress.php');

        $post_ids = $search->get_posts_with_block($block_name);
        if (empty($post_ids)) {
            return;
        }

        $args = [
            'include' => $post_ids,
            'post_type' => [ 'page' ],
            'post_status' => [ 'publish' ],
        ];
        $posts = get_posts($args) ?? [];

        if (empty($posts)) {
            return;
        }

        $settings_keys = [
            'titles' => [
                'necessary_cookies_name',
                'analytical_cookies_name',
                'all_cookies_name',
            ],
            'descriptions' => [
                'necessary_cookies_description',
                'analytical_cookies_description',
                'all_cookies_description',
            ],
        ];

        // We can have multiple posts for multilingual sites.
        foreach ($posts as $post) {
            if (empty($post->post_content)) {
                continue;
            }

            // Post locale is used to update localized options on multilingual sites.
            $post_locale = $site_locale;
            if ($multilingual) {
                $lang_details = apply_filters('wpml_post_language_details', null, $post->ID);
                $post_locale = $lang_details['locale'] ?? $post_locale;
            }

            // Go throught the post blocks to find the planet4-blocks/cookies one.
            $blocks = $parser->parse($post->post_content);
            foreach ($blocks as $block) {
                // Skip other blocks.
                if (! isset($block['blockName']) || $block['blockName'] !== $block_name) {
                    continue;
                }

				echo 'Parsing post ', $post->ID, ', lang ', $post_locale, "\n"; // phpcs:ignore

                // Gathering the data we want from this block.
                $block_settings = [];
                foreach ($block['attrs'] as $key => $value) {
                    $value = trim($value);
                    if (empty($value)) {
                        continue;
                    }

                    if (in_array($key, $settings_keys['titles'], true)) {
                        $block_settings[ $key ] = wp_strip_all_tags($value, true);
                    }
                    if (in_array($key, $settings_keys['descriptions'], true)) {
                        $block_settings[ $key ] = $value;
                    }
                }

                // No data, skip this update.
                if (empty($block_settings)) {
                    continue;
                }

                // Updating planet4_options.
                // Multilingual sites have localized planet4_options, updating accordingly.
                $result = true;
                if ($multilingual) {
                    global $sitepress;
                    $lang_code = $sitepress->get_language_code_from_locale($post_locale);

                    if ($sitepress->get_default_language() === $lang_code) {
                        // Default language uses default options.
                        $result &= self::update_cookies_settings($block_settings);
                    } elseif ($sitepress->is_active_language($lang_code)) {
                        // Other active languages have a localized option field.
                        $result &= self::update_localized_cookies_settings($block_settings, $lang_code);
                    }
                } else {
                    $result &= self::update_cookies_settings($block_settings);
                }

                echo $result
                    ? "Update successful\n"
					: "Update wasn't executed\n"; // phpcs:ignore
            }
        }
    }

    /**
     * Update cookies settings in planet4_options settings.
     *
     * @param array $cookies_settings Cookies settings.
     *
     * @return bool Update successful.
     */
    public static function update_cookies_settings(array $cookies_settings): bool
    {
        $settings = get_option(Settings::KEY);

        echo 'Updated settings: '
			, print_r( array_merge( $settings, $cookies_settings ), true ) // phpcs:ignore
            , "\n";

        return update_option(Settings::KEY, array_merge($settings, $cookies_settings));
    }

    /**
     * Update cookies settings in localized planet4_options_$lang settings.
     *
     * @param array  $cookies_settings Cookies settings.
     * @param string $lang             Language code.
     *
     * @return bool Update successful.
     */
    public static function update_localized_cookies_settings(
        array $cookies_settings,
        string $lang
    ): bool {
        global $wpdb;

		$settings = unserialize( // phpcs:ignore
            $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT option_value
						FROM ' . $wpdb->options . '
						WHERE option_name = %s
						LIMIT 1',
                    Settings::KEY . '_' . $lang
                )
            )
        );

		echo 'Updated localized ' . $lang . ' settings: ' // phpcs:ignore
			, print_r( array_merge( $settings, $cookies_settings ), true ) // phpcs:ignore
            , "\n";

        return $wpdb->update(
            $wpdb->options,
			[ 'option_value' => serialize( array_merge( $settings, $cookies_settings ) ) ], // phpcs:ignore
            [ 'option_name' => Settings::KEY . '_' . $lang ]
        ) !== false;
    }
}
