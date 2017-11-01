<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */


$context = Timber::get_context();
$post    = Timber::query_post();

$page_meta_data             = get_post_meta( $post->ID );
$articles_title             = $page_meta_data['p4_articles_title'][0];
$articles_count             = intval( $page_meta_data['p4_articles_count'][0] );
$articles_count             = $articles_count == 0 ? 3 : $articles_count;
$context['author_override'] = $page_meta_data['p4_author_override'][0];

if ( ! empty( $articles_title ) ) {
	$post->articles = do_shortcode( "[shortcake_articles article_heading='$articles_title' article_count='$articles_count' /]" );
}
$context['post'] = $post;

// Populate the arguments array for the comment form in order to customize the form's fields
$comments_args            = array(

	'title_reply'          => __( 'Leave Your Reply', 'planet4-master-theme' ),
	'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title">',
	'title_reply_after'    => '</h3>',
	'submit_button'        => '<button type="submit" class="btn btn-medium secondary-button mt-64">' . __( 'Post Comment', 'planet4-master-theme' ) . '</button>',
	'comment_notes_before' => '',
	'comment_notes_after'  => '',
	'comment_field'        => '<div class="form-group mb-0">
									<label for="comments-textarea">' . __( 'Comment', 'planet4-master-theme' ) . ' *</label>
									<textarea class="form-control" id="comment" name="comment" rows="6" placeholder="Your Comment"></textarea>
								</div>',

	'fields' => apply_filters( 'comment_form_default_fields', array(

			'author' => '<div class="form-group">
							<label for="comments-name-input">' . __( 'Name', 'planet4-master-theme' ) . ' *</label>
							<input id="author" name="author" type="text" class="form-control" placeholder="' . __( 'Your Name', 'planet4-master-theme' ) . '">
						</div>',

			'email' => '<div class="form-group mb-0">
							<label for="comments-email-input">' . __( 'Email', 'planet4-master-theme' ) . ' *</label>
							<input type="email" class="form-control" id="email" name="email" placeholder="' . __( 'Your Email', 'planet4-master-theme' ) . '">
						</div>',
		)
	)
);
$context['comments_args'] = $comments_args;


if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
