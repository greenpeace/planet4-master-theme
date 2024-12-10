export const TopicLinkFrontend = ({
  imageUrl,
  imageAlt,
  selectedCategory,
  categoryLink,
  focal_points,
}) => {
  return (
    <section className="topic-link-block">
      <a href={categoryLink} target="_self">
        <div className="background-image">
          {imageUrl &&
              <img
                src={imageUrl}
                alt={imageAlt}
                style={{objectPosition: focal_points}}
              />}
        </div>
        <div className="topic-link-content">
          <p>
              Learn more about {selectedCategory}
          </p>
          <div className="chevron-icon"></div>
        </div>
      </a>
    </section>
  );
};
