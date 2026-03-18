const {useMemo, forwardRef} = wp.element;

const htmlDecode = input => {
  const doc = new DOMParser().parseFromString(input, 'text/html');
  return doc.documentElement.textContent;
};

export const StaticCaption = forwardRef(({slide, focusable}, ref) => useMemo(() => (
  <div className="carousel-caption">
    <div className="caption-overlay"></div>
    <div className="container main-header">
      <div className="carousel-captions-wrapper">
        {/* eslint-disable-next-line jsx-a11y/no-noninteractive-tabindex */}
        <h2 ref={ref} tabIndex={focusable ? 0 : -1}>
          {htmlDecode(slide.header)}
        </h2>
        <p dangerouslySetInnerHTML={{__html: slide.description}} />
      </div>
      {slide.link_url && (
        <div className="col-xs-12 col-sm-8 col-md-4 action-button">
          <a
            href={slide.link_url}
            className="btn btn-primary"
            data-ga-category="Carousel Header"
            data-ga-action="Call to Action"
            data-ga-label={slide.index}
            {...(slide.link_url_new_tab && {rel: 'noreferrer noopener', target: '_blank'})}
            tabIndex={focusable ? 0 : -1}
          >
            <span>{slide.link_text}</span>
          </a>
        </div>
      )}
    </div>
  </div>
), [slide, focusable, ref]));
