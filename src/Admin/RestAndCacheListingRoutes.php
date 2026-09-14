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
        error_log( 'SERVE route=' . $request->get_route() . ' method=' . $request->get_method() );

        if ( ! in_array( $request->get_route(), self::LISTING_CACHE_ROUTES, true ) || 'GET' !== $request->get_method() ) {
            error_log( 'SERVE: route check failed, bailing' );
            return $result;
        }

        $key = $this->get_listing_cache_key( $request );
        error_log( 'SERVE key=' . $key );

        $cached = wp_cache_get( $key, self::LISTING_CACHE_GROUP );
        error_log( 'SERVE cached=' . var_export( $cached, true ) );

        if ( false === $cached ) {
            return $result;
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
        error_log( 'STORE route=' . $request->get_route() . ' method=' . $request->get_method() );

        if ( ! in_array( $request->get_route(), self::LISTING_CACHE_ROUTES, true ) || 'GET' !== $request->get_method() ) {
            error_log( 'STORE: route check failed, bailing' );
            return $response;
        }

        if ( is_wp_error( $response ) || ! ( $response instanceof \WP_REST_Response ) ) {
            error_log( 'STORE: not a valid response, bailing' );
            return $response;
        }

        $key = $this->get_listing_cache_key( $request );
        error_log( 'STORE key=' . $key );

        $stored = wp_cache_set(
            $key,
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

        error_log( 'STORE result=' . var_export( $stored, true ) );

        $test = wp_cache_get( $key, self::LISTING_CACHE_GROUP );

        error_log(
            'STORE immediate get=' . var_export( $test, true )
        );

        error_log(
            'STORE persistent=' . ( wp_using_ext_object_cache() ? 'YES' : 'NO' )
        );

        $response->header( 'Cache-Control', 'public, max-age=120, stale-while-revalidate=60' );
        $response->header( 'X-Cache-Status', 'MISS' );

        return $response;
    }
}
