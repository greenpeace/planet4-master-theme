<?php
// phpcs:ignoreFile
/**
 * Post Exporter Functionality
 *
 * @package P4MT
 */

global $wpdb, $post;
$defaults = [
	'content'    => 'all',
	'author'     => false,
	'category'   => false,
	'start_date' => false,
	'end_date'   => false,
	'status'     => false,
];
$args     = wp_parse_args( $post, $defaults );
define( 'CMX_VERSION', '1.0' );
$sitename = sanitize_key( get_bloginfo( 'name' ) );
if ( ! empty( $sitename ) ) {
	$sitename .= '.';
}
$filename = $sitename . gmdate( 'Ymdhis' ) . '.xml';

header( 'Content-Description: File Transfer' );
header( 'Content-Disposition: attachment; filename=' . $filename );
header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

if ( 'all' !== $args['content'] && post_type_exists( $args['content'] ) ) {
	$ptype = get_post_type_object( $args['content'] );

	if ( ! $ptype->can_export ) {
		$args['content'] = 'post';
	}

	$where = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", $args['content'] );
} else {
	$post_types = get_post_types( [ 'can_export' => true ] );
	$esses      = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
	$where      = $wpdb->prepare( "{$wpdb->posts}.post_type IN ( %s )", $esses );
}

if ( $args['status'] && ( 'post' === $args['content'] || 'page' === $args['content'] ) ) {
	$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_status = %s", $args['status'] );
} else {
	$where .= " AND {$wpdb->posts}.post_status != 'auto-draft'";
}

$join = '';
if ( $args['category'] && 'post' === $args['content'] ) {
	$term = term_exists( $args['category'], 'category' );
	if ( $term ) {
		$join   = "INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
		$where .= $wpdb->prepare( " AND {$wpdb->term_relationships}.term_taxonomy_id = %d", $term['term_taxonomy_id'] );
	}
}

