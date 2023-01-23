<?php

namespace P4\MasterTheme;

use CF\Integration\DefaultConfig;
use CF\Integration\DefaultIntegration;
use CF\Integration\DefaultLogger;
use CF\WordPress\DataStore;
use CF\WordPress\WordPressAPI;
use CF\WordPress\WordPressClientAPI;
use Generator;

/**
 * Purge of Cloudflare cache via its API.
 */
class CloudflarePurger
{
    /**
     * @var WordPressClientAPI
     */
    private $api;

    /**
     * @var ?string
     */
    private $zone_id;

    /**
     * Initiate api.
     */
    public function __construct()
    {
        if (! defined('CLOUDFLARE_PLUGIN_DIR')) {
            define('CLOUDFLARE_PLUGIN_DIR', WP_PLUGIN_DIR . '/cloudflare/');
        }
        require_once CLOUDFLARE_PLUGIN_DIR . 'vendor/autoload.php';

        // The following is just a copy of the plugin's dependency chain, can probably be improved.
        $config = new DefaultConfig(file_get_contents(CLOUDFLARE_PLUGIN_DIR . 'config.json', true));
        $logger = new DefaultLogger($config->getValue('debug'));
        $data_store = new DataStore($logger);
        $integration_api = new WordPressAPI($data_store);
        $integration = new DefaultIntegration($config, $integration_api, $data_store, $logger);

        $this->api = new WordPressClientAPI($integration);
        $this->zone_id = $this->api->getZoneTag(get_option('cloudflare_cached_domain_name'));
    }

    /**
     * Query API to purge given URLs list, by chunks of 30.
     * Generates [result, chunk] for each chunk.
     *
     * @param string[] $urls URLs list.
     * @return Generator [(bool) result, (string[]) urls chunk]
     */
    public function purge(array $urls): Generator
    {
        // Ensure HTTPS on all URLs.
        $urls = array_map(fn($url) => str_replace('http://', 'https://', $url), $urls);
        // 30 is Cloudflare's purge API limit.
        $chunks = array_chunk(array_unique($urls), 30);

        foreach ($chunks as $chunk) {
            yield [
                $this->api->zonePurgeFiles($this->zone_id, $chunk),
                $chunk,
            ];
        }
    }

    /**
     * Purge all URLs found by default search.
     *
     * @return array [result, chunk][]
     */
    public function purge_all()
    {
        $urls = self::get_all_urls();
        return iterator_to_array($this->purge($urls));
    }

    /**
     * Get all URLs from the instance.
     *
     * @param ?array $post_types Specify post types, all editable types are used if null.
     * @return array
     */
    public static function get_all_urls(?array $post_types = null): array
    {

        $ids = get_posts(
            [
                'post_type' => $post_types ?? self::get_all_post_types(),
                'posts_per_page' => - 1,
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'fields' => 'ids',
            ]
        );

        return array_map('get_permalink', $ids);
    }

    /**
     * Get all registered post types that "support blocks".
     *
     * @return string[] All posts types that support blocks.
     */
    private static function get_all_post_types(): array
    {
        $supports_editor = static function ($type) {
            return post_type_supports($type, 'editor');
        };

        return array_filter(get_post_types([ 'show_in_rest' => true ]), $supports_editor);
    }
}
