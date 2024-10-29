import {IMAGE_SIZES} from './imageSizes';
import {GalleryLightbox} from './GalleryLightbox';

const ordinals = ['first', 'second', 'third'];

export const GalleryThreeColumns = ({images, postType, expand}) => (
  <div className="three-column-images row">
    {images.slice(0, 3).map((image, index) => (
      <div className="col" key={image.image_src}>
        <div className={`${ordinals[index]}-column split-image`}>
          {
            expand ?
              <GalleryLightbox
                key={index}
                image={image}
                index={index}
                imgSizes={IMAGE_SIZES}
              /> :
              <img
                loading="lazy"
                src={image.image_src}
                srcSet={image.image_srcset}
                sizes={IMAGE_SIZES[`threeColumns${index}`]}
                style={{objectPosition: image.focus_image}}
                alt={image.alt_text}
                title={image.alt_text}
                className={`img_${postType}`}
                data-index={index}
                role="presentation"
              />
          }
        </div>
      </div>
    ))}
  </div>
);
