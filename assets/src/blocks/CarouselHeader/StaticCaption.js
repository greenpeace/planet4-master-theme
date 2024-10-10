const htmlDecode = input => {
  const doc = new DOMParser().parseFromString(input, 'text/html');
  return doc.documentElement.textContent;
};

export const StaticCaption = ({slide}) => (
  <div className="carousel-caption">
    <div className="caption-overlay"></div>
    <div className="container main-header">
      <div className="carousel-captions-wrapper">
        <h2>
          {htmlDecode(slide.header)}
        </h2>
        <p dangerouslySetInnerHTML={{__html: slide.description}} />
      </div>
      {slide.link_url &&
        <div className="col-xs-12 col-sm-8 col-md-4 action-button">
          <a
            href={slide.link_url}
            className="btn btn-primary"
            data-ga-category="Carousel Header"
            data-ga-action="Call to Action"
            data-ga-label={slide.index}
            {...slide.link_url_new_tab && {rel: 'noreferrer noopener', target: '_blank'}}
          >
            <span>
              {slide.link_text}
            </span>
          </a>
        </div>
      }
    </div>
  </div>
);
