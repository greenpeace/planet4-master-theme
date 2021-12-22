import { CoversImagePlaceholder } from './CoversImagePlaceholder';
import { IMAGE_SIZES } from './imageSizes';

const { __ } = wp.i18n;

export const ContentCovers = ({
  covers,
  initialRowsLimit,
  row,
  inEditor = false,
  isCarouselLayout,
  amountOfCoversPerRow,
  isExample,
}) => (
  <div className='covers'>
    {covers.map((cover, index) => {
      const {
        thumbnail,
        link,
        post_title,
        srcset,
        alt_text,
        date_formatted,
        post_excerpt,
        id,
      } = cover;

      const hideCover = !isCarouselLayout && !!initialRowsLimit && index >= row * amountOfCoversPerRow;

      if (hideCover) {
        return null;
      }

      const contentLink = inEditor ? null : link;

      return (
        <div key={id} className='post-column cover'>
          <div className='content-covers-block-wrap clearfix'>
            <div className='content-covers-block-info'>
              <div className='content-covers-block-image' {...isExample && { style: { height: 120 } }}>
                {thumbnail && !isExample &&
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
                {isExample && <CoversImagePlaceholder height='100%' />}
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
                  <p className='post-excerpt' dangerouslySetInnerHTML={{ __html: post_excerpt }} />
                }
              </div>
            </div>
          </div>
        </div>
      );
    })}
  </div>
);
