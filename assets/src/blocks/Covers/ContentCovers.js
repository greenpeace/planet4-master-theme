import { useState, useEffect } from '@wordpress/element';
import { IMAGE_SIZES } from './imageSizes';

const { __ } = wp.i18n;

const isMediumWindow = () => window.innerWidth > 576 && window.innerWidth < 992;

export const ContentCovers = ({ covers, initialRowsLimit, row, loadMoreCovers, inEditor = false }) => {
  const [amountPerRow, setAmountPerRow] = useState(isMediumWindow() ? 3 : 4);

  const updateRowAmount = () => setAmountPerRow(isMediumWindow() ? 3 : 4);

  // The amount of covers per row depends on the window width
  useEffect(() => {
    window.addEventListener('resize', updateRowAmount);
    return () => window.removeEventListener('resize', updateRowAmount);
  }, []);

  const showLoadMore = !!initialRowsLimit && covers.length > amountPerRow * row;

  return (
    <>
      <div className='row publications-slider'>
        {covers.map((cover, index) => {
          const {
            thumbnail,
            link,
            post_title,
            srcset,
            alt_text,
            date_formatted,
            post_excerpt,
          } = cover;

          // On mobile because of the carousel layout we want to show all covers,
          // no matter the initial rows limit
          const hideCover = window.innerWidth >= 576 && !!initialRowsLimit && index >= row * amountPerRow;

          if (hideCover) {
            return null;
          }

          const contentLink = inEditor ? null : link;

          return (
            <div key={post_title} className='col-md-4 col-lg-3 post-column'>
              <div className='content-covers-block-wrap clearfix'>
                <div className='content-covers-block-info'>
                  <div className='content-covers-block-symbol'>
                    {thumbnail &&
                      <a
                        href={contentLink}
                        data-ga-category='Content Covers'
                        data-ga-action='Image'
                        data-ga-label='n/a'
                        aria-label={__('Cover image, link to ' + post_title, 'planet4-blocks')}
                      >
                        <img
                          loading='lazy'
                          src={thumbnail}
                          alt={alt_text}
                          title={alt_text}
                          srcSet={srcset}
                          sizes={IMAGE_SIZES.content}
                        />
                      </a>
                    }
                  </div>
                  <div className='content-covers-block-information'>
                    {post_title &&
                      <h5>
                        <a
                          href={contentLink}
                          data-ga-category='Content Covers'
                          data-ga-action='Title'
                          data-ga-label='n/a'
                        >
                          {post_title}
                        </a>
                      </h5>
                    }
                    {date_formatted &&
                      <p className='publication-date'>{date_formatted}</p>
                    }
                    {post_excerpt &&
                      <p className='d-none d-md-block' dangerouslySetInnerHTML={{ __html: post_excerpt }} />
                    }
                  </div>
                </div>
              </div>
            </div>
          );
        })}
      </div>
      {showLoadMore &&
        <div className='row load-more-posts-button-div'>
          <div className='col-md-12 col-lg-5 col-xl-5'>
            <button onClick={loadMoreCovers} className='btn btn-block btn-secondary'>
              {__('Load more', 'planet4-blocks')}
            </button>
          </div>
        </div>
      }
    </>
  );
};
