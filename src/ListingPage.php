<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Features\Dev\ListingPageGridView;
use P4\MasterTheme\Features\ListingPagePagination;
use Timber\Timber;

class ListingPage {
	/**
	 * Page context
	 */
	protected $context;
	/**
	 * Templates
	 */
	protected $templates;
	/**
	 * Post Type
	 */
	protected $post_type;

	/**
	 * ListingPage constructor.
	 *
	 * @param $context Page context.
	 * @param $templates Templates.
	 */
	public function __construct($context, $post_type, $templates = array()) {
		$this->context 	 = $context;
		$this->post_type = $post_type;
		$this->templates = $templates;

		$this->render();
	}

	protected function render() {
		global $wp_query;
		global $wp;

		if ( ListingPagePagination::is_active() ) {
			$view = ListingPageGridView::is_active() ? 'grid' : 'list';

			$query_template = file_get_contents( get_template_directory() . "/parts/query-$view.html" );

			$content = do_blocks( $query_template );

			$this->context['query_loop'] = $content;
			Timber::render( $this->templates, $this->context );
			exit();
		} else {
			// Only applied to the "Load More" feature.
			if ( null !== get_query_var( 'page_num' ) ) {
				$wp_query->query_vars['page'] = get_query_var( 'page_num' );
			}

			$post_args = [
				'posts_per_page' => 10,
				'post_type'      => $this->post_type,
				'paged'          => 1,
				'has_password'   => false,  // Skip password protected content.
			];

			if ( get_query_var( 'page' ) ) {
				$this->templates    = [ 'tease-taxonomy-post.twig' ];
				$post_args['paged'] = get_query_var( 'page' );
				$pagetype_posts     = new \Timber\PostQuery( $post_args, Post::class );

				foreach ( $pagetype_posts as $pagetype_post ) {
					$this->context['post'] = $pagetype_post;
				}
			} else {
				$pagetype_posts   = new \Timber\PostQuery( $post_args, Post::class );
				$this->context['posts'] = $pagetype_posts;
				$this->context['url']   = home_url( $wp->request );
			}
		}

		Timber::render( $this->templates, $this->context );
		exit();
	}
}
