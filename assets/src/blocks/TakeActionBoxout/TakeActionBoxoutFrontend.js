const { __ } = wp.i18n;

export const TakeActionBoxoutFrontend = ({
  title,
  excerpt,
  link,
  linkText,
  newTab,
  tags,
  imageUrl,
  imageAlt,
  className,
}) => (
  <section
    className={`cover-card action-card ${className || ''}`}
    style={{
      backgroundImage: `linear-gradient(180deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0)), url(${imageUrl})`
    }}
  >
    <a
      data-ga-category="Take Action Boxout"
      data-ga-action="Image"
      data-ga-label="n/a"
      className="cover-card-overlay"
      href={link}
      {...newTab && link && { target: "_blank" }}
    />
    <img src={imageUrl} alt={imageAlt} />
    <div className="cover-card-more">{__('Want to do more?', 'planet4-blocks')}</div>
    <div className="cover-card-content">
      {tags && tags.map(tag => (
        <a
          key={tag.name}
          className="cover-card-tag"
          data-ga-category="Take Action Boxout"
          data-ga-action="Navigation Tag"
          data-ga-label="n/a"
          href={tag.link}
        >
          <span aria-label="hashtag">#</span>{tag.name}
        </a>
      ))}
      {title &&
        <a
          className="cover-card-heading"
          data-ga-category="Take Action Boxout"
          data-ga-action="Title"
          data-ga-label="n/a"
          href={link}
          {...newTab && link && { target: "_blank" }}
        >
          {title}
        </a>
      }
      {excerpt &&
        <p className="cover-card-excerpt">{excerpt}</p>
      }
    </div>
    {link && linkText &&
      <a
        className="btn btn-action btn-block cover-card-btn"
        data-ga-category="Take Action Boxout"
        data-ga-action="Call to Action"
        data-ga-label="n/a"
        href={link}
        {...newTab && link && { target: "_blank" }}
      >
        {linkText}
      </a>
    }
    {link && linkText &&
      <a className="not-now" data-ga-category="Take Action Boxout">
        ×
      </a>
    }
  </section>
);
