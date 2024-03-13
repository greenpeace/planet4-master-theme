<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add default for Posts page.
 */
class M028MovePageHeaderSideBarOptions extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $args = array(
            'post_type' => 'page',
            'posts_per_page' => -1,
        );

        $pages = get_posts($args);

        foreach ($pages as $page) {
            $page_meta_data = get_post_meta($page->ID);

            $page_header_title = $page_meta_data['p4_title'][0] ?? '';
            $page_header_image = $page_meta_data['background_image'][0] ?? '';
            $page_header_image_id = $page_meta_data['background_image_id'][0] ?? '';
            $page_header_description = $page_meta_data['p4_description'][0] ?? '';
            $page_header_button_title = $page_meta_data['p4_button_title'][0] ?? '';
            $page_header_button_link = $page_meta_data['p4_button_link'][0] ?? '';
            $page_header_button_link_checkbox = $page_meta_data['p4_button_link_checkbox'][0] ?? '';
            $page_header_hide_page_title = $page_meta_data['p4_hide_page_title_checkbox'][0] ?? '';

            $page_template = get_page_template_slug($page->ID);

            // If Sitemap Page, update on Page Title.
            if ('page-templates/sitemap.php' === $page_template) {
                if (empty($page_header_title)) {
                    continue;
                }

                $updated_args = array(
                    'ID' => $page->ID,
                    'post_title' => $page_header_title,
                );

                self::update_meta_options($page_meta_data, $page_header_title, $page);

                wp_update_post($updated_args);

                continue;
            }

            // phpcs:disable Generic.Files.LineLength.MaxExceeded
            if (!empty($page_header_image_id) && !empty($page_header_title)) {
                // Build Page header Pattern
                $content = '
                <!-- wp:planet4-block-templates/page-header {"mediaPosition":"right"} -->
                <!-- wp:group {"align":"full"} -->
                <div class="wp-block-group alignfull">
                <!-- wp:group {"className":"container"} -->
                <div class="wp-block-group container">
                <!-- wp:media-text {"align":"full","mediaPosition":"right","mediaId": ' . $page_header_image_id . ',"mediaLink":"","mediaType":"image","imageFill":false,"className":"is-pattern-p4-page-header is-style-parallax"} -->
                <div class="wp-block-media-text alignfull has-media-on-the-right is-stacked-on-mobile is-pattern-p4-page-header is-style-parallax">

                <div class="wp-block-media-text__content"><!-- wp:group -->
                    <div class="wp-block-group">
                    <!-- wp:heading {"level":1,"placeholder":"Enter title","backgroundColor":"white"} -->
                        <h1 class="wp-block-heading has-white-background-color has-background">
                            ' . $page_header_title . '
                        </h1>
                    <!-- /wp:heading --></div>
                <!-- /wp:group -->

                ' . (!empty($page_header_description) ?
                    '<!-- wp:paragraph {"placeholder":"Enter description","style":{"typography":{"fontSize":"1.25rem"}}} -->
                        <p style="font-size:1.25rem">
                            ' . preg_replace("/<p>(.*?)<\/p>/is", "$1", $page_header_description) . '
                        </p>
                    <!-- /wp:paragraph -->'
                : '') . '

                ' . (!empty($page_header_button_link) ?
                    '<!-- wp:buttons -->
                        <div class="wp-block-buttons">
                            <!-- wp:button {"className":"is-style-cta"} /-->
                        </div>
                        <div class="wp-block-button is-style-cta">
                            <a class="wp-block-button__link wp-element-button"
                                href="' . $page_header_button_link . '"
                                target="' . ($page_header_button_link_checkbox === 'on' ? '_blank' : '') . '"
                                rel="noreferrer noopener"
                            >
                                ' . $page_header_button_title . '
                            </a>
                        </div>
                    <!-- /wp:buttons -->'
                : '') . '

                </div>
                <figure class="wp-block-media-text__media">
                    <img
                        src="' . $page_header_image . '"
                        alt=""
                        class="wp-image-' . $page_header_image_id . ' size-full"
                    />
                </figure>
                </div>
                <!-- /wp:media-text -->
                </div>
                <!-- /wp:group -->
                </div>
                <!-- /wp:group -->
                <!-- /wp:planet4-block-templates/page-header -->
                ';

                $updated_post_content = $content . $page->post_content;

                self::update_meta_options($page_meta_data, $page_header_title, $page, true);

                $post_args = array(
                    'post_content' => $updated_post_content,
                    'ID' => $page->ID,
                );

                wp_update_post($post_args);

                continue;
            }

            if (empty($page_header_image_id) || empty($page_header_title)) {
                if (
                    empty($page_header_image_id)
                    && empty($page_header_title)
                    && empty($page_header_description)
                    && empty($page_header_button_title)
                ) {
                    continue;
                }

                $content = '
                    <!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} -->
                    <div class="wp-block-group">
                    ' . (!empty($page_header_description) ?
                            '<!-- wp:paragraph -->
                                <p>' . preg_replace("/<p>(.*?)<\/p>/is", "$1", $page_header_description) . '</p>
                            <!-- /wp:paragraph -->'
                    : ''
                    ) . '

                    ' . (!empty($page_header_button_title) ?
                            '<!-- wp:buttons -->
                            <div class="wp-block-buttons"><!-- wp:button -->
                            <div class="wp-block-button">
                                <a class="wp-block-button__link wp-element-button"
                                    href="' . $page_header_button_link . '"
                                    target="' . ($page_header_button_link_checkbox === 'on' ? '_blank' : '') . '"
                                    rel="noreferrer noopener"
                                >
                                    ' . $page_header_button_title . '
                                </a>
                            </div>
                            <!-- /wp:button --></div>
                            <!-- /wp:buttons -->'

                    : '') . '
                    </div>
                    <!-- /wp:group -->
                ';

                $updated_post_content = $content . $page->post_content;
                $post_title = empty($page_header_title) ? $page->post_title : $page_header_title;

                self::update_meta_options($page_meta_data, $page_header_title, $page);

                $post_args = array(
                    'post_content' => $updated_post_content,
                    'post_title' => $post_title,
                    'ID' => $page->ID,
                );

                wp_update_post($post_args);

                continue;
            }
        }
    }

    /**
     * Update the Post meta Options.
     * @param array   $page_meta_data To check meta keys.
     * @param object  $page That the meta options are gottne from.
     * @param string  $page_header_title Page Header title for current Page.
     * @param bool    $hide_page_title Check if hide page title option should be enabled.
     */
    private static function update_meta_options(array $page_meta_data, string $page_header_title, object $page, ?bool $hide_page_title = null): void
    {
        $keys_to_update = array(
            'p4_title',
            'p4_subtitle',
            'background_image',
            'background_image_id',
            'p4_description',
            'p4_button_title',
            'p4_button_link',
            'p4_button_link_checkbox',
            'p4_hide_page_title_checkbox',
        );

        // Update specific meta values to empty strings
        foreach ($keys_to_update as $meta_key) {
            if ($meta_key === 'p4_hide_page_title_checkbox' && !empty($page_header_title)) {
                if (isset($page_meta_data[$meta_key]) && in_array('on', $page_meta_data[$meta_key])) {
                    continue;
                }

                //  If Page is Sitemap or Hide Page Title param is not set, then do nothing
                if (!isset($hide_page_title)) {
                    continue;
                }

                // Update meta value with string 'on'
                update_post_meta($page->ID, $meta_key, 'on');
            } else {
                update_post_meta($page->ID, $meta_key, '');
            }
        }
    }
}
// phpcs:enable Generic.Files.LineLength.MaxExceeded
