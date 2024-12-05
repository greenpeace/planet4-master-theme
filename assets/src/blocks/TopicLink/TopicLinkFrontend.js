export const TopicLinkFrontend = ({
  title,
  excerpt,
  link,
  linkText,
  newTab,
  imageUrl,
  imageAlt,
  className,
  stickyOnMobile,
}) => (
  <section
    className={`boxout ${className || ''}`}
    {...stickyOnMobile && {id: 'action-card'}}
  >
    {/* eslint-disable-next-line jsx-a11y/anchor-has-content */}
    <a
      data-ga-category="Take Action Boxout"
      data-ga-action="Image"
      data-ga-label="n/a"
      className="cover-card-overlay"
      href={link}
      {...newTab && {rel: 'noreferrer', target: '_blank'}}
    />
    {imageUrl ? <img src={imageUrl} alt={imageAlt} /> : <div className="boxout-placeholder" />}
    <div className="boxout-content">
      {title &&
        <a
          className="boxout-heading"
          data-ga-category="Take Action Boxout"
          data-ga-action="Title"
          data-ga-label="n/a"
          dangerouslySetInnerHTML={{__html: title}}
          href={link}
          {...newTab && {rel: 'noreferrer', target: '_blank'}}
        />
      }
      {excerpt &&
        <p className="boxout-excerpt" dangerouslySetInnerHTML={{__html: excerpt}} />
      }
      {link && linkText &&
        <a
          className="btn btn-primary"
          data-ga-category="Take Action Boxout"
          data-ga-action="Call to Action"
          data-ga-label="n/a"
          href={link}
          {...newTab && {rel: 'noreferrer', target: '_blank'}}
        >
          {linkText}
        </a>
      }
    </div>
  </section>
);
