export const SlideBackground = ({slide, index}) => {
  const {image_url, image_srcset, focal_points, image_alt} = slide;
  return (
    <div className="background-holder">
      <img
        className={index === 0 ? 'carousel-header-image--first' : undefined}
        src={image_url}
        style={{objectPosition: `${(focal_points?.x || .5) * 100}% ${(focal_points?.y || .5) * 100}%`}}
        srcSet={image_srcset}
        alt={image_alt}
        {...(index === 0 ? {fetchpriority: 'high'} : {})}
      />
    </div>
  );
};
