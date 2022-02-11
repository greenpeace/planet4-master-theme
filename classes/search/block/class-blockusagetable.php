<?php
/**
 * Table displaying blocks usage
 *
 * @package P4BKS\Controllers
 */

namespace P4GBKS\Search\Block;

use InvalidArgumentException;
use WP_List_Table;
use WP_Block_Type_Registry;
use P4GBKS\Search\Block\Query\Parameters;
use P4GBKS\Controllers\Menu\Blocks_Usage_Controller;

/**
 * Show block usage, using native WordPress table
 */
class BlockUsageTable extends WP_List_Table {

	public const DEFAULT_GROUP_BY = 'block_type';

	public const DEFAULT_POST_STATUS = [ 'publish', 'private', 'draft', 'pending', 'future' ];

	/**
	 * @var BlockUsage
	 */
	private $block_usage;

	/**
	 * @var WP_Block_Type_Registry
	 */
	private $block_registry;

	/**
	 * @var string[] Search filters requested.
	 */
	private $search_params = [];

	/**
	 * @var string Group column.
	 */
	private $group_by = self::DEFAULT_GROUP_BY;

	/**
	 * @var string[]|null Sort order.
	 */
	private $sort_by = [ 'post_title', 'post_id' ];

	/**
	 * @var string[]
	 */
	private $allowed_groups = [ 'block_type', 'post_id', 'post_title' ];

	/**
	 * @var string[]|null Columns name => title.
	 */
	private $columns = null;

	/**
	 * @var string|null Latest row content displayed.
	 */
	private $latest_row = null;

	/**
	 * @var string[]|null Blocks namespaces.
	 */
	private $blocks_ns = null;

	/**
	 * @var string[]|null Blocks types.
	 */
	private $blocks_types = null;

	/**
	 * @var string[]|null Blocks registered.
	 */
	private $blocks_registered = null;

	/**
	 * @var string[]|null Blocks allowed.
	 */
	private $blocks_allowed = null;

	/**
	 * @var ?string Special filter.
	 */
	private $special = null;

	/**
	 * @param array $args Args.
	 * @throws InvalidArgumentException Throws on missing parameter.
	 * @see WP_List_Table::__construct()
	 */
	public function __construct( $args = [] ) {
		$args['plural'] = 'blocks';
		parent::__construct( $args );

		$this->block_usage    = $args['block_usage'] ?? null;
		$this->block_registry = $args['block_registry'] ?? null;

		if ( ! ( $this->block_usage instanceof BlockUsage ) ) {
			throw new InvalidArgumentException(
				'Table requires a BlockUsage instance.'
			);
		}
		if ( ! ( $this->block_registry instanceof WP_Block_Type_Registry ) ) {
			throw new InvalidArgumentException(
				'Table requires a WP_Block_Type_Registry instance.'
			);
		}
	}

	/**
	 * Prepares table data.
	 *
	 * @param Parameters $search_params Search parameters.
	 * @param string     $group_by      Grouping dimension.
	 * @param ?string    $special  Unregistered blocks only.
	 */
	public function prepare_items(
		?Parameters $search_params = null,
		?string $group_by = null,
		?string $special = null
	): void {
		if ( in_array( $group_by, $this->allowed_groups, true ) ) {
			$this->group_by = $group_by;
		}

		$this->search_params = $search_params
			->with_post_status( self::DEFAULT_POST_STATUS )
			->with_order( array_merge( [ $this->group_by ], $this->sort_by ) );

		$this->items = $this->block_usage->get_blocks( $this->search_params );

		$this->special = $special;
		if ( 'unregistered' === $this->special ) {
			$this->items = $this->filter_for_unregistered( $this->items );
		}
		if ( 'unallowed' === $this->special ) {
			$this->items = $this->filter_for_unallowed( $this->items );
		}

		$this->set_block_filters();
		$this->_column_headers = $this->get_column_headers();
	}

	/**
	 * Filter items to keep unregistered blocks only.
	 *
	 * @param array $items Blocks not registered.
	 */
	private function filter_for_unregistered( array $items ): array {
		$this->set_registered_blocks();
		return array_filter(
			$items,
			fn ( $i ) => ! in_array( $i['block_type'], $this->blocks_registered, true ) && 'core-embed' !== $i['block_ns']
		);
	}

	/**
	 * Filter items to keep unallowed blocks only.
	 *
	 * @param array $items Blocks not registered.
	 */
	private function filter_for_unallowed( array $items ): array {
		$this->set_allowed_blocks();
		return array_filter(
			$items,
			fn ( $i ) => ! in_array( $i['block_type'], $this->blocks_allowed, true )
		);
	}

