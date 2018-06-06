<?php

if ( ! class_exists( 'P4_Image_Compression' ) ) {

	/**
	 * Class P4_Image_Compression
	 */
	class P4_Image_Compression extends WP_Image_Editor_Imagick {

		/** @var string $filter */
		protected $filter = 'FILTER_LANCZOS';

		/**
		 * Override default imagick compression and use progressive compression instead.
		 *
		 * @since 1.9
		 *
		 * @param int    $dst_w       The destination width.
		 * @param int    $dst_h       The destination height.
		 * @param string $filter_name Optional. The Imagick filter to use when resizing. Default 'FILTER_TRIANGLE'.
		 * @param bool   $strip_meta  Optional. Strip all profiles, excluding color profiles, from the image. Default true.
		 * @return bool|WP_Error
		 */
		protected function thumbnail_image( $dst_w, $dst_h, $filter_name = 'FILTER_TRIANGLE', $strip_meta = true ) {
			if ( $this->filter ) {
				$filter_name = $this->filter;
			}
			parent::thumbnail_image( $dst_w, $dst_h, $filter_name, $strip_meta );
			try {
				if ( is_callable( [ $this->image, 'setInterlaceScheme' ] ) && defined( 'Imagick::INTERLACE_PLANE' ) ) {
					$this->image->setInterlaceScheme( Imagick::INTERLACE_PLANE );
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'image_resize_error', $e->getMessage() );
			}
		}
	}
}
