import { useState, useLayoutEffect } from '@wordpress/element';

const { __ } = wp.i18n;

export const ContentCovers = ({ covers, covers_view, row, loadMoreCovers }) => {
  const [rowAmount, setRowAmount] = useState(4);

  const updateRowAmount = () => setRowAmount(window.innerWidth >= 576 && window.innerWidth < 992 ? 3 : 4);

  // The amount of covers per row depends on the window width
  useLayoutEffect(() => {
    window.addEventListener('resize', updateRowAmount);
    updateRowAmount();
    return () => window.removeEventListener('resize', updateRowAmount);
  }, []);

  const showLoadMore = (covers.length > rowAmount && covers_view === '1') ||
    (covers.length > (rowAmount * 2) && covers_view === '2');

  return (
    <div className='container'>
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
          const hideCover = covers_view !== '3' && index >= row * rowAmount;
          return (
            <div key={post_title} className={`col-md-4 col-lg-3 post-column ${hideCover ? 'hidden' : ''}`}>
              <div className='content-covers-block-wrap clearfix'>
                <div className='content-covers-block-info'>
                  <div className='content-covers-block-symbol'>
                    {thumbnail &&
                      <a
                        href={link}
                        data-ga-category='Content Covers'
                        data-ga-action='Image'
                        data-ga-label='n/a'
                        aria-label={__('Cover image, link to ' + post_title, 'planet4-blocks')}
                      >
                        <img loading='lazy' src={thumbnail} alt={alt_text} srcSet={srcset} />
                      </a>
                    }
                  </div>
                  <div className='content-covers-block-information'>
                    {post_title &&
                      <h5>
                        <a
                          href={link}
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
      {showLoadMore && (row * rowAmount) < covers.length &&
        <div className='row load-more-posts-button-div'>
          <div className='col-md-12 col-lg-5 col-xl-5'>
            <button onClick={loadMoreCovers} className='btn btn-block btn-secondary btn-load-more-posts-click'>
              {__('Load more', 'planet4-blocks')}
            </button>
          </div>
        </div>
      }
    </div>
  );
}