	/**
	 * Set dropdown filters content.
	 */
	private function set_block_filters(): void {
		$this->set_registered_blocks();
		$this->set_allowed_blocks();
		$this->blocks_types = array_unique(
			array_merge(
				$this->blocks_registered,
				$this->blocks_allowed
			)
		);

		$namespaces = array_filter(
			array_unique(
				array_map(
					static function ( string $name ) {
						return explode( '/', $name )[0] ?? null;
					},
					$this->blocks_types
				)
			)
		);
		// @todo WP 5.? : parse blocks variations when available
		sort( $namespaces );
		$this->blocks_ns = $namespaces;
	}

	/**
	 * Set the registered blocks list.
	 */
	private function set_registered_blocks() {
		$names = array_keys(
			$this->block_registry->get_all_registered()
		);
		sort( $names );
		$this->blocks_registered = $names;
	}

	/**
	 * Set the allowed blocks list.
	 */
	private function set_allowed_blocks() {
		$post_types = array_filter(
			get_post_types( [ 'show_in_rest' => true ] ),
			fn ( $t ) => post_type_supports( $t, 'editor' )
		);

		$allowed = [];
		foreach ( $post_types as $type ) {
			$context = new \WP_Block_Editor_Context(
				[ 'post' => (object) [ 'post_type' => $type ] ]
			);
			$allowed = array_merge(
				$allowed,
				array_values( get_allowed_block_types( $context ) )
			);
		}

		$allowed = array_unique( $allowed );
		sort( $allowed );
		$this->blocks_allowed = $allowed;
	}

	/**
	 * Columns list for table.
	 */
	public function get_columns() {
		if ( null !== $this->columns ) {
			return $this->columns;
		}

		$default_columns = [
			'post_title'    => 'Title',
			'block_type'    => 'Block',
			'block_attrs'   => 'Attributes',
			'post_date'     => 'Created',
			'post_modified' => 'Modified',
			'post_id'       => 'ID',
			'post_status'   => 'Status',
		];

		$this->columns = array_merge(
			[ $this->group_by => $default_columns[ $this->group_by ] ],
			$default_columns
		);

		return $this->columns;
	}

	/**
	 * All, hidden and sortable columns.
	 */
	private function get_column_headers() {
		return [
			$this->get_columns(),
			[],
			[ 'post_title', 'post_date', 'post_modified' ],
		];
	}

	/**
	 * Available grouping as views.
	 */
	protected function get_views() {
		$link_tpl        = '<a href="%s">%s</a>';
		$active_link_tpl = '<a class="current" href="%s">%s</a>';
		return [
			'block_type' => sprintf(
				'block_type' === $this->group_by ? $active_link_tpl : $link_tpl,
				add_query_arg( 'group', 'block_type' ),
				'Group by block name'
			),
			'post_title' => sprintf(
				'post_title' === $this->group_by ? $active_link_tpl : $link_tpl,
				add_query_arg( 'group', 'post_title' ),
				'Group by post title'
			),
			'post_id'    => sprintf(
				'post_id' === $this->group_by ? $active_link_tpl : $link_tpl,
				add_query_arg( 'group', 'post_id' ),
				'Group by post ID'
			),
		];
	}


	/**
	 * Displays the list of views available on this table.
	 */
	public function views() {
		parent::views();

		$link_tpl        = '<a href="%s">%s</a>';
		$active_link_tpl = '<a class="current" href="%s">%s</a>';
		$unique_views    = [
			'unregistered' => sprintf(
				'unregistered' === $this->special ? $active_link_tpl : $link_tpl,
				'unregistered' === $this->special
					? Blocks_Usage_Controller::url()
					: add_query_arg( [ 'unregistered' => '' ], Blocks_Usage_Controller::url() ),
				'Not registered'
			),
			'unallowed'    => sprintf(
				'unallowed' === $this->special ? $active_link_tpl : $link_tpl,
				'unallowed' === $this->special
					? Blocks_Usage_Controller::url()
					: add_query_arg( [ 'unallowed' => '' ], Blocks_Usage_Controller::url() ),
				'Not allowed'
			),
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

	/**
	 * Select blocks namespaces.
	 */
	private function blockns_dropdown() {
		sort( $this->blocks_ns );
		$filter = $this->search_params->namespace() ?? null;

		echo '<select name="namespace" id="filter-by-ns" onchange="filterBlockNames();">';
		echo '<option value="">- All namespaces -</option>';
		foreach ( $this->blocks_ns as $ns ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $ns ),
				esc_attr( $filter === $ns ? 'selected' : '' ),
				esc_html( $ns )
			);
		}
		echo '</select>';
	}

