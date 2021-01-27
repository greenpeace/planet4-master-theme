const { __ } = wp.i18n;

export const CampaignCovers = ({ covers, covers_view, row, loadMoreCovers}) => {
  const rowAmount = 3;
  const showLoadMore = (covers.length > rowAmount && covers_view === '1') ||
    (covers.length > (rowAmount * 2) && covers_view === '2');

  return (
    <div className='container'>
      <div className='thumbnail-largeview-container'>
        {covers.map((cover, index) => {
          const { href, image, alt_text, name } = cover;
          const hideCover = covers_view !== '3' && index >= row * rowAmount;
          return (
            <div key={name} className={`campaign-card-column ${hideCover ? 'hidden' : ''}`}>
              <a
                href={href}
                data-ga-category='Campaign Covers'
                data-ga-action='Image'
                data-ga-label='n/a'
                aria-label={__('Check our campaign about ' + name, 'planet4-blocks')}
              >
                <div className='thumbnail-large'>
                  {image && image[0] &&
                    <img loading='lazy' src={image[0]} alt={alt_text} />
                  }
                  <span className='yellow-cta'><span aria-label='hashtag'>#</span>{name}</span>
                </div>
              </a>
            </div>
          );
        })}
      </div>
      {showLoadMore && (row * rowAmount) < covers.length &&
        <div className='row'>
          <div className='col-md-12 col-lg-5 col-xl-5 mt-3 load-more-campaigns-button-div'>
            <button onClick={loadMoreCovers} className='btn btn-block btn-secondary btn-load-more-campaigns-click'>
              {__('Load more', 'planet4-blocks')}
            </button>
          </div>
        </div>
      }
    </div>
  );
}
