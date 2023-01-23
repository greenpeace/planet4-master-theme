<?php

namespace P4\MasterTheme\ImageArchive;

use P4\MasterTheme\Exception\RemoteCallFailed;
use WP_Http;

/**
 * Authenticate to the greenpeace media API and query image data.
 */
class ApiClient
{
    private const BASE_URL = 'https://media.greenpeace.org';

    private const AUTH_URL = self::BASE_URL . '/API/Authentication/v1.0/Login';

    private const SEARCH_URL = self::BASE_URL . '/API/search/v3.0/search';

    private const LIST_FIELDS_URL = self::BASE_URL . '/API/search/v3.0/ListFields';

    private const LIST_CRITERIA_URL = self::BASE_URL . '/API/search/v3.0/ListCriteria';

    private const TOKEN_CACHE_KEY = 'ml_auth_token';

    private const RESPONSE_TIMEOUT = 10;

    private const MEDIAS_PER_PAGE = 50;

    private const DEFAULT_PARAMS = [
        'query' => '(Mediatype:Image)',
        'fields' => 'MediaEncryptedIdentifier,Title,Caption,CoreField.Copyright,Path_TR1,Path_TR1_COMP_SMALL,Path_TR7,Path_TR4,Path_TR1_COMP,Path_TR2,Path_TR3,SystemIdentifier,original-language-title,original-language-description,original-language,restrictions,copyright,MediaDate,CreatedDate,EditDate',
        'countperpage' => self::MEDIAS_PER_PAGE,
        'format' => 'json',
        'pagenumber' => 1,
    ];

    /**
     * @var string The temporary authentication token.
     */
    private string $token;

    /**
     * ApiClient constructor.
     *
     * @param string $token The temporary authentication token.
     */
    private function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Use token from the cache to authenticate, or if none is present fetch a new one with the credentials
     * from the settings.
     *
     * @return static Authenticated instance.
     * @throws RemoteCallFailed Authentication failed.
     */
    public static function from_cache_or_credentials(): self
    {
        $cached_token = get_transient(self::TOKEN_CACHE_KEY);

        if (false !== $cached_token) {
            return new self($cached_token);
        }

        return self::from_settings();
    }

    /**
     * Authenticate with the credentials from the settings.
     *
     * @return static Authenticated instance.
     * @throws RemoteCallFailed Authentication failed.
     */
    public static function from_settings(): self
    {
        $p4ml_settings = get_option('p4ml_main_settings');

        return self::from_credentials($p4ml_settings['p4ml_api_username'], $p4ml_settings['p4ml_api_password']);
    }

    /**
     * Fetch a token with provided credentials and use it.
     *
     * @param string $username The username of the API account.
     * @param string $password The password of the API account.
     *
     * @return static Authenticated instance.
     * @throws RemoteCallFailed Authentication failed.
     */
    public static function from_credentials(string $username, string $password): self
    {
        $token = self::fetch_token($username, $password);

        return new self($token);
    }

    /**
     * Call the authentication endpoint with credentials to fetch a token.
     *
     * @param string $username The username of the API account.
     * @param string $password The password of the API account.
     *
     * @return string The authentication token.
     * @throws RemoteCallFailed Authentication failed.
     */
    private static function fetch_token(string $username, string $password): string
    {
        $response = wp_safe_remote_post(
            self::AUTH_URL,
            [
                'body' => [
                    'Login' => $username,
                    'Password' => $password,
                    'format' => 'json',
                ],
                'timeout' => self::RESPONSE_TIMEOUT,
            ]
        );
        // Authentication failure.
        if (is_wp_error($response)) {
            $response = $response->get_error_message() . ' ' . $response->get_error_code();
        } elseif (WP_Http::ACCEPTED !== $response['response']['code']) {
            $response = $response['response']['message'] . ' ' . $response['response']['code'];
        }

        if (! is_array($response) || empty($response['body'])) {
            throw new RemoteCallFailed("Unable to authenticate user {$username}");
        }
        // Communication with ML API is authenticated.
        $body = json_decode($response['body'], true);
        $token = $body['APIResponse']['Token'];

        // Time period in seconds to keep the ml_auth_token before refreshing. Typically 1 hour.
        $expiration_seconds = ( $body['APIResponse']['TimeoutPeriodMinutes'] ?? 60 ) * 60;

        set_transient(self::TOKEN_CACHE_KEY, $token, $expiration_seconds);

        return $token;
    }

