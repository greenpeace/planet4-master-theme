<?php

declare(strict_types=1);

namespace P4\MasterTheme\Report;

use WP_List_Table;
use WP_Date_Query;
use DateTime;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class PostReportTable extends WP_List_Table {
	public const ACTION_NAME = 'post_report';

	private $request;
	private $request_filters = [];
	private $query_filters   = [];

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

		$posts = get_posts( array_merge(
			$this->query_filters,
			['numberposts' => -1]
		) );


		$items = array_map(
			function ( $p ) {
				$visibility = ! empty( $p->post_password )
					? 'password'
					: ( in_array( $p->post_status, ['private', 'draft', 'pending'] ) ? 'private' : 'public' );

				return [
					'post_id'         => $p->ID,
					'post_title'      => $p->post_title,
					'post_type'       => self::post_types()[ $p->post_type ],
					'post_author'     => get_the_author_meta( 'display_name', $p->post_author ),
					'post_status'     => self::post_statuses()[ $p->post_status ],
					'post_visibility' => self::post_visibilities()[ $visibility ],
					'post_revisions'  => count( wp_get_post_revisions( $p->ID ) ),
					'post_modified'   => $p->post_modified,
					'post_published'  => $p->post_date,
				];
			},
			$posts
		);

		// @todo: extract this + get_posts() to a sql query
		if ( isset( $this->query_filters['has_revisions'] ) ) {
			if ( true === $this->query_filters['has_revisions'] ) {
				$items = array_filter(
					$items,
					function ( $item ) {
						return $item['post_revisions'] > 1;
					}
				);
			}

			if ( false === $this->query_filters['has_revisions'] ) {
				$items = array_filter(
					$items,
					function ( $item ) {
						return $item['post_revisions'] <= 1;
					}
				);
			}
		}

		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$total_items  = count( $items );
        $this->items  = array_slice( $items,( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items/$per_page )
			)
		);

		$this->_column_headers = $this->get_column_headers();
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
	 * Validate request filters
	 */
	public function filters_from_request( array $request ): array {
		$type_valid   = in_array( $request['type'] ?? null, array_keys( self::post_types() ) );
		$status_valid = in_array( $request['status'] ?? null, array_keys( self::post_statuses() ) );
		$vis_valid    = in_array( $request['visibility'] ?? null, array_keys( self::post_visibilities() ) );

		$date_from_valid  = DateTime::createFromFormat( 'Y-m-d', $request['date-from'] ?? '' ) !== false;
		$date_to_valid    = DateTime::createFromFormat( 'Y-m-d', $request['date-to'] ?? '' ) !== false;
		$date_field_valid = in_array( $request['date-field'] ?? null, ['post_modified', 'post_published'] );

		$search_valid = ! empty( $request['s'] );

		return [
			'type'       => $type_valid ? $request['type'] : null,
			'status'     => $status_valid ? $request['status'] : null,
			'visibility' => $vis_valid ? $request['visibility'] : null,
			'revisions'  => isset( $request['revisions'] ) ? $request['revisions'] !== '0' : null,
			'date-from'  => $date_from_valid ? $request['date-from'] : null,
			'date-to'    => $date_to_valid ? $request['date-to'] : null,
			'date-field' => $date_field_valid ? $request['date-field'] : null,
			'search'     => $search_valid ? $request['s'] : null,
		];
	}

	/**
	 * Get posts query args
	 */
	private function get_query( $filters ): array {
		return array_filter( [
			'post_type'     => $this->resolve_post_type( $filters ),
			'post_status'   => $this->resolve_post_status( $filters ),
			'orderby'       => 'post_modified',
			'has_password'  => $this->resolve_has_password( $filters ),
			'has_revisions' => $filters['revisions'],
			'date_query'    => $this->resolve_date_query( $filters ),
			's' 			=> $filters['search'],
            'paged' => $filters['paged'] ?? 0,
            'posts_per_page' => 50,
		], function ( $e ) { return ! is_null ( $e ); } );
	}

	private function resolve_post_type( $filters ): ?array {
		return empty( $filters['type'] )
			? array_keys( self::post_types() )
			: [ $filters['type'] ];
	}

	private function resolve_post_status( $filters ): ?array {
		$statuses = array_merge( array_keys( self::post_statuses() ), ['private'] );
		if ( ! empty( $filters['status'] ) ) {
			return in_array( $filters['status'], $statuses )
				? [ $filters['status'] ]
				: $statuses;
		}

		if ( 'password' === $filters['visibility'] ) {
			return $statuses;
		}

		if ( 'private' === $filters['visibility'] ) {
			return ['draft', 'pending', 'private'];
		}

		return $statuses;
	}

	private function resolve_has_password( $filters ): ?bool {
		return ! empty( $filters['visibility'] ) && 'password' === $filters['visibility'] ?: null;
	}

	private function resolve_date_query( $filters ): ?array {
		if ( empty( $filters['date-from'] ) && empty( $filters['date-to'] ) ) {
			return null;
		}

		return [
			'relation' => 'AND',
			'column'   => $filters['date-field'],
			array_filter( [
				'after'     => $filters['date-from'],
				'before'    => $filters['date-to'],
				'inclusive' => true,
			], fn ($e) => ! is_null( $e ) ),
		];
	}

	/**
	 * Columns list for table.
	 */
	public function get_columns() {
		$default_columns = [
			'post_title'      => 'Title',
			'post_type'       => 'Type',
			'post_author'     => 'Author',
			'post_status'     => 'Status',
			'post_visibility' => 'Visibility',
			'post_revisions'  => 'Revisions',
			'post_modified'   => 'Modified',
			'post_published'  => 'Published',
		];

		return $default_columns;
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
	 * Skip self pagination display to control its position in extra_tablenav.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function pagination( $which ) {
		return;
	}

	/**
	 * Add filters to table.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function extra_tablenav( $which ) {
		echo '<div class="actions">';
		$this->post_type_dropdown();
		$this->post_status_dropdown();
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
	 * Select blocks namespaces.
	 */
	private function post_type_dropdown(): void {
		$types = self::post_types();

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
	private function post_status_dropdown(): void {
		$statuses = self::post_statuses();

		echo '<select name="status" id="filter-by-status">';
		echo '<option value="">- All statuses -</option>';
		foreach ( $statuses as $status => $label ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $status ),
				esc_attr( $status === $this->request_filters['status'] ? 'selected' : '' ),
				esc_html( $label )
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

	private function date_fields(): void {
		echo '<label for="date-from">From: </label>';
		echo '<input type="date" id="filter-date-from" name="date-from"
			value="' . ( $this->request_filters['date-from'] ?? '' ) . '">&nbsp;';
		echo '<label for="date-to">To: </label>';
		echo '<input type="date" id="filter-date-to" name="date-to"
			value="' . ( $this->request_filters['date-to'] ?? '' ) . '">&nbsp;';
		echo '<label for="date-field">On: </label>';
		echo '<select name="date-field" id="filter-date-field">';
		echo '<option value="post_modified" ' . (
				$this->request_filters['date-field'] === 'post_modified' ? 'selected' : ''
			) . '>Modified date</option>';
		echo '<option value="post_published" ' . (
				$this->request_filters['date-field'] === 'post_published' ? 'selected' : ''
			) . '>Published date</option>';
		echo '</select>&nbsp;';
	}

	/**
	 * Displays the list of views available on this table.
	 */
	public function views() {
		parent::views();

		$revs = $this->request_filters['revisions'];

		$link_tpl        = '<a href="%s">%s</a>';
		$active_link_tpl = '<a class="current" href="%s">%s</a>';
		$unique_views    = [
			'has_revisions' => sprintf(
				true === $revs ? $active_link_tpl : $link_tpl,
				true === $revs
					? self::url()
					: add_query_arg( [ 'revisions' => '1' ], self::current_url() ),
				'Has revisions'
			),
			'no_revisions' => sprintf(
				false === $revs ? $active_link_tpl : $link_tpl,
				false === $revs
					? self::url()
					: add_query_arg( [ 'revisions' => '0' ], self::current_url() ),
				'No revisions'
			),
			'clear' => sprintf( $link_tpl, self::current_url(), 'Clear' ),
		];

		$views = [];
		echo '<div style="clear: both;"><ul class="subsubsub" style="margin: 0;">';
		foreach ( $unique_views as $class => $view ) {
			$views[ $class ] = "\t<li class='$class'>$view";
		}
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo implode( " |</li>\n", $views ) . "</li>\n";
		echo '</ul></div>';
	}

	public static function url(): string {
		return admin_url( 'edit.php?page=posts_report_beta' );
	}

	public function current_url(): string {
		return $_SERVER['REQUEST_URI'];
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

	/**
	 * Post title display.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	public function column_post_title( $item ): string {
		$content = $item['post_title'] ?? null;
		if ( empty( $content ) ) {
			return '';
		}

		$title_tpl = '%2$s';
		$link_tpl  = '<a href="%s" title="%s" class="row-title">%s</a>';
		$page_uri  = get_page_uri( $item['post_id'] );

		return sprintf(
			empty( $page_uri ) ? $title_tpl : $link_tpl,
			$page_uri,
			esc_attr( $content ),
			( strlen( $content ) > 45 ? substr( $content, 0, 45 ) . '...' : $content )
		);
	}

	public function column_post_revisions( $item ): string {
		$count = $item['post_revisions'] ?? 0;
		if ( empty( $count ) ) {
			return (string) $count;
		}

		$title_tpl = '%2$s';
		$link_tpl  = '<a href="%s" title="%s">%s</a>';
		$page_uri  = wp_get_post_revisions_url( $item['post_id'] );

		return sprintf(
			empty( $page_uri ) ? $title_tpl : $link_tpl,
			$page_uri,
			esc_attr( $count . ' revisions' ),
			$count
		);
	}

	/**
	 * Add action links to a row
	 *
	 * @param array  $item        Item.
	 * @param string $column_name Current column name.
	 * @param string $primary     Primary column name.
	 *
	 * phpcs:disable WordPress.WP.I18n.TextDomainMismatch
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		if ( $column_name !== $primary ) {
			return '';
		}

		$actions = [];
		$id      = (int) $item['post_id'];
		$title   = $item['post_title'];

		$actions['edit'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			get_edit_post_link( $item['post_id'] ),
			/* translators: %s: Post title. */
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'default' ), $title ) ),
			__( 'Edit', 'default' )
		);

		if ( in_array( $item['post_status'], [ 'pending', 'draft', 'future' ], true ) ) {
			$preview_link    = get_preview_post_link( $id );
			$actions['view'] = sprintf(
				'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
				esc_url( $preview_link ),
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'default' ), $title ) ),
				__( 'Preview', 'default' )
			);
		} elseif ( 'trash' !== $item['post_status'] ) {
			$actions['view'] = sprintf(
				'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
				get_permalink( $item['post_id'] ),
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;', 'default' ), $title ) ),
				__( 'View', 'default' )
			);
		}

		$actions['clone'] = '<a href="' . duplicate_post_get_clone_post_link( $id, 'display', false ) .
			'" aria-label="' . esc_attr(
			/* translators: %s: Post title. */
				sprintf( __( 'Clone &#8220;%s&#8221;', 'duplicate-post' ), $title )
			) . '">' .
			esc_html_x( 'Clone', 'verb', 'duplicate-post' ) . '</a>';

		$actions['edit_as_new_draft'] = '<a href="' . duplicate_post_get_clone_post_link( $id ) .
			'" aria-label="' . esc_attr(
			/* translators: %s: Post title. */
				sprintf( __( 'New draft of &#8220;%s&#8221;', 'duplicate-post' ), $title )
			) . '">' .
			esc_html__( 'New Draft', 'duplicate-post' ) .
			'</a>';

		return $this->row_actions( $actions );
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
