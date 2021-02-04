import { IMAGE_SIZES } from './imageSizes';

export const GalleryGrid = ({ images, onImageClick }) => (
  <div className="container">
    <div className="grid-row">
      {images.map((image, index) => (
        <div key={image.image_src} className="grid-item">
          <img
            loading='lazy'
            src={image.image_src}
            srcSet={image.image_srcset}
            sizes={IMAGE_SIZES.grid}
            style={{ objectPosition: image.focus_image }}
            alt={image.alt_text}
            onClick={() => {
              onImageClick(index);
            }}
          />
        </div>
      ))}
    </div>
  </div>
);
