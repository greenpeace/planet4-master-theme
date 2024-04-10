<?php
/**
 * Plugin Name: Planet4 - Gutenberg Blocks
 * Description: Contains the Gutenberg blocks that are used by Planet4 project.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-gutenberg-blocks
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-blocks
 *
 * License:     GPLv3
 * Copyright (C) 2018 Greenpeace International
 *
 * @package P4GBKS
 */

// Exit if accessed directly.
use P4\MasterTheme\Features;
use P4\MasterTheme\MigrationLog;
use P4\MasterTheme\Migrations\M001EnableEnFormFeature;

defined( 'ABSPATH' ) || die( 'Direct access is forbidden !' );


/*
========================
	C O N S T A N T S
========================
*/
if ( ! defined( 'P4GBKS_REQUIRED_PHP' ) ) {
	define( 'P4GBKS_REQUIRED_PHP', '7.0' );
}
if ( ! defined( 'P4GBKS_REQUIRED_PLUGINS' ) ) {
	define(
		'P4GBKS_REQUIRED_PLUGINS',
		[
			'timber' => [
				'min_version' => '1.9.0',
				'rel_path'    => 'timber-library/timber.php',
			],
		]
	);
}
if ( ! defined( 'P4GBKS_PLUGIN_BASENAME' ) ) {
	define( 'P4GBKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_DIRNAME' ) ) {
	define( 'P4GBKS_PLUGIN_DIRNAME', dirname( P4GBKS_PLUGIN_BASENAME ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_DIR' ) ) {
	define( 'P4GBKS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . P4GBKS_PLUGIN_DIRNAME );
}
if ( ! defined( 'P4GBKS_PLUGIN_URL' ) ) {
	define( 'P4GBKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_NAME', 'Planet4 - Gutenberg Blocks' );
}
if ( ! defined( 'P4GBKS_PLUGIN_SHORT_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_SHORT_NAME', 'Blocks' );
}
if ( ! defined( 'P4GBKS_PLUGIN_SLUG_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_SLUG_NAME', 'plugin_blocks_report' );
}
if ( ! defined( 'P4GBKS_INCLUDES_DIR' ) ) {
	define( 'P4GBKS_INCLUDES_DIR', P4GBKS_PLUGIN_DIR . '/templates/' );
}
if ( ! defined( 'P4GBKS_TEMPLATE_OVERRIDE_SUBDIR' ) ) {
	define( 'P4GBKS_TEMPLATE_OVERRIDE_SUBDIR', '/templates/plugins/planet4-plugin-gutenberg-blocks/includes/' );
}
if ( ! defined( 'P4GBKS_ADMIN_DIR' ) ) {
	define( 'P4GBKS_ADMIN_DIR', plugins_url( P4GBKS_PLUGIN_DIRNAME . '/admin/' ) );
}
if ( ! defined( 'P4GBKS_LANGUAGES' ) ) {
	define(
		'P4GBKS_LANGUAGES',
		[
			'en_US' => 'English',
			'el_GR' => 'Ελληνικά',
		]
	);
}

if ( ! defined( 'P4GBKS_ALLOWED_PAGETYPE' ) ) {
	define( 'P4GBKS_ALLOWED_PAGETYPE', [ 'page', 'campaign' ] );
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', P4GBKS_PLUGIN_BASENAME );
}

if ( ! defined( 'P4GBKS_EN_SLUG_NAME' ) ) {
	define( 'P4GBKS_EN_SLUG_NAME', 'engagingnetworks' );
}

if ( ! defined( 'P4_REST_SLUG' ) ) {
	define( 'P4_REST_SLUG', 'planet4-engaging-networks' );
}

require_once __DIR__ . '/classes/class-loader.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/*
==========================
	F I L T E R
==========================
*/

const POST_BLOCK_TYPES = [
	'planet4-blocks/accordion',
	'planet4-blocks/articles',
	'planet4-blocks/counter',
	'planet4-blocks/gallery',
	'planet4-blocks/social-media',
	'planet4-blocks/spreadsheet',
	'planet4-blocks/take-action-boxout',
	'planet4-blocks/timeline',
	'leadin/hubspot-form-block',
	'gravityforms/form',
];

// pages allow all block types.
const PAGE_BLOCK_TYPES = [
	'planet4-blocks/accordion',
	'planet4-blocks/articles',
	'planet4-blocks/carousel-header',
	'planet4-blocks/columns',
	'planet4-blocks/cookies',
	'planet4-blocks/counter',
	'planet4-blocks/covers',
	'planet4-blocks/gallery',
	'planet4-blocks/happypoint',
	'planet4-blocks/media-video',
	'planet4-blocks/social-media',
	'planet4-blocks/spreadsheet',
	'planet4-blocks/submenu',
	'planet4-blocks/timeline',
	'planet4-blocks/enform',
	'planet4-blocks/guestbook',
	'leadin/hubspot-form-block',
	'gravityforms/form',
];

const BETA_PAGE_BLOCK_TYPES = [
	'planet4-blocks/share-buttons',
];

// campaigns allow all block types.
const CAMPAIGN_BLOCK_TYPES = [
	'planet4-blocks/accordion',
	'planet4-blocks/articles',
	'planet4-blocks/carousel-header',
	'planet4-blocks/columns',
	'planet4-blocks/cookies',
	'planet4-blocks/counter',
	'planet4-blocks/covers',
	'planet4-blocks/gallery',
	'planet4-blocks/happypoint',
	'planet4-blocks/media-video',
	'planet4-blocks/social-media',
	'planet4-blocks/spreadsheet',
	'planet4-blocks/sub-pages',
	'planet4-blocks/timeline',
	'planet4-blocks/enform',
	'planet4-blocks/guestbook',
	'leadin/hubspot-form-block',
	'gravityforms/form',
];

const BETA_CAMPAIGN_BLOCK_TYPES = [
	'planet4-blocks/social-media-cards',
	'planet4-blocks/share-buttons',
];

// action page type allow all block types.
const ACTION_BLOCK_TYPES = [
	'planet4-blocks/accordion',
	'planet4-blocks/articles',
	'planet4-blocks/carousel-header',
	'planet4-blocks/columns',
	'planet4-blocks/cookies',
	'planet4-blocks/counter',
	'planet4-blocks/covers',
	'planet4-blocks/gallery',
	'planet4-blocks/happypoint',
	'planet4-blocks/media-video',
	'planet4-blocks/social-media',
	'planet4-blocks/spreadsheet',
	'planet4-blocks/submenu',
	'planet4-blocks/timeline',
	'planet4-blocks/enform',
	'planet4-blocks/guestbook',
	'leadin/hubspot-form-block',
	'gravityforms/form',
	'planet4-blocks/sub-pages',
];

const BETA_ACTION_BLOCK_TYPES = [
	'planet4-blocks/social-media-cards',
	'planet4-blocks/share-buttons',
];

const BLOCK_TEMPLATES = [
	'planet4-block-templates/deep-dive',
	'planet4-block-templates/highlighted-cta',
	'planet4-block-templates/quick-links',
	'planet4-block-templates/reality-check',
	'planet4-block-templates/issues',
	'planet4-block-templates/page-header',
	'planet4-block-templates/side-image-with-text-and-cta',

	// layouts.
	'planet4-block-templates/deep-dive-topic',
	'planet4-block-templates/homepage',
	'planet4-block-templates/campaign',
	'planet4-block-templates/take-action',
	'planet4-block-templates/action',
	'planet4-block-templates/get-informed',
	'planet4-block-templates/high-level-topic',
];

/**
 * Allowed block types based on post type
 *
 * @param array  $allowed_block_types array of allowed block types.
 * @param object $context Current editor context.
 *
 * @return array|bool Array with allowed types, or true if all blocks are allowed.
 */
function set_allowed_block_types( $allowed_block_types, $context ) {
	if ( Features::is_active( 'allow_all_blocks' ) ) {
		return true;
	}
	$post_type = $context->post ? $context->post->post_type : null;
	// https://github.com/WordPress/gutenberg/blob/trunk/lib/blocks.php.
	$wordpress_blocks = [
		'core/block',
		'core/paragraph',
		'core/heading',
		'core/image',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/file',
		'core/html',
		'core/table',
		'core/buttons',
		'core/button',
		'core/separator',
		'core/spacer',
		'core/shortcode',
		'core/group',
		'core/columns',
		'core/column',
		'core/embed',
		'core/media-text',
		'core-embed/twitter',
		'core-embed/youtube',
		'core-embed/facebook',
		'core-embed/instagram',
		'core-embed/wordpress',
		'core-embed/soundcloud',
		'core-embed/spotify',
		'core-embed/flickr',
		'core-embed/vimeo',
		'core-embed/dailymotion',
		'core-embed/funnyordie',
		'core-embed/imgur',
		'core-embed/issuu',
		'core-embed/kickstarter',
		'core-embed/meetup-com',
		'core-embed/mixcloud',
		'core-embed/photobucket',
		'core-embed/polldaddy',
		'core-embed/reddit',
		'core-embed/scribd',
		'core-embed/slideshare',
		'core-embed/speaker',
		'core-embed/ted',
		'core-embed/videopress',
	];

	$migration_ran = MigrationLog::from_wp_options()->already_ran( M001EnableEnFormFeature::get_id() );

	$enform_active = ! $migration_ran || Features::is_active( 'allow_all_blocks' );

	$page_block_types = array_merge(
		PAGE_BLOCK_TYPES,
		! Features::is_active( 'beta_blocks' ) ? [] : BETA_PAGE_BLOCK_TYPES,
		! $enform_active ? [] : [ 'planet4-blocks/enform' ],
		(bool) planet4_get_option( 'new_ia' ) ? [] : [ 'planet4-blocks/split-two-columns' ],
		BLOCK_TEMPLATES,
	);

	$campaign_block_types = array_merge(
		CAMPAIGN_BLOCK_TYPES,
		! Features::is_active( 'beta_blocks' ) ? [] : BETA_CAMPAIGN_BLOCK_TYPES,
		! $enform_active ? [] : [ 'planet4-blocks/enform' ],
		BLOCK_TEMPLATES,
	);

	$action_block_types = array_merge(
		ACTION_BLOCK_TYPES,
		! Features::is_active( 'beta_blocks' ) ? [] : BETA_ACTION_BLOCK_TYPES,
		! $enform_active ? [] : [ 'planet4-blocks/enform' ],
		BLOCK_TEMPLATES,
	);

	$all_allowed_p4_block_types = [
		'post'      => POST_BLOCK_TYPES,
		'page'      => $page_block_types,
		'campaign'  => $campaign_block_types,
		'p4_action' => $action_block_types,
	];

	$allowed_p4_block_types = $all_allowed_p4_block_types[ $post_type ] ?? $all_allowed_p4_block_types['page'];

	return array_merge( $wordpress_blocks, $allowed_p4_block_types );
}

add_filter( 'allowed_block_types_all', 'set_allowed_block_types', 10, 2 );

/**
 * @param array $block the block being rendered.
 * For the "link_new_tab" field the type was initially incorrectly set to
 * string instead of boolean. As a result we need to catch all empty strings here and
 * turn them into false.
 *
 * Note that this DOES NOT get called when a block is rendered using the REST API column rendered *sigh*.
 * Even better, there is no hook that allows to modify the request before the parameters are validated.
 * As this is only for the block renderer of columns, a good option would be to replace the invalid argument
 * on the frontend before the API is called.
 * This should be sufficient as we can fix the data after this fix hsa been applied, after which we can remove the workaround.
 *
 * @return array
 */
function empty_string_to_false_in_link_new_tab_in_columns_blocks( $block ): array {
	// Yes, that's right, WordPress doesn't follow its own rules here so we have a camel among snakes.
	if ( 'planet4-blocks/columns' === $block['blockName'] ?? null ) {
		foreach ( $block['attrs']['columns'] ?? [] as $key => $column ) {
			if ( isset( $column['link_new_tab'] ) && true !== $column['link_new_tab'] ) {
				$block['attrs']['columns'][ $key ]['link_new_tab'] = false;
			}
		}
	}

	return $block;
}

add_filter( 'render_block_data', 'empty_string_to_false_in_link_new_tab_in_columns_blocks' );

// Add a filter to prevent the main query from being run on the DB or ES. This is needed because we actually executed a
// separate query for our results and never use the result of the main query. Returning an empty array in
// posts_pre_query for the main query on a search page short circuits the main query. However we also need to remove the
// filter that is set up by ElasticPress. Then we call it manually in our filter when we're not performing the main
// query of search.

// Even though all instances have ElasticPress plugin, this is run during unit tests where the ElasticPress plugin is
// not (yet?) loaded, causing a fatal error.
if ( class_exists( Elasticpress\Indexables::class ) ) {

	remove_filter(
		'posts_pre_query',
		[
			\ElasticPress\Indexables::factory()->get( 'post' )->query_integration,
			'get_es_posts',
			10,
		]
	);

	add_filter(
		'posts_pre_query',
		function ( $posts, $query ) {
			if ( is_search() && ! is_admin() && $query->is_main_query() ) {
				return [];
			}
			return \ElasticPress\Indexables::factory()->get( 'post' )->query_integration->get_es_posts(
				$posts,
				$query
			);
		},
		10,
		2
	);
}

/*
==========================
	L O A D  P L U G I N
==========================
*/
P4GBKS\Loader::get_instance();
\P4GBKS\Rest\Rest_Api::add_endpoints();

$remove_rtl_fix = function (): void {
	global $sitepress;
	// This RTL fix does not seem a good idea. Probably it was a bad attempt at solving the issues `url_to_postid`
	// creates.
	remove_action( 'wp_head', [ $sitepress, 'rtl_fix' ] );
	remove_action( 'admin_print_styles', [ $sitepress, 'rtl_fix' ] );

	// This caused `switch_lang` to get called. As a result the RTL fix messed up.
	remove_filter( 'url_to_postid', [ $sitepress, 'url_to_postid' ] );
};
$remove_rtl_fix();
add_action( 'wpml_after_startup', $remove_rtl_fix, 10, 0 );

$breakpoints = [
	[
		'screen' => 1600,
		'width'  => '1320px',
	],
	[
		'screen' => 1200,
		'width'  => '1140px',
	],
	[
		'screen' => 992,
		'width'  => '960px',
	],
	[
		'screen' => 768,
		'width'  => '720px',
	],
	[
		'screen' => 601,
		'width'  => '540px',
	],
	[
		'screen' => 577,
		'width'  => '540px',
	],
];

add_filter(
	'render_block_core/query-pagination',
	function ( $content ) {
		// Check if prev and next buttons are disabled.
		if (
			str_contains( $content, 'wp-block-query-pagination-previous disabled' )
			&& str_contains( $content, 'wp-block-query-pagination-next disabled' )
		) {
			return null;
		}

		return $content;
	},
	10,
	3
);

add_filter(
	'render_block_core/query-pagination-previous',
	function ( $content, $parsed, $block ) {
		$button_label = __( 'Prev', 'planet4-blocks' );

		if ( ! array_key_exists( 'label', $block->attributes ) ) {
			$block->attributes['label'] = $button_label;
			return $block->render();
		}

		// Check if the button isn't rendered, then return it.
		if ( empty( $content ) ) {
			return '<a href="/" class="wp-block-query-pagination-previous disabled">' . $button_label . '</a>';
		}

		return $content;
	},
	10,
	3
);

add_filter(
	'render_block_core/query-pagination-next',
	function ( $content, $parsed, $block ) {
		$button_label = __( 'Next', 'planet4-blocks' );

		if ( ! array_key_exists( 'label', $block->attributes ) ) {
			$block->attributes['label'] = $button_label;
			return $block->render();
		}

		// Check if the button isn't rendered, then return it.
		if ( empty( $content ) ) {
			return '<a href="/" class="wp-block-query-pagination-next disabled">' . $button_label . '</a>';
		}

		return $content;
	},
	10,
	3
);

add_filter(
	'render_block',
	function ( $block_content, $block, WP_Block $instance ) use ( $breakpoints ) {
		if ( 'core/query' === $block['blockName'] ) {
			$column_count = $instance->attributes['displayLayout']['columns'] ?? null;
			if ( ! $column_count || 1 === $column_count ) {
				return $block_content;
			}

			$sizes = array_map(
				function ( $breakpoint ) use ( $column_count ) {
					$screen         = $breakpoint['screen'];
					$container      = $breakpoint['width'];
					$cols_minus_one = $column_count - 1;

					return "(min-width: ${screen}px) calc($container / $column_count - 1.25em * $cols_minus_one)";
				},
				$breakpoints
			);

			$sizes_attr = 'sizes="' . implode( ', ', array_merge( $sizes, [ 'calc(100vw - 24px)' ] ) ) . '"';

			// Assume all images are full width in a container.
			$block_content = preg_replace( '/sizes=".*"/', $sizes_attr, $block_content );
		}

		if ( 'core/media-text' === $block['blockName'] && array_key_exists( 'mediaId', $instance->attributes ) ) {
			$media_id    = $instance->attributes['mediaId'];
			$media_width = $instance->attributes['mediaWidth'] ?? 50;

			$srcset = wp_get_attachment_image_srcset( $media_id, 'full' );

			if ( 'full' === $instance->attributes['align'] ) {
				$sizes = ! $instance->attributes['isStackedOnMobile'] ? "${media_width}vw"
					: "(min-width: 601px) {$media_width}vw, 100vw";

				$sizes_attr = "sizes=\"{$sizes}\"";
			} else {
				$default = ! $instance->attributes['isStackedOnMobile'] ? "calc((100vw - 24px) * $media_width / 100)"
					: 'calc(100vw - 24px)';
				$sizes   = implode(
					',',
					array_map(
						function ( $breakpoint ) use ( $instance, $media_width ) {
							$screen       = $breakpoint['screen'];
							$container    = $breakpoint['width'];
							$should_stack = $screen < 600 && $instance->attributes['isStackedOnMobile'];
							$fraction     = $should_stack ? 1 : round( 100 / $media_width, 4 );

							// Currently, we need to subtract 24px for Bootstrap container.
							return "(min-width: ${screen}px) calc(($container - 24px) / $fraction)";
						},
						$breakpoints
					)
				);

				$sizes_attr = "sizes=\"{$sizes}, {$default}\"";
			}

			$image_class_start = "class=\"wp-image-$media_id ";

			$block_content = str_replace(
				$image_class_start,
				"$sizes_attr srcset=\"$srcset\" $image_class_start",
				$block_content
			);
		}

		return $block_content;
	},
	10,
	3
);
