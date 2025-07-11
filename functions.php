<?php // phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols

use P4\MasterTheme\Api;
use P4\MasterTheme\Loader;
use P4\MasterTheme\MediaArchive\Rest;
use P4\MasterTheme\Post;
use Timber\Timber;

// This theme vendor dir
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
// Local env base vendor dir
if (file_exists(WP_CONTENT_DIR . '/vendor/autoload.php')) {
    require_once WP_CONTENT_DIR . '/vendor/autoload.php';
}
// Prod env base vendor dir
if (file_exists(dirname(ABSPATH) . '/vendor/autoload.php')) {
    require_once dirname(ABSPATH) . '/vendor/autoload.php';
}

/**
 * A simpler way to add a filter that only returns a static value regardless of the input.
 *
 * @param string   $filter_name The WordPress filter.
 * @param mixed    $value The value to be returned by the filter.
 * @param int|null $priority The priority for the filter.
 *
 */
function simple_value_filter(string $filter_name, $value, ?int $priority = null): void
{
    add_filter(
        $filter_name,
        static function () use ($value) {
            return $value;
        },
        $priority,
        0
    );
}

/**
 * Generate a bunch of placeholders for use in an IN query.
 * Unfortunately WordPress doesn't offer a way to do bind IN statement params, it would be a lot easier if we could pass
 * the array to wpdb->prepare as a whole.
 *
 * @param array  $items The items to generate placeholders for.
 * @param int    $start_index The start index to use for creating the placeholders.
 * @param string $type The type of value.
 *
 * @return string The generated placeholders string.
 */
