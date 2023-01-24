<?php
// phpcs:ignoreFile PSR1.Files.SideEffects.FoundWithSymbols

namespace P4\MasterTheme;

use Exception;
use Imagick;
use WP_Error;
use WP_Image_Editor_Imagick;

require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';

/**
 * Class ImageCompression
 */
class ImageCompression extends WP_Image_Editor_Imagick
{
    /**
     * Image compression filter.
     *
     */
    protected string $filter = 'FILTER_LANCZOS';

    /**
     * Override default imagick compression and use progressive compression instead.
     *
     * @param int    $dst_w The destination width.
     * @param int    $dst_h The destination height.
     * @param string $filter_name Optional. The Imagick filter to use when resizing. Default 'FILTER_TRIANGLE'.
     * @param bool   $strip_meta Optional. Strip all profiles, excluding color profiles, from the image. Default true.
     *
     * @return bool|WP_Error
     * @since 1.9
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint -- compatible with parent class
    protected function thumbnail_image($dst_w, $dst_h, $filter_name = 'FILTER_TRIANGLE', $strip_meta = true)
    {
        if ($this->filter) {
            $filter_name = $this->filter;
        }
        parent::thumbnail_image($dst_w, $dst_h, $filter_name, $strip_meta);

        // The order of methods applied is: Resize -> Sharpen -> Compress.
        try {
            // Sharpen image after it has been resized.
            if ('image/jpeg' === $this->mime_type) {
                if (is_callable([ $this->image, 'unsharpMaskImage' ])) {
                    $this->image->unsharpMaskImage(1, 0.45, 3, 0);
                }
            }
        } catch (Exception $e) {
            return new WP_Error('image_sharpening_error', $e->getMessage());
        }

        try {
            // Compress image after it has been sharpened.
            if (is_callable([ $this->image, 'setInterlaceScheme' ]) && defined('Imagick::INTERLACE_PLANE')) {
                $this->image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
            }
        } catch (Exception $e) {
            return new WP_Error('image_resize_error', $e->getMessage());
        }
    }
}
