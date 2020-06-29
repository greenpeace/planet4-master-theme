<?php

namespace P4\MasterTheme\ImageArchive;

class Image implements \JsonSerializable {
	public const ARCHIVE_ID_META_KEY = 'gp_archive_id';

	/**
	 * Media Library System Identifier.
	 *
	 * @var $archive_id
	 */
	private $archive_id;

	/**
	 * Image Title.
	 *
	 * @var $title
	 */
	private $title;

	/**
	 * Image Caption.
	 *
	 * @var $caption
	 */
	private $caption;

	/**
	 * Image Credit.
	 *
	 * @var $credit
	 */
	private $credit;

	/**
	 * Image restrictions.
	 *
	 * @var $restrictions
	 */
	private $restrictions;

	/**
	 * The attachment id if the image is stored in WordPress db.
	 *
	 * @var $wordpress_id
	 */
	private $wordpress_id;

	/**
	 * The Original language title of attachment.
	 *
	 * @var $original_language_title
	 */
	private $original_language_title;

	/**
	 * @var array|mixed
	 */
	private $original_language_description;

	/**
	 * @var ImageSize[]
	 */
	private $sizes = [];

	private $original;

	private function __construct() {
	}

	/**
	 * @param array $data The data returned by Orange Logic API.
	 * @param array $images_in_wordpress Array with all images that are already in WP.
	 *
	 * @return static The created instance.
	 */
	public static function from_api_response( array $data, array $images_in_wordpress ): self {
		$image               = new self();
		$image->archive_id   = $data['SystemIdentifier'];
		$image->title        = $data['Title'];
		$image->caption      = $data['Caption'];
		$image->credit       = trim( $data['copyright'] ?? '' );
		$image->restrictions = $data['restrictions'] ?? [];
		$image->sizes        = ImageSize::all_from_api_response( $data );

		$image->original_language_title       = $data['original-language-title'] ?? null;
		$image->original_language_description = $data['original-language-description'] ?? null;

		foreach ( $image->sizes as $size ) {
			if ( $size->is_original() ) {
				$image->original = $size;
				break;
			}
		}

		$image->wordpress_id = $images_in_wordpress[ $image->archive_id ] ?? null;

		return $image;
	}

	public static function all_from_api_response( array $response, array $images_in_wordpress ): array {

		return array_map( static function ( $item ) use ( $images_in_wordpress ) {
			return Image::from_api_response( $item, $images_in_wordpress );
		},
			$response['APIResponse']['Items'] ?? [] );
	}

	public function jsonSerialize(): array {
		return [
			'id'           => $this->archive_id,
			'title'        => $this->title,
			'caption'      => $this->caption,
			'credit'       => $this->credit,
			'restrictions' => $this->restrictions,
			'sizes'        => $this->sizes,
			'wordpress_id' => $this->wordpress_id,

			'original_language_title'       => $this->original_language_title,
			'original_language_description' => $this->original_language_description,
		];
	}

	/**
	 * @return mixed
	 */
	public function get_title() {
		return $this->title;
	}

	public function put_in_wordpress( bool $use_original_language ): void {
		if ( null !== $this->wordpress_id ) {
			return;
		}
		$url = $this->original->getUrl();

		$filename = basename( $url );
		$filename = preg_replace( '/\?.*$/', '', $filename );

		$context = stream_context_create( [
			'ssl' => [
//					'verify_peer'      => false,
//					'verify_peer_name' => false,
			],
		] );

		// Upload file into WP upload dir.
		$upload_file = wp_upload_bits( $filename, null, file_get_contents( $url, false, $context ) );

		if ( ! empty( $upload_file['error'] ) ) {
			throw new \RuntimeException( 'File upload failed: ' . $upload_file['error'] );
		}

		$wp_filetype = wp_check_filetype( $filename, null );

		$title       = $use_original_language ? $this->original_language_title : $this->title;
		$description = $use_original_language ? $this->original_language_description : $this->caption;

		// Prepare an array of post data for the attachment.
		$attachment = [
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', $title ),
			'post_content'   => $description,
			'post_excerpt'   => $description,
			'post_status'    => 'inherit',
		];

		// Check title has full stop at the end, if not then add it.
		$alt_text = rtrim( $title, '.' ) . '.';

		$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0, true );

		if ( is_wp_error( $attachment_id ) ) {
			throw new \RuntimeException( __( 'Error while inserting attachment...!', 'planet4-medialibrary' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Generate the metadata for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );

		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// Add credit to alt field.
		if ( '' !== $this->credit ) {
			$alt_text .= ' ' . $this->credit;
		}
		// Set the image Alt-Text & image Credit.
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		update_post_meta( $attachment_id, '_credit_text', $this->credit );

		// Set media restriction details.
		update_post_meta( $attachment_id, '_media_restriction', $this->restrictions );

		update_post_meta( $attachment_id, self::ARCHIVE_ID_META_KEY, $this->archive_id );

		$this->wordpress_id = $attachment_id;
	}
}
