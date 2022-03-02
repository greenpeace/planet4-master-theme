<?php

declare(strict_types=1);

namespace P4\MasterTheme\Report;

use WP_List_Table;
use WP_Date_Query;
use DateTime;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PostActivityTable extends WP_List_Table {
	public const ACTION_NAME = 'post_activity';

	private $request;
	private $request_filters = [];
	private $query_filters   = [];

	public function __construct( $args ) {
		parent::__construct( $args );

		if ( ! function_exists( 'wp_prepare_revisions_for_js' ) ) {
			require_once ABSPATH . 'wp-admin/includes/revision.php';
		}
	}

	public function set_request( array $request ): void {
		$this->request = $request;
	}

	/**
	 * Prepares table data.
	 */
	public function prepare_items(): void {
		global $wp_query;

		$this->request_filters = $this->filters_from_request( $this->request );
		$this->query_filters   = $this->get_query( $this->request_filters );

		$post_ids = $this->get_post_ids();
		if ( empty( $post_ids ) ) {
			$this->items = [];
			return;
		}

		$total_items  = count( $post_ids );
		$per_page     = 100;
		$current_page = $this->get_pagenum();

		$paged_ids = array_slice( $post_ids,( ( $current_page - 1 ) * $per_page ), $per_page );
		$posts = $this->get_activities(
			['numberposts' => -1, 'include' => $paged_ids]
		);

		$items = array_map(
			function ( $p ) {
				$parent     = get_post( $p->post_parent ) ?? $p;
				$rev_ids    = $this->get_rev_ids( $parent );
				$visibility = ! empty( $parent->post_password )
					? 'password'
					: ( in_array( $parent->post_status, ['private', 'draft', 'pending', 'trash'] ) ? 'private' : 'public' );

				return [
					'post_id'         => $p->ID,
					'post_title'      => $p->post_title,
					'post_name'       => $p->post_name,
					'post_author'     => \get_the_author_meta( 'display_name', $p->post_author ),
					'post_status'     => self::post_statuses()[ $parent->post_status ] ?? $parent->post_status,
					'post_visibility' => self::post_visibilities()[ $visibility ],
					'post_modified'   => $p->post_modified,
					'post_published'  => 'publish' === $parent->post_status ? $parent->post_date : '',
					'post_parent'     => $parent->ID ?? null,
					'revision_ids'    => $rev_ids,
					'diff_data'       => ( new PostDiff( $parent, $p ) )->get_data(),
				];
			},
			$posts
		);

        $this->items  = $items;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items/$per_page )
			)
		);

		$this->_column_headers = $this->get_column_headers();
	}

	public function get_activities( array $args ): array {
		return get_posts( array_merge( $args, [
			'post_type'   => 'revision',
			'post_status' => 'inherit',
			'orderby'     => 'post_modified',
		] ) );
	}

	public function get_rev_ids( $post ): array {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT ID FROM wp_posts
					WHERE post_parent=%d
					ORDER BY post_modified ASC',
				$post->ID
			)
		);

		return array_map(
			fn ($r) => (int) $r->ID,
			$results
		);
	}

	public function get_post_ids(): array {
		global $wpdb;

		$results = $wpdb->get_results(
			'SELECT post.ID FROM wp_posts AS post
				LEFT JOIN wp_posts AS parent ON (post.post_parent = parent.ID)
				WHERE ' . implode( ' AND ', $this->get_query_args( $this->request_filters ) ) . '
				ORDER BY post.post_modified DESC'
		);

		return array_map(
			fn ($r) => (int) $r->ID,
			$results
		);
	}

	/**
	 * Validate request filters
	 */
	public function filters_from_request( array $request ): array {
		$type_valid = in_array( $request['type'] ?? null, array_keys( self::event_types() ) );
		$user_valid = ! empty( $request['user'] );
		$vis_valid  = in_array( $request['visibility'] ?? null, array_keys( self::post_visibilities() ) );

		$date_from_valid  = DateTime::createFromFormat( 'Y-m-d', $request['date-from'] ?? '' ) !== false;
		$date_to_valid    = DateTime::createFromFormat( 'Y-m-d', $request['date-to'] ?? '' ) !== false;
		$date_field_valid = in_array( $request['date-field'] ?? null, ['post_modified', 'post_published'] );

		$search_valid = ! empty( $request['s'] );

		return [
			'type'       => $type_valid ? $request['type'] : null,
			'user'       => $user_valid ? (int) $request['user'] : null,
			'change'     => $request['change'] ?? null,
			'visibility' => $vis_valid ? $request['visibility'] : null,
			'date-from'  => $date_from_valid ? $request['date-from'] : null,
			'date-to'    => $date_to_valid ? $request['date-to'] : null,
			'date-field' => $date_field_valid ? $request['date-field'] : null,
			'search'     => $search_valid ? $request['s'] : null,
			'post-id'    => empty( $request['post-id'] ) ? null
				: implode( ',', array_map( 'intval', explode( ',', $request['post_id'] ) ) ),
		];
	}

	/**
	 * Get posts query args
	 */
	private function get_query_args( $filters ): array {
		return array_filter( [
			'post_type'   => 'post.post_type=\'revision\'',
			'post_name'   => $filters['type'] ? "post.post_name LIKE '%" . $filters['type'] . "%'" : null,
			'post_author' => $filters['user'] ? sprintf( "post.post_author=%d", $filters['user'] ) : null,
			'date_query'  => $this->resolve_date_query( $filters ),
			's'           => $this->resolve_search( $filters ),
			'post_id'     => $this->resolve_post_id( $filters ),
			'post_status' => $this->resolve_post_status( $filters ),
		], function ( $e ) { return ! is_null ( $e ); } );
	}

	private function resolve_post_id( $filters ): ?string {
		return empty( $filters['post_id'] )
			? null
			: sprintf( 'ID in (%s)', $filters['post_id'] );
	}

	private function resolve_date_query( $filters ): ?string {
		if ( empty( $filters['date-from'] ) && empty( $filters['date-to'] ) ) {
			return null;
		}

		return substr( ( new WP_Date_Query( [
			'relation' => 'AND',
			'column'   => 'post.' . $filters['date-field'],
			array_filter( [
				'after'     => $filters['date-from'],
				'before'    => $filters['date-to'],
				'inclusive' => true,
			], fn ($e) => ! is_null( $e ) ),
		] ) )->get_sql(), 4);
	}

	private function resolve_search( $filters ): ?string {
		if ( empty( $filters['search'] ) ) {
			return null;
		}

		return sprintf( "(
			post.post_title LIKE '%%%s%%'
			OR post.post_excerpt LIKE '%%%s%%'
			OR post.post_content LIKE '%%%s%%'
		)", $filters['search'], $filters['search'], $filters['search'] );
	}

	private function resolve_post_status( $filters ): ?string {
		if ( $filters['visibility'] === 'private' ) {
			return "parent.post_status IN ('draft', 'auto-draft', 'private', 'pending', 'trash')";
		}

		if ( $filters['visibility'] === 'password' ) {
			return "parent.post_password <> ''";
		}

		if ( $filters['visibility'] === 'public' ) {
			return "parent.post_status IN ('publish')";
		}

		return null;
	}

	/**
	 * Columns list for table.
	 */
	public function get_columns() {
		$default_columns = [
			'post_modified'   => 'Date',
			'post_name'       => 'Type',
			'post_author'     => 'User',
			'diff_data'       => 'Changed',
			'post_title'      => 'Title',
			'post_status'     => 'Status',
			'post_visibility' => 'Visibility',
			'post_published'  => 'Published',
		];

		return $default_columns;
	}

	/**
	 * All, hidden and sortable columns.
	 */
	private function get_column_headers() {
		return [
			$this->get_columns(),
		];
	}

	/**
	 * Default column value representation.
	 *
	 * @param array  $item Item.
	 * @param string $column_name Column name.
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ] ?? '';
	}

	public function column_post_modified( $item ) {
		$title_tpl = '%2$s';
		$link_tpl  = '<a href="%s" title="%s">%s</a>';
		$page_uri  = get_edit_post_link( $item['post_id'] );

		return sprintf(
			empty( $page_uri ) ? $title_tpl : $link_tpl,
			$page_uri,
			esc_attr( $item['post_modified'] ),
			$item['post_modified']
		);
	}

	public function column_post_name( $item ) {
		if ( $item['revision_ids'][0] === $item['post_id'] ) {
			return 'origin';
		}

		preg_match('#([0-9]{1,9})-([a-z]{1,10})-(.*)#', $item['post_name'], $matches);
		return $matches[2] ? ucfirst( $matches[2] ) : '?';
	}

	public function column_diff_data( $item ) {
		$changed = array_keys( $item['diff_data']['fields'] );

		return implode( ', ', $changed );
	}

	/**
	 * Post title display.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	public function column_post_title( $item ): string {
		$content = $item['post_title'] ?: __( '(no title)' );
		if ( 'trash' === $item['post_status'] ) {
			return (string) $item['post_parent'] ?? '';
		}

		$title_tpl = '%2$s';
		$link_tpl  = '<a href="%s" title="%s">%s</a>';
		$page_uri  = \get_permalink( $item['post_parent'] );

		return sprintf(
			empty( $page_uri ) ? $title_tpl : $link_tpl,
			$page_uri,
			esc_attr( $content ),
			( strlen( $content ) > 20 ? substr( $content, 0, 20 ) . '...' : $content )
		);
	}

	/**
	 * @return array
	 */
	public static function post_types(): array {
		return [
			'post'      => 'Post',
			'page'      => 'Page',
			'p4_action' => 'Action',
			'campaign'  => 'Campaign',
		];
	}

	/**
	 * @return array
	 */
	public static function post_statuses(): array {
		return [
			'draft'    => 'Draft',
			'pending'  => 'Pending review',
			'publish'  => 'Published',
		];
	}

	/**
	 * @return array
	 */
	public static function post_visibilities(): array {
		return [
			'public'   => 'Public',
			'password' => 'Password protected',
			'private'  => 'Private',
		];
	}

	/**
	 * @return array
	 */
	public static function event_types(): array {
		return [
			'revision' => 'Revision',
			'autosave' => 'Autosave',
		];
	}

	/**
	 * @return array
	 */
	public static function change_types(): array {
		return [
			'post_title'   => 'Title',
			'post_excerpt' => 'Excerpt',
			'post_content' => 'Content',
		];
	}

	/**
	 * Display full page with table
	 */
	public function display_page(): void {
		echo '<div class="wrap">
			<h1 class="wp-heading-inline">Posts activity</h1>
			<hr class="wp-header-end">';

		echo '<form id="post-activity" method="get">';
		$this->views();
		$this->search_box( 'Search in posts', 'post-activity' );
		$this->display();

		echo '<div class="tablenav bottom">';
		parent::pagination( 'bottom' );
		echo '<br class="clear" /></div>';

		echo '<input type="hidden" name="action"
			value="' . self::ACTION_NAME . '"/>';
		echo '</form>';
	}

	/**
	 * Show only top tablenav (duplicate form post bug)
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function display_tablenav( $which ) {
		if ( 'bottom' === $which ) {
			return;
		}
		parent::display_tablenav( $which );
	}

	/**
	 * Add filters to table.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function extra_tablenav( $which ) {
		echo '<div class="actions">';
		$this->event_type_dropdown();
		$this->event_user_dropdown();
		// $this->change_type_dropdown();
		$this->post_visibility_dropdown();
		echo '</div>';
		echo '<br class="clear"/>';
		parent::pagination( 'top' );
		echo '<div class="actions">';
		$this->date_fields();
		submit_button(
			__( 'Filter' ),
			'',
			'filter_action',
			false,
			[ 'id' => 'post-report-submit' ]
		);
		echo '</div>';
		echo '<style>
		tablenav .actions label, .tablenav .actions input, .tablenav .actions select {
			margin-bottom: 3px;
		}
		.tablenav .actions label, .tablenav .actions input{
			float: left;
			margin-right: 6px;
			max-width: 12.5rem;
		}
		.tablenav .actions label {
			line-height: 28px;
		}
		</style>';
	}

	/**
	 * Skip self pagination display to control its position in extra_tablenav.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function pagination( $which ) {
		return;
	}

	/**
	 * Select blocks namespaces.
	 */
	private function event_type_dropdown(): void {
		$types = self::event_types();

		echo '<select name="type" id="filter-by-type">';
		echo '<option value="">- All types -</option>';
		foreach ( $types as $type => $label ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $type ),
				esc_attr( $type === $this->request_filters['type'] ? 'selected' : '' ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * Select blocks namespaces.
	 */
	private function event_user_dropdown(): void {
		global $wpdb;
		$users = $wpdb->get_results(
			'SELECT ID, user_nicename, display_name FROM wp_users
				ORDER BY display_name ASC'
		);

		echo '<select name="user" id="filter-by-user">';
		echo '<option value="">- All users -</option>';
		foreach ( $users as $user ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $user->ID ),
				esc_attr( (int) $user->ID === $this->request_filters['user'] ? 'selected' : '' ),
				esc_html( $user->display_name )
			);
		}
		echo '</select>';
	}

	/**
	 * Select blocks namespaces.
	 */
	private function post_visibility_dropdown(): void {
		$vis = self::post_visibilities();

		echo '<select name="visibility" id="filter-by-visibility">';
		echo '<option value="">- All visibilities -</option>';
		foreach ( $vis as $visibility => $label ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $visibility ),
				esc_attr( $visibility === $this->request_filters['visibility'] ? 'selected' : '' ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	private function change_type_dropdown(): void {
		$changes = self::change_types();

		echo '<select name="change" id="filter-by-change">';
		echo '<option value="">- All changes -</option>';
		foreach ( $changes as $change => $label ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $change ),
				esc_attr( $change === $this->request_filters['change'] ? 'selected' : '' ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	private function date_fields(): void {
		echo '<label for="date-from">From: </label>';
		echo '<input type="date" id="filter-date-from" name="date-from"
			value="' . ( $this->request_filters['date-from'] ?? '' ) . '">&nbsp;';
		echo '<label for="date-to">To: </label>';
		echo '<input type="date" id="filter-date-to" name="date-to"
			value="' . ( $this->request_filters['date-to'] ?? '' ) . '">&nbsp;';
	}

	public static function url(): string {
		return admin_url( 'edit.php?page=posts_activity' );
	}

	public function current_url(): string {
		return $_SERVER['REQUEST_URI'];
	}

	/**
	 * Add redirection for filter action
	 */
	public static function set_hooks(): void {
		add_action(
			'admin_action_' . self::ACTION_NAME,
			function () {
				if ( empty( $_GET['_wpnonce'] ) ) {
					return;
				}

				$query = remove_query_arg(
					['_wp_http_referer', '_wpnonce', 'action', 'filter_action'],
					\parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY )
				);
				\parse_str( $query, $args );
				$args = array_filter( $args, fn($e) => ! empty( $e ) && $e !== '0' );

				wp_redirect( add_query_arg( $args, self::url() ) );
				exit;
			},
			10
		);
	}
}
