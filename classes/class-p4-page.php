<?php

class P4_Page {
	/** @var integer $page_id */
	protected $page_id;
	/** @var array $context */
	public $context;
	/** @var object $post */
	public $post;
	/** @var object $page_meta_data */
	public $page_meta_data;

	/**
	 * P4_Page constructor.
	 */
	public function __construct() {
		$this->context = Timber::get_context();
		$this->post = new TimberPost();

		$this->context['post'] = $this->post;

		$this->page_meta_data = get_post_meta( $this->post->ID );

		$this->context['header_title']        = null === $this->page_meta_data['p4_title'][0] ? $this->post->title : $this->page_meta_data['p4_title'][0];
		$this->context['header_subtitle']     = $this->page_meta_data['p4_subtitle'][0];
		$this->context['header_description']  = $this->page_meta_data['p4_description'][0];
		$this->context['header_button_title'] = $this->page_meta_data['p4_button_title'][0];
		$this->context['header_button_link']  = $this->page_meta_data['p4_button_link'][0];

		// Footer Items. TODO: object cache it since it should not change from post to post
		$this->context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
		$this->context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
		$this->context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
		$this->context['copyright_text']        = get_option( 'copyright', '' ) ? get_option( 'copyright' ) : '';

		$page_tags = wp_get_post_tags( $post->ID );
		$this->context['page_tags'] = $page_tags;

	}

}
