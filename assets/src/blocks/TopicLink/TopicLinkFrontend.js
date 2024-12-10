export const TopicLinkFrontend = ({
  imageUrl,
  imageAlt,
  selectedCategory,
  focal_points,
}) => {
  const setObjectPosition = () => {
    if (focal_points === undefined) {
      return '50% 50%';
    }
    const floatX = parseFloat(focal_points.x).toFixed(2);
    const floatY = parseFloat(focal_points.y).toFixed(2);
    return `${floatX * 100}% ${floatY * 100}%`;
  };

  return (
    <section className="topic-link-block">
      <div className="background-image">
        {imageUrl &&
            <img
              src={imageUrl}
              alt={imageAlt}
              style={{objectPosition: setObjectPosition()}}
            />}
      </div>
      <div className="topic-link-content">
        <p>
            Learn more about {selectedCategory}
        </p>
        <div className="chevron-icon"></div>
      </div>
    </section>
  );
};
