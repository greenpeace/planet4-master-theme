const { __ } = wp.i18n;

export const TakeActionBoxoutFrontend = ({
  title,
  excerpt,
  link,
  linkText,
  newTab,
  imageUrl,
  imageAlt,
  className,
}) => (
  <section className={ `boxout ${ className || '' }` }>
    <a
      data-ga-category="Take Action Boxout"
      data-ga-action="Image"
      data-ga-label="n/a"
      className="cover-card-overlay"
      href={link}
      {...newTab && link && { target: "_blank" }}
    />
    <img src={imageUrl} alt={imageAlt} />
    <div className="boxout-content">
      {title &&
        <a
          className="boxout-heading"
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
        <p className="boxout-excerpt">{excerpt}</p>
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
