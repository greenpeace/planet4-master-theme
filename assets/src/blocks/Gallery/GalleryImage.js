export const GalleryImage = ({image, index, imgSizes, postType}) => {
  return (
    <img
      loading="lazy"
      src={image.image_src}
      srcSet={image.image_srcset}
      sizes={imgSizes.grid}
      style={{objectPosition: image.focus_image}}
      alt={image.alt_text}
      title={image.alt_text}
      data-index={index}
      role="presentation"
      className={postType ?? `img_${postType}`}
    />
  );
};
