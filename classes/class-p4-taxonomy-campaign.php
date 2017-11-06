<?php

use Timber\Timber;

if ( ! class_exists( 'P4_Taxonomy_Campaign' ) ) {

	/**
	 * Class P4_Taxonomy_Campaign
	 */
	class P4_Taxonomy_Campaign {

		/** @var array $context */
		public $context = [];

		/** @var array $templates */
		protected $templates = [];

		/**
		 * P4_Taxonomy_Campaign constructor.
		 *
		 * @param array $templates An indexed array with template file names. The first to be found will be used.
		 * @param array $context An associative array with all the context needed to render the template found first.
		 */
		public function __construct( $templates = [ 'archive.twig', 'index.twig' ], $context = [] ) {
			$this->templates = $templates;
			$this->context   = $context;
		}

		/**
		 * Add a block to the Campaign template.
		 *
		 * @param string $block_name The name of the block to be added.
		 * @param array  $data An associative array with data needed by the block.
		 */
		public function add_block( $block_name, $data ) {

			if ( $block_name && $data ) {

				$shortcode = '[shortcake_' . $block_name;
				if ( $data ) {
					foreach ( $data as $param => $value ) {
						$shortcode .= " $param=\"$value\"";
					}
				}
				$shortcode .= ' /]';

				$this->context['blocks'][ $block_name ] = do_shortcode( $shortcode );
			}
		}

		/**
		 * View the Campaign template.
		 */
		public function view() {
			Timber::render( $this->templates, $this->context );
		}
	}
}
