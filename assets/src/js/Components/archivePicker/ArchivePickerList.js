import {useMemo, useCallback} from '@wordpress/element';
import classNames from 'classnames';
import {toSrcSet} from './sizeFunctions';
import {useArchivePickerContext} from '../ArchivePicker';

const {__} = wp.i18n;

export default function ArchivePickerList() {
  const {
    images,
    loading,
    loaded,
    dispatch,
    selectedImages,
  } = useArchivePickerContext();

  console.log('IMAGE DETAILS: ', images);

  const onScrollHandler = useCallback(event => {
    const {scrollHeight, scrollTop, clientHeight} = event.target;
    const tillEnd = (scrollHeight - scrollTop - clientHeight) / scrollHeight;

    if (tillEnd < 0.1 && !loading && loaded) {
      dispatch({type: 'NEXT_PAGE'});
    }
  }, [dispatch, loaded]);

  return useMemo(
    /* eslint-disable no-nested-ternary */
    () => (images.length) ? (
      <ul className="picker-list" onScroll={onScrollHandler}>
        {images.map(image => {
          const {
            id,
            sizes,
            title,
            alt,
            wordpress_id,
            original,
          } = image;

          try {
            return <li
              key={id}
              data-wordpress-id={wordpress_id}>
              {wordpress_id && (
                <div className="added-to-library">
                  {__('Added to Media Library', 'planet4-master-theme-backend')}
                </div>
              )}
              <img
                className={classNames({'picker-selected': selectedImages[image.id] && true})}
                srcSet={toSrcSet(sizes, {maxWidth: 900})}
                title={title}
                alt={alt}
                width={200 * (original.width / original.height)}
                height={200}
                onClick={event => {
                  // metaKey for Mac users
                  dispatch({type: 'TOGGLE_IMAGE', payload: {image, multiSelection: (event.ctrlKey || event.metaKey)}});
                }}
                role="presentation"
              />
            </li>;
          } catch (exception) {
            return <li key={id}>
              <span>{image.title}</span>
              <span>No image available. {`${exception}`}</span>
            </li>;
          }
        })}
      </ul>
    ) : ((!loading && loaded && !images.length) ? <div className="empty-media-items-message">No media items found</div> : null),
    [images, loading, loaded, selectedImages, dispatch]);
}
