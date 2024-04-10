<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use P4\MasterTheme\Search\Search;
use P4\MasterTheme\ActionPage;
use WP_Query;

/**
 * Content type used for search.
 * Native types (post, page, attachment, etc.).
 * Custom types (p4_action, etc.).
 */
class ContentTypes
{
    public const QUERY_ID = 'ctype';
    public const CONTEXT_ID = 'content_types';

    public const POST = 'post';
    public const PAGE = 'page';
    public const ACTION = 'p4_action';
    public const CAMPAIGN = 'campaign';
    public const ATTACHMENT = 'attachment';
    public const ARCHIVE = 'archive';

    /**
     * Get all content types.
     *
     * @return array<WP_Post_Type>
     */
    public static function get_all(): array
    {
        $config = self::get_config();
        $types = get_post_types(
            [
                'public' => true,
                'exclude_from_search' => false,
            ],
            false
        );

        return array_filter(
            $types,
            function ($type) use ($config) {
                return isset($config[ $type->name ]);
            }
        );
    }

    /**
     * @param bool $include_archive Include archive type.
     * @param bool $include_action Include p4_action type.
     *
     * @return array{string, array{id: int, name: string, results: int}}
     */
    public static function get_filters(
        bool $include_archive = false,
        bool $include_action = false
    ): array {
        $types = self::get_all();
        $config = self::get_config();

        foreach ($types as $name => $type) {
            if (self::ARCHIVE === $name && ! $include_archive) {
                continue;
            }

            if (self::ACTION === $name && ! $include_action) {
                continue;
            }

            $type_data = $config[ $type->name ] ?? null;
            if (! $type_data) {
                continue;
            }

            $filters[ $type_data['id'] ] = [
                'id' => $type_data['id'],
                'slug' => $type_data['slug'],
                'name' => $type_data['label'],
                'results' => 0,
            ];
        }

        return $filters;
    }

    /**
     * Get all content type config for search
     *
     * @return array{string, array{id: int, label: string}}
     */
    public static function get_config(): array
    {
        return [
            self::ATTACHMENT => [
                'id' => 1,
                'label' => __('Document', 'planet4-master-theme'),
                'slug' => self::ATTACHMENT,
            ],
            self::PAGE => [
                'id' => 2,
                'label' => __('Page', 'planet4-master-theme'),
                'slug' => self::PAGE,
            ],
            self::POST => [
                'id' => 3,
                'label' => __('Post', 'planet4-master-theme'),
                'slug' => self::POST,
            ],
            self::CAMPAIGN => [
                'id' => 4,
                'label' => __('Campaign', 'planet4-master-theme'),
                'slug' => self::CAMPAIGN,
            ],
            self::ARCHIVE => [
                'id' => 5,
                'label' => __('Archive', 'planet4-master-theme'),
                'slug' => self::ARCHIVE,
            ],
            self::ACTION => [
                'id' => 6,
                'label' => __('Action', 'planet4-master-theme'),
                'slug' => self::ACTION,
            ],
        ];
    }

    /**
     * @return array{string: id}
     */
    public static function get_ids_map(): array
    {
        $conf = self::get_config();
        return array_merge(...array_map(fn($v, $k) => [$k => $v['id']], $conf, array_keys($conf)));
    }

    public static function apply_to_query(int $value, WP_Query $query): void
    {
        switch ($value) {
            case 1:
                $query->set('post_type', 'attachment');
                $query->set('post_status', 'inherit');
                $query->set('post_mime_type', Search::DOCUMENT_TYPES);
                break;
            case 2:
                $query->set('post_type', 'page');
                $query->set('post_status', 'publish');
                break;
            case 3:
                $query->set('post_type', 'post');
                $query->set('post_status', 'publish');
                break;
            case 4:
                $query->set('post_type', 'campaign');
                $query->set('post_status', 'publish');
                break;
            case 5:
                $query->set('post_type', 'archive');
                break;
            case 6:
                $query->set('post_type', ActionPage::POST_TYPE);
                $query->set('post_status', 'publish');
                break;
            default:
                break;
        }
    }
}
