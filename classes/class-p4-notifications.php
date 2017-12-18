<?php

if ( ! class_exists( 'P4_Notifications' ) ) {
	/**
	 * Class P4_Notifications
	 */
	class P4_Notifications {
		
		/**
		 * Notification constructor.
		 */
		public function __construct() {
			$this->hooks();
		}
		/**
		 * Class hooks.
		 */
		private function hooks() { 
			add_action( 'publish_post', array( $this, 'send_mails_on_publish' ), 10, 2 );
		}

		/**
		 * Call on publish a post.
		 */
		public function send_mails_on_publish( $ID, $post ) {
			$emails  = [];
			$args    = [
				'role' => 'editor',
			];
			$users   = get_users( $args );
			if( $users ) {
				foreach( $users as $user ) {
					$emails[] = $user->user_email;
				}
			}

			if( $emails ) {
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$name     = $post->post_name;
				$subject  = __( 'New post published', 'planet4-master-theme' );
				$body     = sprintf( __('Hello Team, <br><br>', 'planet4-master-theme' ) );
				$body    .= sprintf( __('The post <strong> %s </strong> has been published. <br>', 'planet4-master-theme' ), $name );
				$body    .= sprintf( __('Post link : %s <br><br>', 'planet4-master-theme' ), get_permalink( $post ) );
				$body    .= sprintf( __('Thanks<br>', 'planet4-master-theme' ) );
				$body    .= sprintf( __('Greenpeace IT', 'planet4-master-theme' ) );

				wp_mail( $emails, $subject, $body, $headers );
			}
		}
	}
}
