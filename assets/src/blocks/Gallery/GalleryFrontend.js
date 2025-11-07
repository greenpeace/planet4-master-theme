import {GalleryCarousel} from './GalleryCarousel';
import {GalleryThreeColumns} from './GalleryThreeColumns';
import {GalleryGrid} from './GalleryGrid';
import {getGalleryLayout, GALLERY_BLOCK_CLASSES} from './getGalleryLayout';
import {usePhotoSwipeLightbox} from '../components/Lightbox/photoSwipeLightbox';
import {useCallback, useEffect, useMemo, useState} from '@wordpress/element';

/**
 * Renders a gallery block on the frontend using the appropriate layout (slider, grid, or three-columns)
 * and integrates PhotoSwipe lightbox functionality for viewing larger images.
 *
 * @function GalleryFrontend
 * @param {Object} props                 - React component props.
 * @param {Object} [props.attributes={}] - Block attributes from Gutenberg.
 * @return {JSX.Element} The rendered gallery block with lightbox-enabled images.
 */
export const GalleryFrontend = ({attributes = {}}) => {
  const [images, setImages] = useState([]);
  const className = attributes.className ?? '';
  const layout = getGalleryLayout(className, attributes.gallery_block_style ?? '');
  const postType = document.body.getAttribute('data-post-type');

  /**
   * Normalizes and sets the images array when block attributes are updated.
   */
  useEffect(() => {
    if (attributes.image_data?.length) {
      setImages(attributes.images?.length ? attributes.images : attributes.image_data);
    }
  }, [attributes]);

  /**
   * Prepares the image data for PhotoSwipe by extracting width, height, src, and caption.
   *
   * @type {Array<{srcset: string, src: string, width: number|null, height: number|null, alt: string, caption: string}>}
   */
  const items = useMemo(() => {
    return images.map(img => {
      const sizeMatches = img.image_srcset?.match(/-(\d+)x(\d+)\.\w+/g);
      let width = null;
      let height = null;

      if (sizeMatches?.length) {
        const sizes = sizeMatches.map(s => {
          const [, w, h] = s.match(/-(\d+)x(\d+)/);
          return {w: parseInt(w, 10), h: parseInt(h, 10)};
        });
        const largest = sizes.reduce((a, b) => (a.w > b.w ? a : b));
        width = largest.w;
        height = largest.h;
      }

      return {
        srcset: img.image_srcset,
        src: img.image_src,
        width,
        height,
        alt: img.alt_text,
        caption: img.caption,
      };
    });
  }, [images]);

  /**
   * Initializes the PhotoSwipe lightbox hook.
   *
   * @type {Function} openLightbox - Function to open the lightbox at a specific image index.
   */
  const openLightbox = usePhotoSwipeLightbox({
    options: items,
    onClose: () => {},
  });

  /**
   * Shared click handler for opening the lightbox on image click.
   *
   * @param {MouseEvent} e - Click event.
   */
  const handleImageClick = useCallback(
    e => {
      const index = parseInt(e.currentTarget.dataset.index, 10);
      openLightbox(index);
    },
    [openLightbox]
  );

  /**
   * Determines whether clicking images should open the lightbox.
   * If the gallery has the `force-no-lightbox` class, the lightbox is disabled.
   *
   * @type {Function|null}
   */
  const expandOnClick = className.includes('force-no-lightbox') ? null : handleImageClick;

  return (
    <section className={`block ${GALLERY_BLOCK_CLASSES[layout]} ${className}`}>
      {attributes.gallery_block_title && (
        <header>
          <h2
            className="page-section-header"
            dangerouslySetInnerHTML={{__html: attributes.gallery_block_title}}
          />
        </header>
      )}
      {attributes.gallery_block_description && (
        <p
          className="page-section-description"
          dangerouslySetInnerHTML={{__html: attributes.gallery_block_description}}
        />
      )}

      {images.length ? (
        <>
          {layout === 'slider' && (
            <GalleryCarousel images={images} onImageClick={expandOnClick} />
          )}
          {layout === 'three-columns' && (
            <GalleryThreeColumns images={images} postType={postType} onImageClick={expandOnClick} />
          )}
          {layout === 'grid' && (
            <GalleryGrid images={images} onImageClick={expandOnClick} />
          )}
        </>
      ) : null}
    </section>
  );
};
