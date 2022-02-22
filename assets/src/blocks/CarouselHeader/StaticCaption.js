export const StaticCaption = ({ slide }) => (
  <div className='carousel-caption'>
    <div className='caption-overlay'></div>
    <div className='container main-header'>
      <div className='carousel-captions-wrapper'>
        <h2>
          {slide.header}
        </h2>
        <p dangerouslySetInnerHTML={{ __html: slide.description }} />
      </div>
      {slide.link_url &&
        <div className='col-xs-12 col-sm-8 col-md-4 action-button'>
          <a
            href={slide.link_url}
            target={slide.link_url_new_tab ? '_blank' : '_self'}
            className='btn btn-primary btn-block'
            data-ga-category='Carousel Header'
            data-ga-action='Call to Action'
            rel='noopener noreferrer'
            data-ga-label={slide.index}
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
