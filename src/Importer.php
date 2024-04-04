<?php

namespace P4\MasterTheme;

/**
 * Add missing functionality to WordPress import.
 */
class Importer
{
    /**
     * Old and new attachment ids mapping var
     *
     * @var array $attachment_mapping
     */
    private array $attachment_mapping = [];

    /**
     * AutoLoad Hooks
     * */
    public function __construct()
    {
        add_action('wp_import_insert_post', [ $this, 'update_attachements' ], 10, 4);
        add_filter('wp_import_post_terms', [ $this, 'filter_wp_import_post_terms' ], 10, 3);
        add_filter('wp_import_post_meta', [ $this, 'process_campaign_metas' ]);
        add_filter('wp_import_post_data_processed', [ $this, 'set_imported_posts_as_drafts' ], 10, 1);
        add_action('import_end', [ $this, 'action_import_end' ], 10, 0);
        add_filter('wp_import_post_meta', [ $this, 'skip_duplicate_postmeta_import' ], 10, 2);

        add_action(
            'wp_import_insert_post',
            function ($post_id): void {
                $link = get_edit_post_link($post_id);
                $title = esc_html(get_the_title($post_id));
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo "successfully imported <a href=\"$link\">$title</a>\n";
            }
        );
    }

    /**
     * Filter the old attachement Ids and replace them with the newly imported attachment Ids.
     *
     * @param int $post_id Post ID.
     * @param int $original_post_id Original Post ID.
     * @param array   $postdata Post data array.
     * @param array   $post Post array.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     */
    public function update_attachements(int $post_id, int $original_post_id, array $postdata, array $post): void
    {
        $post_content = $post['post_content'];
        $filter_data = [];

        $blocks = parse_blocks($post_content);
        foreach ($blocks as $block) {
            // Fetch the attachement id/s from block fields.
            switch ($block['blockName']) {
                case 'planet4-blocks/enform':
                    $filter_data[] = isset($block['attrs']['background'])
                        ? 'background":' . $block['attrs']['background'] : '';
                    break;

                case 'planet4-blocks/happypoint':
                    $filter_data[] = isset($block['attrs']['id']) ? 'id":' . $block['attrs']['id'] : '';
                    break;

                case 'planet4-blocks/media-video':
                    $filter_data[] = isset($block['attrs']['video_poster_img'])
                        ? 'video_poster_img":' . $block['attrs']['video_poster_img'] : '';
                    break;

                case 'planet4-blocks/gallery':
                    $filter_data[] = isset($block['attrs']['multiple_image'])
                        ? 'multiple_image":"' . $block['attrs']['multiple_image'] : '';
                    break;

                case 'planet4-blocks/carousel-header':
                    if (isset($block['attrs']['slides'])) {
                        foreach ($block['attrs']['slides'] as $slide) {
                            $filter_data[] = 'image":' . $slide['image'];
                        }
                    }
                    break;

                case 'planet4-blocks/split-two-columns':
                    $filter_data[] = isset($block['attrs']['issue_image'])
                        ? 'issue_image":' . $block['attrs']['issue_image'] : '';
                    $filter_data[] = isset($block['attrs']['tag_image'])
                        ? 'tag_image":' . $block['attrs']['tag_image'] : '';
                    break;

                case 'planet4-blocks/columns':
                    if (isset($block['attrs']['columns'])) {
                        foreach ($block['attrs']['columns'] as $column) {
                            $filter_data[] = 'attachment":' . $column['attachment'];
                        }
                    }
                    break;

                case 'planet4-blocks/social-media-cards':
                    if (isset($block['attrs']['cards'])) {
                        foreach ($block['attrs']['cards'] as $card) {
                            $filter_data[] = 'image_id":' . $card['image_id'];
                        }
                    }
                    break;

                case 'planet4-blocks/take-action-boxout':
                    $filter_data[] = isset($block['attrs']['background_image'])
                        ? 'background_image":' . $block['attrs']['background_image'] : '';
                    break;

                case 'core/image':
                    if (isset($block['attrs']['id'])) {
                        $filter_data[] = 'id":' . $block['attrs']['id'];
                        $filter_data[] = 'wp-image-' . $block['attrs']['id'];
                    }
                    break;

                case 'core/media-text':
                    if (isset($block['attrs']['mediaId'])) {
                        $filter_data[] = 'mediaId":' . $block['attrs']['mediaId'];
                    }

                    if (isset($block['attrs']['mediaLink'])) {
                        $filter_data[] = 'mediaLink":' . $block['attrs']['mediaLink'];
                    }

                    if (isset($block['attrs']['mediaType'])) {
                        $filter_data[] = 'mediaType":' . $block['attrs']['mediaType'];
                    }
                    break;
            }
        }

        $filter_data = array_unique($filter_data);

        // Check if attachement mapping var is empty and update it.
        if (empty($this->attachment_mapping)) {
            global $wpdb;

			// phpcs:disable
			$sql          = 'SELECT post_id, meta_value FROM %1$s WHERE meta_key = \'_wp_attachment_metadata\' AND meta_value LIKE \'%imported_attachment_id%\'';
			$prepared_sql = $wpdb->prepare( $sql, [ $wpdb->postmeta ] );
			$result       = $wpdb->get_results( $prepared_sql );
			// phpcs:enable

            foreach ($result as $attachment_metadata) {
                $new_attachment_id = $attachment_metadata->post_id;
                $attachment_data = maybe_unserialize($attachment_metadata->meta_value);
                $old_attachment_id = $attachment_data['image_meta']['imported_attachment_id'];
                $this->attachment_mapping[ $old_attachment_id ] = $new_attachment_id;
            }
        }

        // Old ids and new ids(attachment ids) string data mapping.
        $filter_data_mapping = [];
        foreach ($filter_data as $filter_str) {
            if (strpos($filter_str, 'multiple_image') !== false || strpos($filter_str, 'ids') !== false) {
                $new_filter_str = $filter_str;
                preg_match_all('#(\d+)#', $new_filter_str, $matches, PREG_SET_ORDER);

                foreach ($matches as $old_id) {
                    if (!isset($this->attachment_mapping[ $old_id[0] ])) {
                        continue;
                    }

                    $new_filter_str = str_replace($old_id[0], $this->attachment_mapping[ $old_id[0] ], $new_filter_str);
                }
                $filter_data_mapping[] = [ $filter_str, $new_filter_str ];
            } else {
                foreach ($this->attachment_mapping as $old_id => $new_id) {
                    $updated_str = str_replace($old_id, $new_id, $filter_str);
                    if ($updated_str === $filter_str) {
                        continue;
                    }

                    $filter_data_mapping[] = [ $filter_str, $updated_str ];
                }
            }
        }

        // Search replace filter data string(with old attachement ids) with updated attachment ids string.
        foreach ($filter_data_mapping as $filter_data) {
            $post_content = str_replace($filter_data[0], $filter_data[1], $post_content);
        }

        // Update Page header fields background image in postmeta.
        $postmeta = [];
        if (isset($post['postmeta'])) {
            foreach ($post['postmeta'] as $metakey => $metadata) {
                if ('background_image_id' !== $metadata['key']) {
                    continue;
                }

                if (!isset($this->attachment_mapping[ $metadata['value'] ])) {
                    continue;
                }

                $post['postmeta'][ $metakey ]['value'] = $this->attachment_mapping[ $metadata['value'] ];
            }
            $postmeta = $post['postmeta'];
        }

        $updated_post = [
            'ID' => $post_id,
            'post_title' => $post['post_title'],
            'post_content' => $post_content,
            'postmeta' => $postmeta,
        ];
        wp_update_post(wp_slash($updated_post));
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Update attachement source ID in attachment metadata for future data mapping purpose.
     *
     * @param array $post_terms Post term array.
     * @param int $post_id Post ID.
     * @param array $post Post object.
     * @return array $post_terms Post term array.
     */
    public function filter_wp_import_post_terms(array $post_terms, int $post_id, array $post): array
    {
        if ('attachment' === $post['post_type']) {
            $attachment_metadata = wp_get_attachment_metadata($post_id);
            $attachment_metadata['image_meta']['imported_attachment_id'] = $post['post_id'];
            wp_update_attachment_metadata($post_id, $attachment_metadata);

            if (! empty($this->attachment_mapping)) {
                $this->attachment_mapping = [];
            }
        }

        return $post_terms;
    }

    /**
     * Clean the imported attachment metadata.
     */
    public function action_import_end(): void
    {
        global $wpdb;

		// phpcs:disable
		$sql          = 'SELECT post_id, meta_value FROM %1$s WHERE meta_key = \'_wp_attachment_metadata\' AND meta_value LIKE \'%imported_attachment_id%\'';
		$prepared_sql = $wpdb->prepare( $sql, [ $wpdb->postmeta ] );
		$result       = $wpdb->get_results( $prepared_sql );
		// phpcs:enable

        foreach ($result as $attachment_metadata) {
            $attachment_data = maybe_unserialize($attachment_metadata->meta_value);
            unset($attachment_data['image_meta']['imported_attachment_id']);
            wp_update_attachment_metadata($attachment_metadata->post_id, $attachment_data);
        }
    }

    /**
     * Filter for wp_import_post_data_processed.
     * Set imported posts as drafts.
     *
     * @param array $postdata Post data that can be filtered.
     *
     * @return array
     */
    public function set_imported_posts_as_drafts(array $postdata): array
    {
        $postdata['post_status'] = 'draft';

        return $postdata;
    }

    /**
     * 1. Exclude campaign style meta fields if the NRO has this setting enabled.
     *
     * 2. Populate the "theme" meta field with the contents of its previous name if the new field doesn't exist yet.
     *
     * @param array $post_meta The to be imported post meta fields.
     *
     * @return array The normalized post meta fields.
     */
    public function process_campaign_metas(array $post_meta): array
    {
        $p4_options = get_option('planet4_options');
        // 1. Exclude style fields the option for that is set or if it's passed in the form data.
        if (
            ! empty($p4_options['campaigns_import_exclude_style'])
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            || ! empty($_POST['campaigns_import_exclude_style'])
        ) {
            // Also exclude the old attribute as the code still falls back to it.
            $excluded_keys = array_merge(PostCampaign::META_FIELDS, [ '_campaign_page_template' ]);
            foreach ($post_meta as $index => $meta) {
                if (!in_array($meta['key'], $excluded_keys, true)) {
                    continue;
                }

                unset($post_meta[ $index ]);
            }
        } else {
            // 2. Populate the new `theme` field and unset the old `_campaign_page_template if the new doesn't exist.
            foreach ($post_meta as $index => $meta) {
                if ('_campaign_page_template' === $meta['key']) {
                    $old_theme = $meta['value'];
                    unset($post_meta[ $index ]);
                } elseif ('theme' === $meta['key']) {
                    $new_theme = $meta['value'];
                }
            }
            if (isset($old_theme) && ! isset($new_theme)) {
                $post_meta[] = [
                    'key' => 'theme',
                    'value' => $old_theme,
                ];
            }
        }

        return $post_meta;
    }

    /**
     * Skip already existing postmeta data from import.
     *
     * @param array   $postmeta The to be imported post meta fields.
     * @param int $post_id Post ID.
     *
     * @return array The cleaned post meta fields.
     */
    public function skip_duplicate_postmeta_import(array $postmeta, int $post_id): array
    {
        $existing_postmeta = get_post_meta($post_id);

        return array_filter(
            $postmeta,
            function ($meta) use ($existing_postmeta) {
                return ! array_key_exists($meta['key'], $existing_postmeta);
            }
        );
    }
}
