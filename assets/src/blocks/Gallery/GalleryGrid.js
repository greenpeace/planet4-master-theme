export const GalleryGrid = ({ images, onImageClick }) => (
  <div className="container">
    <div className="grid-row">
      {images.map((image, index) => (
        <div key={image.image_src} className="grid-item">
          <img
            src={image.image_src}
            srcSet={image.image_srcset}
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
