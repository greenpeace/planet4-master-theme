<?php

/**
 * @package P4GBKS\Rest
 */

 namespace P4\MasterTheme\Admin;

use P4\MasterTheme\SqlParameters;
use WP_REST_Request;
use WP_REST_Server;
use wpdb;

/**
 * Published items.
 *
 * Faster alternative to get_posts(), to return only IDs and titles of published items.
 */
class Published
{
    /**
     * Allowed post types
     *
     */
    public const ALLOWED_TYPES = [ 'post', 'page', 'p4_action' ];

    private wpdb $db;

    private WP_REST_Request $request;

    /**
     * Constructor
     *
     * @param WP_REST_Request $request Request.
     */
    public function __construct(WP_REST_Request $request)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->request = $request;
    }

    /**
     * Permission callback
     */
    public static function permission(): bool
    {
        return current_user_can('edit_pages') || current_user_can('edit_campaigns');
    }

    /**
     * HTTP Method
     */
    public static function methods(): string
    {
        return WP_REST_Server::READABLE;
    }

    /**
     * Get API response data.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function response()
    {
        $types = explode(',', $this->request->get_param('post_type') ?? '');
        $types = array_intersect(self::ALLOWED_TYPES, $types);
        if (empty($types)) {
            return rest_ensure_response([]);
        }

        if (is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            $lang = apply_filters('wpml_current_language', null);
            $query = $this->get_wpml_posts_query($lang, $types);
        } else {
            $query = $this->get_posts_query($types);
        }

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $this->db->get_results($query) ?? [];

        array_walk(
            $results,
            function (&$p): void {
                $p->id = (int) $p->id;
            }
        );

        return rest_ensure_response($results);
    }

    /**
     * Create query for all published items of type asked.
     *
     * @param string[] $types Post types.
     */
    private function get_posts_query(array $types): string
    {
        $params = new SqlParameters();
        $sql = 'SELECT id, post_title
			FROM ' . $params->identifier($this->db->posts) . '
			WHERE post_status = \'publish\'
				AND post_type IN ' . $params->string_list($types) . '
			ORDER BY post_date DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->db->prepare($sql, $params->get_values());
    }

    /**
     * Create a query for all published items with a certain language code.
     *
     * @param string   $lang  Return posts with this language code.
     * @param string[] $types Post types.
     *
     * @return string The prepared query.
     */
    private function get_wpml_posts_query(string $lang, array $types): string
    {
        $icl_types = array_map(
            fn ($t) => 'post_' . $t,
            $types
        );

        $params = new SqlParameters();
        $sql = 'SELECT p.id, p.post_title
			FROM ' . $params->identifier($this->db->posts) . ' p
			JOIN ' . $params->identifier($this->db->prefix . 'icl_translations') . ' t
					ON p.ID = t.element_id
					AND t.element_type IN ' . $params->string_list($icl_types) . '
			WHERE p.post_status = \'publish\'
				AND p.post_type IN ' . $params->string_list($types) . '
				AND t.language_code = ' . $params->string($lang) . '
			ORDER BY p.post_date DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->db->prepare($sql, $params->get_values());
    }
}
