const { __ } = wp.i18n;

export const CampaignCovers = ({ covers, covers_view }) => {
  const showLoadMore = (covers.length > 3 && covers_view === '1') ||
    (covers.length > 6 && covers_view === '2');

  return (
    <div className='container'>
      <div className='thumbnail-largeview-container limit-visibility'>
        {covers.map(cover => {
          const { href, image, alt_text, name } = cover;
          return (
            <div key={name} className='campaign-card-column'>
              <a
                href={href}
                data-ga-category='Campaign Covers'
                data-ga-action='Image'
                data-ga-label='n/a'
                aria-label={__('Check our campaign about ' + name, 'planet4-blocks')}
              >
                <div className='thumbnail-large'>
                  {image && image[0] &&
                    <img src={image[0]} alt={alt_text} />
                  }
                  <span className='yellow-cta'><span aria-label='hashtag'>#</span>{name}</span>
                </div>
              </a>
            </div>
          );
        })}
      </div>
      {showLoadMore &&
        <div className='row'>
          <div className='col-md-12 col-lg-5 col-xl-5 mt-3 load-more-campaigns-button-div'>
            <button className='btn btn-block btn-secondary btn-load-more-campaigns-click'>
              {__('Load more', 'planet4-blocks')}
            </button>
          </div>
        </div>
      }
    </div>
  );
}
