import {GalleryLightbox} from './GalleryLightbox';
import {GalleryImage} from './GalleryImage';
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
              <GalleryImage
                image={image}
                index={index}
                imgSizes={IMAGE_SIZES}
              />
          }
        </div>
      );
    })}
  </div>
);
