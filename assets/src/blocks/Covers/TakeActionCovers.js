import { useState, useLayoutEffect } from '@wordpress/element';

const { __ } = wp.i18n;

export const TakeActionCovers = ({ covers_view, covers }) => {
  const [rowAmount, setRowAmount] = useState(3);

  const updateRowAmount = () => {
    setRowAmount(window.innerWidth < 992 ? 2 : 3);
  }

  // The amount of covers per row depends on the window width
  useLayoutEffect(() => {
    window.addEventListener('resize', updateRowAmount);
    updateRowAmount();
    return () => window.removeEventListener('resize', updateRowAmount);
  }, []);

  const showLoadMore = (covers.length > rowAmount && covers_view === '1') ||
    (covers.length > (rowAmount * 2) && covers_view === '2') ||
    (covers_view === '3' && window.innerWidth < 992 && covers.length > 4);

  return (
    <div className='container'>
      <div className='row limit-visibility'>
        {covers.map(cover => {
          const {
            button_link,
            title,
            tags,
            image,
            excerpt,
            button_text
          } = cover;
          return (
            <div key={title} className='col-lg-4 col-md-6 cover-card-column'>
              <div className='cover-card card-one' style={{ backgroundImage: `url(${image})` }}>
                <a
                  className='cover-card-overlay'
                  data-ga-category='Take Action Covers'
                  data-ga-action='Image'
                  data-ga-label='n/a'
                  href={button_link}
                  aria-label={__('Take action cover, link to ' + title, 'planet4-blocks')}
                />
                <div className='cover-card-content'>
                  {tags && tags.map(tag => (
                    <a
                      key={tag.name}
                      className='cover-card-tag'
                      data-ga-category='Take Action Covers'
                      data-ga-action='Navigation Tag'
                      data-ga-label='n/a'
                      href={tag.href}
                    >
                      <span aria-label='hashtag'>#</span>
                      {tag.name}
                    </a>
                  ))}
                  <a
                    className='cover-card-heading'
                    data-ga-category='Take Action Covers'
                    data-ga-action='Title'
                    data-ga-label='n/a'
                    href={button_link}
                  >
                    {title}
                  </a>
                  <p>{excerpt}</p>
                </div>
                <a
                  className='btn btn-action btn-block cover-card-btn'
                  data-ga-category='Take Action Covers'
                  data-ga-action='Call to Action'
                  data-ga-label='n/a'
                  href={button_link}
                >
                  {button_text}
                </a>
              </div>
            </div>
          )
        })}
      </div>
      {showLoadMore &&
        <div className='row'>
          <div className='col-lg-5 col-md-12 load-more-covers-button-div'>
            <button className='btn btn-block btn-secondary btn-load-more-covers-click'>
              {__( 'Load more', 'planet4-blocks' )}
            </button>
          </div>
        </div>
      }
    </div>

  );
}
