import { useState, useEffect } from '@wordpress/element';
import { IMAGE_SIZES } from './imageSizes';

const { __ } = wp.i18n;

const isSmallWindow = () => window.innerWidth < 992;

export const TakeActionCovers = ({ initialRowsLimit, covers, row, loadMoreCovers, inEditor = false }) => {
  const [amountPerRow, setAmountPerRow] = useState(isSmallWindow() ? 2 : 3);

  const updateRowAmount = () => setAmountPerRow(isSmallWindow() ? 2 : 3);

  // The amount of covers per row depends on the window width
  useEffect(() => {
    window.addEventListener('resize', updateRowAmount);
    return () => window.removeEventListener('resize', updateRowAmount);
  }, []);

  const showLoadMore = !!initialRowsLimit && covers.length > amountPerRow * row;
  return (
    <div className='container'>
      <div className='row'>
        {covers.map((cover, index) => {
          const {
            button_link,
            title,
            tags,
            image,
            srcset,
            alt_text,
            excerpt,
            button_text,
          } = cover;
          const hideCover = !!initialRowsLimit && index >= row * amountPerRow;

          if (hideCover) {
            return null;
          }

          const buttonLink = inEditor ? null : button_link;

          return (
            <div key={title} className='col-lg-4 col-md-6'>
              <div className='cover-card-new'>
                <a
                  className='cover-card-overlay'
                  data-ga-category='Take Action Covers'
                  data-ga-action='Card'
                  data-ga-label='n/a'
                  href={buttonLink}
                  aria-label={__('Take action cover, link to ' + title, 'planet4-blocks')}
                />
                <a
                  data-ga-category='Take Action Covers'
                  data-ga-action='Image'
                  data-ga-label='n/a'
                  href={buttonLink}
                  aria-label={__('Take action cover, link to ' + title, 'planet4-blocks')}
                >
                  <img
                    alt={alt_text}
                    src={image}
                    srcSet={srcset}
                    sizes={IMAGE_SIZES.takeAction}
                  />
                </a>
                <div className='cover-card-content'>
                  {/* Regardless of how many tags there are, we only show the first one */}
                  {tags && tags.length > 0 && <span className='cover-card-tag'>{tags[0].name}</span>}
                  <a
                    className='cover-card-heading'
                    data-ga-category='Take Action Covers'
                    data-ga-action='Title'
                    data-ga-label='n/a'
                    href={buttonLink}
                  >
                    {title}
                  </a>
                  <p className="cover-card-excerpt">{excerpt}</p>
                </div>
                <a
                  className='btn cover-card-btn btn-primary'
                  data-ga-category='Take Action Covers'
                  data-ga-action='Call to Action'
                  data-ga-label='n/a'
                  href={buttonLink}
                >
                  {button_text}
                </a>
              </div>
            </div>
          );
        })}
      </div>
      {showLoadMore &&
        <div className='row'>
          <div className='load-more-covers-button-div-new'>
            <button onClick={loadMoreCovers} className='btn btn-block btn-secondary'>
              {__( 'Load more', 'planet4-blocks' )}
            </button>
          </div>
        </div>
      }
    </div>

  );
};