function generate_list_placeholders(array $items, int $start_index, string $type = 'd'): string
{
    $placeholders = [];
    foreach (range($start_index, count($items) + $start_index - 1) as $i) {
        $placeholder = "%{$i}\${$type}";
        // Quote it if it's a string.
        if ('s' === $type) {
            $placeholder = "'{$placeholder}'";
        }
        $placeholders[] = $placeholder;
    }

    return implode(',', $placeholders);
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @param string $key Options array key.
 * @param mixed  $default The default value to use if the options is not set.
 * @return mixed Option value.
 */
function planet4_get_option(string $key = '', $default = null)
{
    $options = get_option('planet4_options');

    return $options[ $key ] ?? $default;
}

// Timber loading
if (!class_exists(Timber::class)) {
    add_action('admin_notices', function (): void {
        echo '<div class="error"><p>Timber not activated. '
            . 'Make sure you installed the composer package `timber/timber`.</p></div>';
    });
    add_filter(
        'template_include',
        fn() => get_stylesheet_directory() . '/static/no-timber.html'
    );
    return;
}
Timber::$cache = defined('WP_DEBUG') ? !WP_DEBUG : true;
$timber = new Timber();

add_action(
    'rest_api_init',
    function (): void {
        Rest::register_endpoints();
        Api\Search::register_endpoint();
        Api\Settings::register_endpoint();
        Api\AnalyticsValues::register_endpoint();
        Api\Tracking::register_endpoint();
    }
);

/**
 * Hide core updates notification in the dashboard, to avoid confusion while an upgrade is already in progress.
 */
function hide_wp_update_nag(): void
{
    remove_action('admin_notices', 'update_nag', 3);
    remove_filter('update_footer', 'core_update_footer');
}

add_action('admin_menu', 'hide_wp_update_nag');

require_once 'load-class-aliases.php';

Loader::get_instance();

// WP core's escaping logic doesn't take the case into account where a gradient is followed by a URL.
add_filter(
    'safecss_filter_attr_allow_css',
    function (bool $allow_css, $css_test_string) {
        // Short circuit in case the CSS is already allowed.
        // This filter only runs to catch the case where it's not allowed but should be.
        if ($allow_css) {
            return true;
        }

        $without_property = preg_replace('/.*:/', '', $css_test_string);

        // Same regex as in WordPress core, except it matches anywhere in the string.
        // See https://github.com/WordPress/WordPress/blob/a5293aa581802197b0dd7c42813ba137708ad0e1/wp-includes/kses.php#L2438.
        $gradient_regex = '/(repeating-)?(linear|radial|conic)-gradient\(([^()]|rgb[a]?\([^()]*\))*\)/';

        // Check if a gradient is still present.
        // The only case where $css_test_string can still have this present is if it
        // was missed by the faulty WP regex.
        if (! preg_match($gradient_regex, $css_test_string)) {
            return $allow_css;
        }

        $without_gradient = preg_replace($gradient_regex, '', trim($without_property));

        return trim($without_gradient, ', ') === '';
    },
    10,
    2
);

/**
 * This is not a column in WordPress by default, but is added by the Post Type Switcher plugin.
 * It's not needed for the plugin to work, and needlessly takes up space on pages where everything has the same post
 * type.
 *
 * Showing the field is only somewhat useful when using quick edit to switch a single post from the admin listing page.
 */
add_filter(
    'default_hidden_columns',
    function ($hidden) {
        $hidden[] = 'post_type';

        return $hidden;
    },
    10,
    1
);

/**
 * TODO: Move to editor only area.
 * Set the editor width per post type.
 */
add_filter(
    'block_editor_settings_all',
    function (array $editor_settings, WP_Block_Editor_Context $block_editor_context) {
        if (isset($block_editor_context->post) && 'post' !== $block_editor_context->post->post_type) {
            $editor_settings['__experimentalFeatures']['layout']['contentSize'] = '1320px';
        }

        return $editor_settings;
    },
    10,
    2
);

// Add filters to the News & Stories listing page.
// Right now only "category" and "post type" are available.
add_action(
    'pre_get_posts',
    function ($query): void {
        if (!$query->is_main_query() || is_admin() || !is_home()) {
            return;
        }
        // Category filter
        $category_slug = isset($_GET['category']) ? $_GET['category'] : '';
        $category = get_category_by_slug($category_slug);
        $query->set('category__in', $category ? [$category->term_id] : []);

        // Post type filter
        $post_type_slug = isset($_GET['post-type']) ? $_GET['post-type'] : '';
        $post_type = get_term_by('slug', $post_type_slug, 'p4-page-type');
        $query->set('tax_query', !$post_type ? [] : [[
            'taxonomy' => 'p4-page-type',
            'field' => 'term_id',
            'terms' => [$post_type->term_id],
        ]]);
    }
);

// This action overrides the WordPress functionality for adding a notice message
// https://github.com/WordPress/wordpress-develop/blob/trunk/src/wp-admin/edit-form-blocks.php#L303-L305
// When it's a page for posts.
add_action(
    'admin_enqueue_scripts',
    function (): void {
        global $post;

        if (!$post || (int) get_option('page_for_posts') !== $post->ID) {
            return;
        }

        // Adding this style, it works as a workaround for editors
        // To disable the ability to edit the content of the listing page.
        echo '<style>
            .edit-post-header-toolbar,
            .block-list-appender {
                pointer-events: none;
                opacity: 0;
            }

            .block-editor-block-list__layout {
                display: none;
            }

            .components-notice__actions {
                display: inline-flex !important;
                margin-left: 5px;
            }
        </style>';

        wp_add_inline_script(
            'wp-notices',
            sprintf(
                'wp.data.dispatch( "core/notices" ).createNotice("warning", "%s" , { isDismissible: false, actions: [ { label: "%s", url: "/wp-admin/options-reading.php"} ] } )',
                __('The content on this page is hidden because this page is being used as your \"All Posts\" listing page. You can disable this by un-setting the \"Posts page\"', 'planet4-master-theme'),
                __('here', 'planet4-master-theme')
            )
        );
    },
    100
);

// The new `pagenum` query_var is used ONLY trough the default listing pages (index.php),
// when the Load more feature is enabled.
// Also added in replacement of the `page` query_var since that param is returning always zero.
add_filter(
    'query_vars',
    function ($vars) {
        $vars[] = 'page_num';
        return $vars;
    }
);

// Action to filter P4 settings menu.
add_action(
    'admin_head',
    function (): void {
        global $submenu;

        if (!isset($submenu['planet4_settings_navigation'])) {
            return;
        }

        uasort($submenu['planet4_settings_navigation'], fn ($a, $b) => $a[0] <=> $b[0]);
    }
);

// This filter replaces the default Canonical URL with what is set in the Sidebar Options.
// If no URL is set for the Canonical link, the default WP url is used.
add_filter(
    'get_canonical_url',
    function ($canonical_url, $post) {
        if (isset($post->p4_seo_canonical_url) && '' !== $post->p4_seo_canonical_url) {
            $canonical_url = $post->p4_seo_canonical_url;
        }
        return $canonical_url;
    },
    10,
    2
);

if (class_exists('\\Sentry\\Options')) {
    add_filter('wp_sentry_options', function (\Sentry\Options $options) {
        // Only sample 50% of the events
        $options->setSampleRate(0.50);

        // Set server_name tag
        $podname = gethostname() ?: 'unknown'; // Fallback to 'unknown' if gethostname() fails
        $parts = explode('-', $podname);

        if (count($parts) === 1) {
            // Local development
            $server_name = $podname;
        } elseif ($parts[1] === 'test') {
            // Test instances
            $server_name = $parts[2];
        } else {
            // Production/Development instances
            $server_name = $parts[1];
        }
        $options->setServerName($server_name);

        return $options;
    });
}

// Enable Hide Page title by default when Pattern Layout is used
add_action(
    'transition_post_status',
    function ($new_status, $old_status, $post): void {
        // Check if the new status is 'publish' and the old status is not 'publish'
        // And it is a Page
        if ($new_status !== 'publish' || $old_status === 'publish' || $post->post_type === 'post') {
            return;
        }

        // Parse the blocks in the post content
        $blocks = parse_blocks($post->post_content);

        // Check if the first block matches the regex
        if (empty($blocks) || !preg_match('/^planet4-block-templates\/.*/', $blocks[0]['blockName'])) {
            return;
        }

        // Update the 'hide_page_title' metadata
        update_post_meta($post->ID, 'p4_hide_page_title_checkbox', 'on');
    },
    10,
    3
);

// Update Core Post Author block with P4 custom block
// P4 custom block has author override value
add_filter(
    'render_block',
    function ($block_content, $block): string {
        if ($block['blockName'] === 'core/post-author-name') {
            return render_block(['blockName' => 'p4/post-author-name']);
        }
        return $block_content;
    },
    10,
    2
);
// phpcs:enable Generic.Files.LineLength.MaxExceeded
