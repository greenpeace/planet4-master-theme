<?php

/**
 * Block search SQL query
 *
 * @package P4BKS\Search\Block
 */

namespace P4\MasterTheme\BlockReportSearch\Block\Sql;

use P4\MasterTheme\BlockReportSearch\Block;
use P4\MasterTheme\SqlParameters;
use wpdb;

/**
 * SQL implementation of Query interface
 */
class SqlQuery implements Block\Query
{
    private wpdb $db;

    /**
     * @param wpdb|null $db Database singleton.
     */
    public function __construct(?wpdb $db = null)
    {
        global $wpdb;

        $this->db = $db ? $db : $wpdb;
    }

    /**
     * @param Block\Query\Parameters ...$params_list Query parameters.
     * @return int[] List of posts IDs.
     */
    public function get_posts(Block\Query\Parameters ...$params_list): array
    {
        $query = $this->get_sql_query(...$params_list);
        $results = $this->db->get_results($query);

        return array_map(
            function ($r) {
                return (int) $r->ID;
            },
            $results
        );
    }

    /**
     * @param Block\Query\Parameters ...$params_list Query parameters.
     * @return string SQL query string
     * @throws \UnexpectedValueException Empty prepared query.
     */
    private function get_sql_query(Block\Query\Parameters ...$params_list): string
    {
        // Prepare query parameters.
        $like = [];
        $status = [];
        $type = [];
        $order = [];
        foreach ($params_list as $params) {
            $like[] = ( new Like($params) )->__toString();
            $status = array_merge($status, $params->post_status() ?? []);
            $type = array_merge($type, $params->post_type() ?? []);
            $order = array_merge($order, $params->order() ?? []);
        }
        $status = array_unique(array_filter($status));
        $type = array_unique(array_filter($type));
        $order = $this->parse_order(array_unique(array_filter($order)));

        // Prepare query.
        $sql_params = new SqlParameters();
        $sql = 'SELECT ID
			FROM ' . $sql_params->identifier($this->db->posts) . '
			WHERE post_status IN ' . $sql_params->string_list($status);
        if (! empty($type)) {
            $sql .= ' AND post_type IN ' . $sql_params->string_list($type);
        }
        foreach ($like as $l) {
            $sql .= ' AND post_content LIKE ' . $sql_params->string($l) . ' ';
        }
        if (! empty($order)) {
            $sql .= ' ORDER BY ' . implode(',', $order);
        }

        $query = $this->db->prepare($sql, $sql_params->get_values());
        if (empty($query)) {
            throw new \UnexpectedValueException('Search query is invalid');
        }

        return $query;
    }

    /**
     * Parse and filter order parameter
     *
     * @param string[] $order List of sort columns.
     * @return string[]
     */
    private function parse_order(array $order): array
    {
        $parsed = [];
        foreach ($order as $k) {
            switch ($k) {
                case 'block_type':
                    break;
                case 'post_id':
                    $parsed[] = 'ID';
                    break;
                default:
                    $parsed[] = $k;
            }
        }
        return $parsed;
    }
}
