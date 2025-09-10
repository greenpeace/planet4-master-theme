<?php

namespace P4\MasterTheme;

use WP_Term;

/**
 * Class Campaigns
 */
class Campaigns
{
    /**
     * Taxonomy
     *
     */
    private string $taxonomy = 'post_tag';
    /**
     * Page Types
     *
     */
    public array $page_types = [];
    /**
     * Localizations
     *
     */
    public array $localizations = [];

    /**
     * Taxonomy_Image constructor.
     */
    public function __construct()
    {
        $this->localizations = [
            'media_title' => esc_html__('Select Image', 'planet4-master-theme-backend'),
        ];
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_action('post_tag_add_form_fields', [ $this, 'add_taxonomy_form_fields' ]);
        add_action('post_tag_edit_form_fields', [ $this, 'add_taxonomy_form_fields' ]);
        add_action('create_post_tag', [ $this, 'save_taxonomy_meta' ]);
        add_action('edit_post_tag', [ $this, 'save_taxonomy_meta' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);

        add_filter('manage_edit-post_tag_columns', [ $this, 'edit_taxonomy_columns' ]);
        add_filter('manage_post_tag_custom_column', [ $this, 'manage_taxonomy_custom_column' ], 10, 3);
        add_filter('manage_edit-post_tag_sortable_columns', [ $this, 'manage_taxonomy_custom_sortable_column' ], 10, 3);
    }

    /**
     * Add custom field(s) to taxonomy form.
     *
     * @param WP_Term|string $wp_tag The object passed to the callback when on Edit Tag page.*
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function add_taxonomy_form_fields($wp_tag): void
    {
        $this->page_types = get_terms(
            [
                'hide_empty' => false,
                'orderby' => 'name',
                'taxonomy' => 'p4-page-type',
            ]
        );

        if (isset($wp_tag) && $wp_tag instanceof WP_Term) {
            $selected_page_types = get_term_meta($wp_tag->term_id, 'selected_page_types');
            if (! isset($selected_page_types[0])) {
                $selected_page_types[0] = [];
            }

            $attachment_id = get_term_meta($wp_tag->term_id, 'tag_attachment_id', true);
            $image_attributes = wp_get_attachment_image_src($attachment_id, 'full');
            $attachment_url = $image_attributes ? $image_attributes[0] : '';
            ?>
            <tr>
                <th colspan="2">
                    <?php esc_html_e('Column block: Choose which Page Types will populate the content of the Column block. If no box is checked Publications will appear by default.', 'planet4-master-theme-backend'); ?>
                </th>
            </tr>
            <?php foreach ($this->page_types as $index => $page_type) { ?>
                <tr class="form-field edit-wrap term-page-type-<?php echo esc_attr($page_type->slug); ?>-wrap">
                    <th></th>
                    <td>
                        <div class="field-block shortcode-ui-field-checkbox shortcode-ui-attribute-p4_page_type_<?php echo esc_attr($page_type->slug); ?>">
                            <label for="shortcode-ui-p4_page_type_<?php echo esc_attr($page_type->slug); ?>">
                                <input type="checkbox" name="p4_page_type[]" id="shortcode-ui-p4_page_type_<?php echo esc_attr($page_type->slug); ?>" value="<?php echo esc_attr($page_type->slug); ?>" <?php echo in_array($page_type->slug, $selected_page_types[0], true) ? 'checked' : ''; ?>>
                                <?php echo esc_html($page_type->name); ?>

                            </label>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <tr class="form-field edit-wrap term-image-wrap">
                <th>
                    <label><?php esc_html_e('Image', 'planet4-master-theme-backend'); ?></label>
                </th>
                <td>
                    <input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag-attachment-id field-id" value="<?php echo esc_attr($attachment_id); ?>" />
                    <input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="<?php echo esc_url($attachment_url); ?>" />
                    <button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
                        <?php esc_html_e('Select/Upload Image', 'planet4-master-theme-backend'); ?>
                    </button>
                    <p class="description"><?php esc_html_e('Associate this tag with an image.', 'planet4-master-theme-backend'); ?></p>
                    <img class="attachment-thumbnail size-thumbnail" src="<?php echo esc_url($attachment_url); ?>"/>
                    <i class="dashicons dashicons-dismiss <?php echo $image_attributes ? '' : 'hidden'; ?>" style="cursor: pointer;"></i>
                </td>
            </tr>
            <?php
        } else { ?>
            <div class="form-field add-wrap term-image-wrap">
                <label><?php esc_html_e('Image', 'planet4-master-theme-backend'); ?></label>
                <input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag_attachment_id field-id" value="" />
                <input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="" />
                <button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
                    <?php esc_html_e('Select/Upload Image', 'planet4-master-theme-backend'); ?>
                </button>
                <p class="description"><?php esc_html_e('Associate this tag with an image.', 'planet4-master-theme-backend'); ?></p>
                <img class="attachment-thumbnail size-thumbnail" src="" />
                <i class="dashicons dashicons-dismiss hidden" style="cursor: pointer;"></i>
                <p class="submit">
                    <input name="submit" id="addtag" type="submit" class="button button-primary" value="<?php esc_html_e('Add new tag', 'planet4-master-theme-backend'); ?>" />
                </p>
            </div>
            <?php
        }
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Save taxonomy custom field(s).
     *
     * @param int $term_id The ID of the WP_Term object that is added or edited.
     */
    public function save_taxonomy_meta(int $term_id): void
    {
        // Save the selected page types for this campaign.
        $selected_page_types = $_POST['p4_page_type'] ?? []; // phpcs:ignore

        if ($this->validate_page_types($selected_page_types)) {
            update_term_meta($term_id, 'selected_page_types', $selected_page_types);
        }

        $field_id = 'tag_attachment_id';
        $field_url = 'tag_attachment';
        $attachment_id = filter_input(INPUT_POST, $field_id, FILTER_VALIDATE_INT);
        $attachment_url = filter_input(INPUT_POST, $field_url, FILTER_VALIDATE_URL);

        if (!$attachment_id || !$this->validate($attachment_id)) {
            return;
        }

        update_term_meta($term_id, $field_id, $attachment_id);
        update_term_meta($term_id, $field_url, $attachment_url);
    }

    /**
     * Add custom column.
     *
     * @param array $columns Associative array with the columns of the taxonomy.
     *
     * @return array Associative array with the columns of the taxonomy.
     */
    public function edit_taxonomy_columns(array $columns): array
    {
        $columns['image'] = __('Image', 'planet4-master-theme-backend');

        return $columns;
    }

    /**
     * Apply custom output to a custom column.
     *
     * @param string $output The html to be applied to each row of the $column.
     * @param string $column The name of the column to be managed.
     * @param int    $term_id The ID of the WP_Term object that is added or edited.
     *
     * @return string The new html to be applied to each row of the $column.
     */
    public function manage_taxonomy_custom_column(string $output, string $column, int $term_id): string
    {
        if ('image' === $column) {
            $attachment_id = get_term_meta($term_id, 'tag_attachment_id', true);
            return wp_get_attachment_image($attachment_id);
        }

        return $output;
    }

    /**
     * Make column sortable.
     *
     * @param array $columns Associative array with the columns of the taxonomy.
     *
     * @return array Associative array with the columns of the taxonomy.
     */
    public function manage_taxonomy_custom_sortable_column(array $columns): array
    {
        $columns['image'] = 'image';
        return $columns;
    }

    /**
     * Validates the input.
     *
     * @param int $id The attachment id to be validated.
     *
     * @return bool True if validation is ok, false if validation fails.
     */
    public function validate(int $id): bool
    {
        return $id >= 0;
    }

    /**
     * Validates the page types input.
     *
     * @param array $selected_page_types The selected page types selected by the editor.
     *
     * @return bool True if validation is ok, false if validation fails.
     */
    public function validate_page_types(array $selected_page_types): bool
    {
        $page_types_slugs = [];
        $this->page_types = get_terms(
            [
                'hide_empty' => false,
                'orderby' => 'name',
                'taxonomy' => 'p4-page-type',
            ]
        );

        if ($this->page_types) {
            foreach ($this->page_types as $page_type) {
                if (!($page_type instanceof WP_Term)) {
                    continue;
                }

                $page_types_slugs[] = $page_type->slug;
            }
        }

        if (isset($selected_page_types) && is_array($selected_page_types)) {
            foreach ($selected_page_types as $selected_page_type) {
                if (! in_array($selected_page_type, $page_types_slugs, true)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Uses block's filtered/converted attributes and it's name to convert it to a gutenberg equivalent block.
     *
     * @param string $block_name Gutenberg block name.
     * @param array  $block_attributes block attribute array.
     *
     */
    protected function make_gutenberg_comment(string $block_name, array $block_attributes): string
    {
        return '<!-- wp:' . $block_name . ' ' . wp_json_encode($block_attributes, JSON_UNESCAPED_SLASHES) . ' /-->';
    }

    /**
     * Load assets.
     */
    public function enqueue_admin_assets(): void
    {
        if (! is_admin() || strpos(get_current_screen()->taxonomy, $this->taxonomy) === false) {
            return;
        }
        wp_enqueue_style(
            'custom-login',
            get_template_directory_uri() . '/admin/css/post_tag.css',
            [],
            Loader::theme_file_ver('admin/css/post_tag.css')
        );
        wp_register_script(
            $this->taxonomy,
            get_template_directory_uri() . "/admin/js/$this->taxonomy.js",
            [],
            Loader::theme_file_ver("admin/js/$this->taxonomy.js"),
            true
        );
        wp_localize_script($this->taxonomy, 'localizations', $this->localizations);
        wp_enqueue_script($this->taxonomy);
        wp_enqueue_media();
    }
}