	/**
	 * Select blocks types.
	 */
	private function blocktype_dropdown() {
		sort( $this->blocks_types );
		$filter = $this->search_params->name() ?? null;

		echo '<select name="name" id="filter-by-name">';
		echo '<option value="">- All blocks -</option>';
		foreach ( $this->blocks_types as $type ) {
			echo sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $type ),
				esc_attr( $filter === $type ? 'selected' : '' ),
				esc_html( $type )
			);
		}
		echo '</select>';

		echo "<script>
			const filterBlockNames = () => {
				let selectedNs = document.getElementById('filter-by-ns').selectedOptions[0].value;
				let select = document.getElementById('filter-by-name');
				for (let option of select.options) {
					let display = selectedNs.length <= 0
						|| option.value.length <= 0
						|| option.value.startsWith(`\${selectedNs}/`);
					option.style.display = display ? 'inline' : 'none';
				}
				if ( selectedNs.length >= 1 ) {
					select.value = '';
				}
			}
			filterBlockNames();
		</script>";
	}

	/**
	 * Add filters to table.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function extra_tablenav( $which ) {
		echo '<div class="actions">';
		$this->blockns_dropdown();
		$this->blocktype_dropdown();
		submit_button(
			__( 'Filter', 'planet4-blocks-backend' ),
			'',
			'filter_action',
			false,
			[ 'id' => 'block-query-submit' ]
		);
		echo '</div>';
	}

	/**
	 * Add pagination information to table.
	 *
	 * @param string $which Tablenav identifier.
	 */
	protected function pagination( $which ) {
		echo sprintf(
			'<div class="tablenav-pages">
			<span class="displaying-num">%d blocks, %d posts</span>
			</div>',
			esc_html( $this->block_count() ),
			esc_html( $this->post_count() )
		);
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
	 * Block option display.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	public function column_block_attrs( $item ): string {
		$content = $item['block_attrs'] ?? null;
		if ( empty( $content ) ) {
			return '';
		}

		//phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$content = print_r( $content, true );
		$content = trim( substr( $content, 5, strlen( $content ) ) );

		return sprintf(
			'<span title="%s">%s</span>',
			esc_attr( $content ),
			esc_html(
				strlen( $content ) > 30
				? substr( $content, 0, 30 ) . '...'
				: $content
			)
		);
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
		$link_tpl  = '<a href="%s" title="%s">%s</a>';
		$page_uri  = get_page_uri( $item['post_id'] );

		return sprintf(
			empty( $page_uri ) ? $title_tpl : $link_tpl,
			$page_uri,
			esc_attr( $content ),
			( strlen( $content ) > 45 ? substr( $content, 0, 45 ) . '...' : $content )
		);
	}

	/**
	 * Post ID display.
	 *
	 * @param array $item Item.
	 * @return string
	 */
	public function column_post_id( $item ): string {
		return sprintf(
			'<a href="%s">%s</a>',
			get_edit_post_link( $item['post_id'] ),
			$item['post_id']
		);
	}

	/**
	 * Full row display, edited for grouping functionality.
	 *
	 * @param array $item Item.
	 */
	public function single_row( $item ) {
		$cols      = $this->get_columns();
		$colspan   = count( $cols );
		$first_col = array_key_first( $cols );

		if ( $this->latest_row !== $item[ $first_col ] ) {
			echo '<tr>';
			echo sprintf(
				'<th colspan="%s"><strong>%s</strong></th>',
				esc_attr( $colspan ),
				esc_html( $item[ $first_col ] )
			);
			echo '</tr>';
		}

		$this->latest_row   = $item[ $first_col ];
		$item[ $first_col ] = '';
		parent::single_row( $item );
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
	 * Block count in search result.
	 *
	 * @return int
	 */
	public function block_count(): int {
		return count( $this->items );
	}

	/**
	 * Post count in search result.
	 *
	 * @return int
	 */
	public function post_count(): int {
		return count(
			array_unique(
				array_column(
					$this->items,
					'post_id'
				)
			)
		);
	}

	/**
	 * Search parameters
	 */
	public function get_search_params(): Parameters {
		return $this->search_params;
	}
}
