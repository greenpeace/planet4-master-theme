<?php
/**
 * P4 Campaigner Role
 *
 * @package P4MT
 */

namespace P4MT;

/**
 * Class P4_Campaigner.
 * Register custom 'campaigner' role and adds custom capabilities.
 */
class P4_Campaigner {

	/**
	 * P4_Campaigner constructor.
	 */
	public function __construct() {
	}

	/**
	 * Register campaigner role and add custom capabilities.
	 */
	public function register_role_and_add_capabilities() {
		$this->add_campaigner_role();
		$this->add_campaign_caps_admin();
		$this->add_campaign_caps_editor();
		$this->add_campaign_caps_author();
		$this->add_campaign_caps_contributor();
		$this->add_campaigner_caps_import();
	}

	/**
	 * Add Campaign capabilities to Administrator User.
	 */
	public function add_campaign_caps_admin() {
		$role = get_role( 'administrator' );

		$role->add_cap( 'edit_campaign' );
		$role->add_cap( 'read_campaign' );
		$role->add_cap( 'delete_campaign' );
		$role->add_cap( 'edit_campaigns' );
		$role->add_cap( 'edit_others_campaigns' );
		$role->add_cap( 'publish_campaigns' );
		$role->add_cap( 'read_private_campaigns' );
		$role->add_cap( 'delete_campaigns' );
		$role->add_cap( 'delete_private_campaigns' );
		$role->add_cap( 'delete_published_campaigns' );
		$role->add_cap( 'delete_others_campaigns' );
		$role->add_cap( 'edit_private_campaigns' );
		$role->add_cap( 'edit_published_campaigns' );
	}

	/**
	 * Add Campaign capabilities to Editor User.
	 */
	public function add_campaign_caps_editor() {
		$role = get_role( 'editor' );

		$role->add_cap( 'edit_campaign' );
		$role->add_cap( 'read_campaign' );
		$role->add_cap( 'delete_campaign' );
		$role->add_cap( 'edit_campaigns' );
		$role->add_cap( 'edit_others_campaigns' );
		$role->add_cap( 'publish_campaigns' );
		$role->add_cap( 'delete_campaigns' );
		$role->add_cap( 'delete_published_campaigns' );
		$role->add_cap( 'delete_others_campaigns' );
		$role->add_cap( 'edit_published_campaigns' );
	}

	/**
	 * Add Campaign capabilities to Author User.
	 */
	public function add_campaign_caps_author() {
		$role = get_role( 'author' );

		$role->add_cap( 'edit_campaign' );
		$role->add_cap( 'read_campaign' );
		$role->add_cap( 'delete_campaign' );
		$role->add_cap( 'edit_campaigns' );
		$role->add_cap( 'publish_campaigns' );
		$role->add_cap( 'delete_published_campaigns' );
		$role->add_cap( 'edit_published_campaigns' );
	}

	/**
	 * Add Campaign capabilities to Author User.
	 */
	public function add_campaign_caps_contributor() {
		$role = get_role( 'contributor' );

		$role->add_cap( 'edit_campaign' );
		$role->add_cap( 'read_campaign' );
		$role->add_cap( 'edit_campaigns' );
		$role->add_cap( 'edit_published_campaigns' );
	}

	/**
	 * Add Campaigner role.
	 */
	public function add_campaigner_role() {
		add_role(
			'campaigner',
			__( 'Campaigner', 'planet4-master-theme-backend' ),
			[
				// General.
				'read'                       => true,

				// Media upload.
				'upload_files'               => true,

				// Edit(own), View, Delete(own) posts.
				'edit_post'                  => true,
				'read_post'                  => true,
				'edit_posts'                 => true,
				'delete_posts'               => true,

				// Campaign capabilities.
				'edit_campaign'              => true,
				'read_campaign'              => true,
				'delete_campaign'            => true,
				'edit_campaigns'             => true,
				'edit_others_campaigns'      => true,
				'publish_campaigns'          => true,
				'read_private_campaigns'     => true,
				'delete_campaigns'           => true,
				'delete_private_campaigns'   => true,
				'delete_published_campaigns' => true,
				'delete_others_campaigns'    => true,
				'edit_private_campaigns'     => true,
				'edit_published_campaigns'   => true,
			]
		);
	}

	/**
	 * Add Campaign import capabilities to Campaigner User.
	 */
	public function add_campaigner_caps_import() {
		$role = get_role( 'campaigner' );

		$role->add_cap( 'import' );
	}
}
