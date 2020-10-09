export const GalleryGrid = ({ images }) => (
  <div className="container">
    <div className="grid-row">
      {images.map(image => (
        <div key={image.image_src} className="grid-item">
          <img
            src={image.image_src}
            style={{ objectPosition: image.focus_image }}
            alt={image.alt_text}
          />
        </div>
      ))}
    </div>
  </div>
);