    /**
     * Call the API to get specific images.
     *
     * @param array $ids The ids of the desired images.
     *
     * @return Image[]|null Data for these images.
     * @throws RemoteCallFailed Failed to fetch images.
     */
    public function get_selection(array $ids): ?array
    {
        $params = [
            'query' => 'SystemIdentifier: ' . implode(' OR ', $ids),
        ];

        return $this->fetch_images($params);
    }

    /**
     * Search for images that satisfy provided params.
     *
     * @param array $additional_params Supplement or override default parameters.
     *
     * @return Image[]|null The matching images.
     * @throws RemoteCallFailed Failed to fetch images.
     */
    public function fetch_images(array $additional_params = []): ?array
    {
        $params = array_merge(self::DEFAULT_PARAMS, $additional_params, $this->token_param());

        $url = add_query_arg($params, self::SEARCH_URL);

        $response = wp_remote_get(
            $url,
            [
                'timeout' => self::RESPONSE_TIMEOUT,
            ]
        );

        if (is_wp_error($response) || WP_Http::OK !== $response['response']['code']) {
            // Maybe will throw exception here.
            throw new RemoteCallFailed(
                is_wp_error($response)
                    ? $response->get_error_message()
                    : ( $response['body'] ?? 'Unknown error.' )
            );
        }
        $response = json_decode($response['body'], true);

        $images_in_wordpress = self::get_images_in_wordpress($response);

        return Image::all_from_api_response($response, $images_in_wordpress);
    }

    /**
     * Fetch all fields that can be requested from the API + description and examples.
     *
     * @return array All fields that can be requested from the API + description and examples.
     */
    public function show_fields(): array
    {
        $url = add_query_arg(array_merge($this->token_param(), [ 'format' => 'json' ]), self::LIST_FIELDS_URL);

        $response = wp_remote_get(
            $url,
            [
                'timeout' => self::RESPONSE_TIMEOUT,
            ]
        );

        $response = json_decode($response['body'], true);

        return $response['APIResponse']['Metadata'];
    }

    /**
     * List criteria that can be used in API queries.
     *
     * @return array List of criteria that can be used in API queries.
     */
    public function show_criteria(): array
    {
        $url = add_query_arg(array_merge($this->token_param(), [ 'format' => 'json' ]), self::LIST_CRITERIA_URL);

        $response = wp_remote_get(
            $url,
            [
                'timeout' => self::RESPONSE_TIMEOUT,
            ]
        );

        $response = json_decode($response['body'], true);

        return $response['APIResponse'];
    }

    /**
     * Get the ids from the api response so we can know which ones are already in WP before creating the Image
     * representation. That way we don't need to execute a query for each image.
     *
     * @param array $api_data The API data from which we extract the identifiers.
     *
     * @return string[] Indexed array with the WordPress ID of all images that are in WordPress
     */
    private static function get_images_in_wordpress(array $api_data): array
    {
        global $wpdb;
        $images = $api_data['APIResponse']['Items'] ?? [];

        $ids = array_map(
            static function ($image) {
                return $image['SystemIdentifier'];
            },
            $images
        );

        $sql = '
SELECT p.id, m.meta_value
FROM %1$s p JOIN %2$s m ON m.post_id = p.id
WHERE m.meta_key = "' . Image::ARCHIVE_ID_META_KEY . '" AND m.meta_value IN (' . generate_list_placeholders($ids, 3, 's') . ')';

        $prepared = $wpdb->prepare($sql, array_merge([ $wpdb->posts, $wpdb->postmeta ], $ids)); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $results = $wpdb->get_results($prepared, ARRAY_A);//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Return as indexed array to make lookups easier.
        $indexed = [];
        foreach ($results as $result) {
            $indexed[ $result['meta_value'] ] = (int) $result['id'];
        }

        return $indexed;
    }

    /**
     * Get the token with the right array key.
     *
     * @return string[] Array with the token key and value.
     */
    private function token_param(): array
    {
        return [ 'token' => $this->token ];
    }
}
