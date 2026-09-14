<?php

/**
 * @package P4\MasterTheme\Admin
 */

namespace P4\MasterTheme\Admin;

/**
 * This class is just a place for add_endpoints to live.
 */
class RestAndCacheListingRoutes
{
    const LISTING_CACHE_ROUTES = [ '/wp/v2/posts', '/wp/v2/p4_action' ];

    const LISTING_CACHE_GROUP  = 'planet4_listing';

    /**
     * Initialize class.
     */
    public function load(): void
    {
        add_action( 'save_post', [$this, 'bump_listing_cache_version'] );
        add_action( 'deleted_post', [$this, 'bump_listing_cache_version'] );
        add_action( 'edited_terms', [$this, 'bump_listing_cache_version'] );
        add_action( 'set_object_terms', [$this, 'bump_listing_cache_version'] );
        add_filter( 'rest_post_dispatch', [$this, 'store_response'], 10, 3 );
        add_filter( 'rest_pre_dispatch', [$this, 'serve_response'], 10, 3 );
    }

    /**
     * Builds a cache key from the route and query params, so each unique
     * combination of page/filters/embed args gets its own cache entry.
     */
    private function get_listing_cache_key( \WP_REST_Request $request ) {
        $version = $this->get_listing_cache_version();
        $params  = $request->get_query_params();
        ksort( $params ); // Ensure key order doesn't produce different keys for the same query.

        return 'listing_' . $version . '_' . md5( $request->get_route() . '?' . http_build_query( $params ) );
    }

    private function get_listing_cache_version() {
        $version = wp_cache_get( 'version', self::LISTING_CACHE_GROUP );
        return false !== $version ? $version : 1;
    }

    public function bump_listing_cache_version() {
        wp_cache_set( 'version', $this->get_listing_cache_version() + 1, self::LISTING_CACHE_GROUP, 0 ); // 0 = no expiry
    }

    /**
     * Serves a cached response before WordPress runs the real REST query,
     * for the listing endpoints only.
     */
    public function serve_response ( $result, $server, $request ) {
        if ( ! in_array( $request->get_route(), self::LISTING_CACHE_ROUTES, true ) || 'GET' !== $request->get_method() ) {
            return $result;
        }

        $cached = wp_cache_get( $this->get_listing_cache_key( $request ), self::LISTING_CACHE_GROUP );
        if ( false === $cached ) {
            return $result; // Cache miss: let the real request run.
        }

        $response = new \WP_REST_Response( $cached['data'] );
        foreach ( $cached['headers'] as $name => $value ) {
            if ( null !== $value ) {
                $response->header( $name, $value );
            }
        }

        $response->header( 'X-Cache-Status', 'HIT' );

        return $response;
    }

    /**
     * Stores the response after a real (cache-miss) request completes.
     */
    public function store_response ( $response, $server, $request ) {
        if ( ! in_array( $request->get_route(), self::LISTING_CACHE_ROUTES, true ) || 'GET' !== $request->get_method() ) {
            return $response;
        }

        if ( is_wp_error( $response ) || ! ( $response instanceof \WP_REST_Response ) ) {
            return $response;
        }

        wp_cache_set(
            $this->get_listing_cache_key( $request ),
            [
                'data'    => $response->get_data(),
                'headers' => [
                    'X-WP-Total'      => $response->get_headers()['X-WP-Total'] ?? null,
                    'X-WP-TotalPages' => $response->get_headers()['X-WP-TotalPages'] ?? null,
                ],
            ],
            self::LISTING_CACHE_GROUP,
            DAY_IN_SECONDS
        );

        $response->header( 'Cache-Control', 'public, max-age=120, stale-while-revalidate=60' );
        $response->header( 'X-Cache-Status', 'MISS' );

        return $response;
    }
}
