import {IMAGE_SIZES} from './imageSizes';
import {GalleryLightbox} from './GalleryLightbox';
import {GalleryImage} from './GalleryImage';

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
              <GalleryImage
                image={image}
                index={index}
                imgSizes={IMAGE_SIZES[`threeColumns${index}`]}
                className={`img_${postType}`}
              />
          }
        </div>
      </div>
    ))}
  </div>
);
