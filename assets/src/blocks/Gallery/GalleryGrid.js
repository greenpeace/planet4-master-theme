import {useMemo} from '@wordpress/element';
import {IMAGE_SIZES} from './imageSizes';

export const GalleryGrid = ({images, onImageClick}) => useMemo(
  () => (
    <div className="grid-row">
      {images.map((image, index) => (
        <div key={`${image.image_src}-${index}`} className="grid-item">
          <img
            loading="lazy"
            src={image.image_src}
            srcSet={image.image_srcset}
            sizes={IMAGE_SIZES.grid}
            style={{objectPosition: image.focus_image}}
            alt={image.alt_text}
            title={image.alt_text}
            data-index={index}
            onClick={onImageClick}
            role="presentation"
          />
        </div>
      ))}
    </div>
  ),
  [images, onImageClick]
);
