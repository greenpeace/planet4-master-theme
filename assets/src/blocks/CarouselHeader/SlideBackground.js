export const SlideBackground = ({ slide }) => {
  const { image_url, image_sizes, image_srcset, focal_points } = slide;
  return (
    <div className='background-holder'>
      <img
        src={image_url}
        style={{ objectPosition: `${focal_points?.x * 100}% ${focal_points?.y * 100}%` }}
        srcSet={image_srcset}
        sizes={image_sizes || 'false'}
      />
    </div>
  );
}
