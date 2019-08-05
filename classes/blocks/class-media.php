<?php
/**
 * Media block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Media_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Media extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'media_video';

	/**
	 * Media constructor.
	 */
	public function __construct() {

		register_block_type(
			'planet4-blocks/media-video',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'video_title'      => [
						'type' => 'string',
					],
					'description'      => [
						'type' => 'string',
					],
					'youtube_id'       => [
						'type' => 'string',
					],
					'video_poster_img' => [
						'type' => 'integer',
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {
		$media_url        = $fields['youtube_id'];
		$url_path_segment = wp_parse_url( $media_url, PHP_URL_PATH );
		$description      = $fields['description'] ?? '';

		// Assume that a non-URL is a YouTube video ID, for back compat.
		if ( false === strstr( $media_url, '/' ) ) {
			$media_url = "https://www.youtube.com/watch?v={$media_url}";
		}

		if ( preg_match( '/^(https?)?:\/\/soundcloud.com\//i', $media_url ) ) {
			// Soundcloud track URL (differentiated for styling purposes).
			$type       = 'audio';
			$embed_html = wp_oembed_get( $media_url );
		} elseif ( preg_match( '/\.mp4$/', $url_path_segment ) ) {
			// Bare video URL.
			$type   = 'video';
			$poster = empty( $fields['video_poster_img'] )
				? '' : wp_get_attachment_image_src( $fields['video_poster_img'], 'large' );

			$embed_html = wp_video_shortcode(
				[
					'src'    => $media_url,
					'poster' => $poster[0],
				]
			);
		} elseif ( preg_match( '/\.(mp3|wav|ogg)$/', $url_path_segment ) ) {
			// Bare audio URL.
			$type       = 'audio';
			$embed_html = wp_audio_shortcode( [ 'src' => $media_url ] );
		} else {
			$type       = 'video';
			$embed_html = wp_oembed_get( $media_url );
		}

		$data = [
			'fields' => [
				'title'       => $fields['video_title'],
				'description' => $description,
				'embed_html'  => str_replace( 'youtube.com', 'youtube-nocookie.com', $embed_html ),
				'type'        => $type,
			],
		];
		return $data;
	}
}
