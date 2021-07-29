import { IMAGE_SIZES } from './imageSizes';
const { __ } = wp.i18n;

export const CampaignCovers = ({ covers, initialRowsLimit, row, loadMoreCovers }) => {
  const amountPerRow = 3;
  const showLoadMore = !!initialRowsLimit && covers.length > amountPerRow * row;

  return (
    <div className='container'>
      <div className='thumbnail-largeview-container'>
        {covers.map((cover, index) => {
          const { href, image, alt_text, name, src_set } = cover;
          const hideCover = !!initialRowsLimit && index >= row * amountPerRow;

          if (hideCover) {
            return null;
          }

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
                    <img
                      loading='lazy'
                      sizes={IMAGE_SIZES.campaign}
                      srcSet={src_set}
                      src={image[0]}
                      alt={alt_text}
                      title={alt_text}
                    />
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
            <button onClick={loadMoreCovers} className='btn btn-block btn-secondary btn-load-more-campaigns-click'>
              {__('Load more', 'planet4-blocks')}
            </button>
          </div>
        </div>
      }
    </div>
  );
}
