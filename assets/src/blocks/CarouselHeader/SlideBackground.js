export const SlideBackground = ({ slide }) => {
  const { image_url, image_srcset, focal_points, image_alt } = slide;
  return (
    <div className='background-holder'>
      <img
        src={image_url}
        style={{ objectPosition: `${focal_points?.x * 100}% ${focal_points?.y * 100}%` }}
        srcSet={image_srcset}
        alt={image_alt}
      />
    </div>
  );
}
