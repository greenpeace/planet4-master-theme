import {GalleryLightbox} from './GalleryLightbox';
import {IMAGE_SIZES} from './imageSizes';

export const GalleryGrid = ({images, expand}) => (
  <div className="grid-row">
    {images.map((image, index) => {
      return (
        <div key={image.image_src} className="grid-item">
          {
            expand ?
              <GalleryLightbox
                key={index}
                image={image}
                index={index}
                imgSizes={IMAGE_SIZES}
              /> :
              <div key={image.image_src} className="grid-item">
                <img
                  loading="lazy"
                  src={image.image_src}
                  srcSet={image.image_srcset}
                  sizes={IMAGE_SIZES.grid}
                  style={{objectPosition: image.focus_image}}
                  alt={image.alt_text}
                  title={image.alt_text}
                  data-index={index}
                  role="presentation"
                />
              </div>
          }

        </div>
      );
    })}
  </div>
);
