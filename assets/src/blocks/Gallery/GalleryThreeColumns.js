import { IMAGE_SIZES } from './imageSizes';

const ordinals = ['first', 'second', 'third'];

export const GalleryThreeColumns = ({ images, postType, onImageClick }) => (
  <div className="three-column-images row">
    {images.slice(0, 3).map((image, index) => (
      <div className="col" key={image.image_src}>
        <div className={`${ordinals[index]}-column split-image`}>
          {image.image_src &&
            <img
              loading='lazy'
              src={image.image_src}
              srcSet={image.image_srcset}
              sizes={IMAGE_SIZES[`threeColumns${index}`]}
              style={{ objectPosition: image.focus_image }}
              alt={image.alt_text}
              className={`img_${postType}`}
              onClick={() => {
                onImageClick(index);
              }}
            />
          }
        </div>
      </div>
    ))}
  </div>
);