if ( 'post' === $args['content'] || 'page' === $args['content'] ) {
	if ( $args['author'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $args['author'] );
	}

	if ( $args['start_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date >= %s", date( 'Y-m-d', strtotime( $args['start_date'] ) ) );
	}

	if ( $args['end_date'] ) {
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_date < %s", date( 'Y-m-d', strtotime( '+1 month', strtotime( $args['end_date'] ) ) ) );
	}
}
$sql = sprintf( "SELECT ID FROM {$wpdb->posts} %s WHERE %s", $join, $where );
// Ignore lint on the following line because it doesn't detect the context of the $sql variable.
$post_ids = apply_filters( 'export_post_ids', $wpdb->get_col( $sql ), $args );  // phpcs:ignore

$cats  = [];
$tags  = [];
$terms = [];

/**
 * Wrap strings in nested CDATA tags.
 *
 * @param string $str String to replace.
 */
function p4_px_single_post_cdata( $str ) {
	if ( seems_utf8( $str ) === false ) {
		$str = utf8_encode( $str );
	}
	$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

	return $str;
}

/**
 * Get the site url.
 */
function p4_px_single_post_site_url() {
	if ( is_multisite() ) {
		return network_home_url();
	} else {
		return get_bloginfo_rss( 'url' );
	}
}

/**
 * Get the Category name.
 *
 * @param object $category Category object.
 */
function p4_px_single_post_cat_name( $category ) {
	if ( empty( $category->name ) ) {
		return;
	}

	echo '<wp:cat_name>' . p4_px_single_post_cdata( $category->name ) . '</wp:cat_name>';
}

/**
 * Get the Category description.
 *
 * @param object $category Category object.
 */
function p4_px_single_post_category_description( $category ) {
	if ( empty( $category->description ) ) {
		return;
	}

	echo '<wp:category_description>' . p4_px_single_post_cdata( $category->description ) . '</wp:category_description>';
}

/**
 * Get the Post Tag name.
 *
 * @param object $tag Tag object.
 */
function p4_px_single_post_tag_name( $tag ) {
	if ( empty( $tag->name ) ) {
		return;
	}

	echo '<wp:tag_name>' . p4_px_single_post_cdata( $tag->name ) . '</wp:tag_name>';
}

/**
 * Get the Post Tag description.
 *
 * @param object $tag Tag object.
 */
function p4_px_single_post_tag_description( $tag ) {
	if ( empty( $tag->description ) ) {
		return;
	}

	echo '<wp:tag_description>' . p4_px_single_post_cdata( $tag->description ) . '</wp:tag_description>';
}

/**
 * Get the Post Term name.
 *
 * @param object $term Term object.
 */
function p4_px_single_post_term_name( $term ) {
	if ( empty( $term->name ) ) {
		return;
	}

	echo '<wp:term_name>' . p4_px_single_post_cdata( $term->name ) . '</wp:term_name>';
}

/**
 * Get the Post Term description.
 *
 * @param object $term Tag object.
 */
function p4_px_single_post_term_description( $term ) {
	if ( empty( $term->description ) ) {
		return;
	}

	echo '<wp:term_description>' . p4_px_single_post_cdata( $term->description ) . '</wp:term_description>';
}

/**
 * Get the Post's authors.
 *
 * @param array $post_ids Tag object.
 */
function p4_px_single_post_authors_list( $post_ids ) {
	global $wpdb;

	$post_ids = array_map( 'intval', $post_ids );  // santize the post_ids manually.
	$post_ids = array_filter( $post_ids ); // strip ones that didn't validate.

	$authors = [];

	$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

	$sql = 'SELECT DISTINCT post_author
			FROM %1$s
			WHERE ID IN(' . $placeholders . ') AND post_status != \'auto-draft\'';

	$values       = [];
	$values[0]    = $wpdb->posts;
	$values       = array_merge( $values, $post_ids );
	$prepared_sql = $wpdb->prepare( $sql, $values );     // WPCS: unprepared SQL OK.
	$results      = $wpdb->get_results( $prepared_sql ); // phpcs:ignore

	foreach ( (array) $results as $result ) {
		$authors[] = get_userdata( $result->post_author );
	}

	$authors = array_filter( $authors );

	foreach ( $authors as $author ) {
		echo "\t<wp:author>";
		echo '<wp:author_id>' . $author->ID . '</wp:author_id>';
		echo '<wp:author_login>' . $author->user_login . '</wp:author_login>';
		echo '<wp:author_email>' . $author->user_email . '</wp:author_email>';
		echo '<wp:author_display_name>' . p4_px_single_post_cdata( $author->display_name ) . '</wp:author_display_name>';
		echo '<wp:author_first_name>' . p4_px_single_post_cdata( $author->user_firstname ) . '</wp:author_first_name>';
		echo '<wp:author_last_name>' . p4_px_single_post_cdata( $author->user_lastname ) . '</wp:author_last_name>';
		echo "</wp:author>\n";
	}
}

/**
 * Get the Post Taxonomy.
 */
function p4_px_single_post_taxonomy() {
	$post = get_post();

	$taxonomies = get_object_taxonomies( $post->post_type );
	if ( empty( $taxonomies ) ) {
		return;
	}
	$terms = wp_get_object_terms( $post->ID, $taxonomies );

	foreach ( (array) $terms as $term ) {
		echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . p4_px_single_post_cdata( $term->name ) . "</category>\n";
	}
}

$post_id = explode( ',', $_GET['post'] );

// Add campaign attachements in XML file.
require_once 'exporter-helper.php';
$post_id = get_campaign_attachments( $post_id );

echo '<?xml version="1.0" encoding="' . get_bloginfo( 'charset' ) . "\" ?>\n";

?>
<!-- This is a WordPress eXtended RSS file generated by WordPress as an export of your site. -->
<!-- It contains information about your site's posts, pages, comments, categories, and other content. -->
<!-- You may use this file to transfer that content from one site to another. -->
<!-- This file is not intended to serve as a complete backup of your site. -->

<!-- To import this information into a WordPress site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Tools: Import in the WordPress admin panel. -->
<!-- 3. Install the "WordPress" importer from the list. -->
<!-- 4. Activate & Run Importer. -->
<!-- 5. Upload this file using the form provided on that page. -->
<!-- 6. You will first be asked to map the authors in this export file to users -->
<!--    on the site. For each author, you may choose to map to an -->
<!--    existing user on the site or to create a new user. -->
<!-- 7. WordPress will then import each of the posts, pages, comments, categories, etc. -->
<!--    contained in this file into your site. -->

<?php the_generator( 'export' ); ?>
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wp="http://wordpress.org/export/1.0/"
>

	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<wp:wxr_version><?php echo CMX_VERSION; ?></wp:wxr_version>
		<wp:base_site_url><?php echo p4_px_single_post_site_url(); ?></wp:base_site_url>
		<wp:base_blog_url><?php bloginfo_rss( 'url' ); ?></wp:base_blog_url>

		<?php p4_px_single_post_authors_list( $post_id ); ?>

		<?php foreach ( $cats as $c ) : ?>
			<wp:category>
				<wp:term_id><?php echo $c->term_id; ?></wp:term_id>
				<wp:category_nicename><?php echo $c->slug; ?></wp:category_nicename>
				<wp:category_parent><?php echo $c->parent ? $cats[ $c->parent ]->slug : ''; ?></wp:category_parent><?php p4_px_single_post_cat_name( $c ); ?><?php p4_px_single_post_category_description( $c ); ?>
			</wp:category>
		<?php endforeach; ?>
		<?php foreach ( $tags as $t ) : ?>
			<wp:tag>
				<wp:term_id><?php echo $t->term_id; ?></wp:term_id>
				<wp:tag_slug><?php echo $t->slug; ?></wp:tag_slug><?php p4_px_single_post_tag_name( $t ); ?><?php p4_px_single_post_tag_description( $t ); ?>
			</wp:tag>
		<?php endforeach; ?>
		<?php foreach ( $terms as $t ) : ?>
			<wp:term>
				<wp:term_id><?php echo $t->term_id; ?></wp:term_id>
				<wp:term_taxonomy><?php echo $t->taxonomy; ?></wp:term_taxonomy>
				<wp:term_slug><?php echo $t->slug; ?></wp:term_slug>
				<wp:term_parent><?php echo $t->parent ? $terms[ $t->parent ]->slug : ''; ?></wp:term_parent><?php p4_px_single_post_term_name( $t ); ?><?php p4_px_single_post_term_description( $t ); ?>
			</wp:term>
		<?php endforeach; ?>

		<?php
		do_action( 'rss2_head' );
		?>

		<?php
		if ( '' !== $post_id ) {
			global $wp_query;
			$wp_query->in_the_loop = true;
			for ( $i = 0; $i < count( $post_id ); $i ++ ) {
				$next_posts  = [ $post_id[ $i ] ];
				$attachments = get_attached_media( '', $post_id[ $i ] );
				if ( ! empty( $attachments ) ) {
					foreach ( $attachments as $attachment ) {
						array_push( $next_posts, $attachment->ID );
					}
				}
				$where = array_map( 'intval', $post_ids );  // santize the post_ids manually.
				$where = array_filter( $post_ids ); // strip ones that didn't validate.
				$where = implode( ',', $next_posts );
				$posts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID IN (%s)", $where ) );
				foreach ( $posts as $post ) {
					setup_postdata( $post );
					$is_sticky = is_sticky( $post->ID ) ? 1 : 0;
					?>
					<item>
						<title><?php echo apply_filters( 'the_title_rss', $post->post_title ); ?></title>
						<link><?php the_permalink_rss(); ?></link>
						<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', get_post_time( 'Y-m-d H:i:s', true ), false ); ?></pubDate>
						<dc:creator><?php echo p4_px_single_post_cdata( get_the_author_meta( 'login' ) ); ?></dc:creator>
						<guid isPermaLink="false"><?php the_guid(); ?></guid>
						<description></description>
						<content:encoded><?php echo p4_px_single_post_cdata( apply_filters( 'the_content_export', $post->post_content ) ); ?>
							</content:encoded>
						<excerpt:encoded><?php echo p4_px_single_post_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) ); ?>
							</excerpt:encoded>
						<wp:post_id><?php echo $post->ID; ?></wp:post_id>
						<wp:post_date><?php echo $post->post_date; ?></wp:post_date>
						<wp:post_date_gmt><?php echo $post->post_date_gmt; ?></wp:post_date_gmt>
						<wp:comment_status><?php echo $post->comment_status; ?></wp:comment_status>
						<wp:ping_status><?php echo $post->ping_status; ?></wp:ping_status>
						<wp:post_name><?php echo $post->post_name; ?></wp:post_name>
						<wp:status><?php echo $post->post_status; ?></wp:status>
						<wp:post_parent><?php echo $post->post_parent; ?></wp:post_parent>
						<wp:menu_order><?php echo $post->menu_order; ?></wp:menu_order>
						<wp:post_type><?php echo $post->post_type; ?></wp:post_type>
						<wp:post_password><?php echo $post->post_password; ?></wp:post_password>
						<wp:is_sticky><?php echo $is_sticky; ?></wp:is_sticky>
						<?php if ( 'attachment' === $post->post_type ) : ?>
							<wp:attachment_url><?php echo wp_get_attachment_url( $post->ID ); ?></wp:attachment_url>
						<?php endif; ?>
						<?php p4_px_single_post_taxonomy(); ?>
						<?php
						$postmeta = get_post_meta( $post->ID );
						foreach ( $postmeta as $meta_key => $meta_value ) :
							if ( '_edit_lock' === $meta_key ) {
								continue;
							}
							?>
							<wp:postmeta>
								<wp:meta_key><?php echo $meta_key; ?></wp:meta_key>
								<wp:meta_value><?php echo p4_px_single_post_cdata( maybe_serialize($meta_value[0]) ); ?></wp:meta_value>
							</wp:postmeta>
						<?php endforeach; ?>
						<?php
						$comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved <> 'spam'", $post->ID ) );
						foreach ( $comments as $c ) :
							?>
							<wp:comment>
								<wp:comment_id><?php echo $c->comment_ID; ?></wp:comment_id>
								<wp:comment_author><?php echo p4_px_single_post_cdata( $c->comment_author ); ?></wp:comment_author>
								<wp:comment_author_email><?php echo $c->comment_author_email; ?></wp:comment_author_email>
								<wp:comment_author_url><?php echo esc_url_raw( $c->comment_author_url ); ?></wp:comment_author_url>
								<wp:comment_author_IP><?php echo $c->comment_author_IP; ?></wp:comment_author_IP>
								<wp:comment_date><?php echo $c->comment_date; ?></wp:comment_date>
								<wp:comment_date_gmt><?php echo $c->comment_date_gmt; ?></wp:comment_date_gmt>
								<wp:comment_content><?php echo p4_px_single_post_cdata( $c->comment_content ); ?></wp:comment_content>
								<wp:comment_approved><?php echo $c->comment_approved; ?></wp:comment_approved>
								<wp:comment_type><?php echo $c->comment_type; ?></wp:comment_type>
								<wp:comment_parent><?php echo $c->comment_parent; ?></wp:comment_parent>
								<wp:comment_user_id><?php echo $c->user_id; ?></wp:comment_user_id>
								<?php
								$c_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->commentmeta WHERE comment_id = %d", $c->comment_ID ) );
								foreach ( $c_meta as $meta ) :
									?>
									<wp:commentmeta>
										<wp:meta_key><?php echo $meta->meta_key; ?></wp:meta_key>
										<wp:meta_value><?php echo p4_px_single_post_cdata( $meta->meta_value ); ?></wp:meta_value>
									</wp:commentmeta>
								<?php endforeach; ?>
							</wp:comment>
						<?php endforeach; ?>
					</item>
					<?php
				}
			}
		}
		?>
	</channel>
</rss>
