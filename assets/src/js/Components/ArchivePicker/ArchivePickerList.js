import classNames from 'classnames';
import {ACTIONS, useArchivePickerContext} from './ArchivePicker';
import {toSrcSet} from './sizeFunctions';

const {__} = wp.i18n;
const {useCallback, useEffect, useMemo, useState} = wp.element;

const sort = indexes => indexes.sort(
  (a, b) => (a > b) ? a : null
);

const fill = indexes => {
  const min = indexes[0];
  const max = indexes[indexes.length - 1];
  return [min].concat([...Array((max - min)).keys()].map(n => (++n + min)));
};

const multiSelect = (indexes, index, selected, isShiftKey) => {
  if (isShiftKey) {
    return fill(sort([...indexes].concat(index)));
  }
  if (!selected.includes(index)) {
    return [...selected, index];
  }
  return [...selected].filter(idx => idx !== index);
};

export default function ArchivePickerList() {
  const {
    images,
    loading,
    loaded,
    bulkSelect,
    dispatch,
    selectedImages,
    selectedImagesIds,
    processingIds,
    processedIds,
    currentBlockImageId,
  } = useArchivePickerContext();
  const [selectedIndexes, setSelectedIndexes] = useState([]);

  const onScrollHandler = useCallback(event => {
    const {scrollHeight, scrollTop, clientHeight} = event.target;
    const tillEnd = (scrollHeight - scrollTop - clientHeight) / scrollHeight;

    if (tillEnd < 0.1 && !loading && loaded) {
      dispatch({type: ACTIONS.NEXT_PAGE});
    }
  }, [dispatch, loaded]);

  const onClickHandler = useCallback(evt => {
    const {id, wordpressId} = evt.currentTarget.dataset;
    if (wordpressId && bulkSelect) {
      return;
    }

    const index = images.findIndex(img => img.id === id);
    const multiSelection = (evt.ctrlKey || evt.metaKey || evt.shiftKey);

    let indexes = [...selectedIndexes];

    if (bulkSelect || multiSelection) {
      indexes = multiSelect(indexes, index, selectedIndexes, (evt.shiftKey && true));
    } else {
      indexes = [index];
    }
    setSelectedIndexes(indexes);
  }, [bulkSelect, selectedIndexes, images]);

  useEffect(() => {
    dispatch({
      type: ACTIONS.SELECT_IMAGES,
      payload: {
        selection: selectedIndexes.map(idx => {
          if (bulkSelect) {
            if (!images[idx].wordpress_id) {
              return images[idx];
            }
          } else {
            return images[idx];
          }
          return null;
        }).filter(value => value !== null),
      },
    });
  }, [selectedIndexes]);

  useEffect(() => {
    if (bulkSelect) {
      // Clean up selected indexes
      setSelectedIndexes([]);
    }
  }, [bulkSelect]);

  useEffect(() => {
    if (processedIds.length && bulkSelect) {
      dispatch({type: ACTIONS.BULK_SELECT_CANCEL});
    }
  }, [processedIds]);

  return useMemo(
    /* eslint-disable no-nested-ternary */
    () => (images.length) ? (
      <ul className={classNames('picker-list', {'bulk-select': bulkSelect})} onScroll={onScrollHandler}>
        {images.map((image, index) => {
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
              data-id={id}
              data-wordpress-id={wordpress_id}
              data-index={index}
              onClick={onClickHandler}
              aria-hidden="true"
              className={classNames({'is-selected': selectedImagesIds.includes(id)}, {'is-disabled': wordpress_id && bulkSelect})}>
              <img
                className={classNames({'picker-selected': selectedImagesIds.includes(id)})}
                srcSet={toSrcSet(sizes, {maxWidth: 900})}
                title={title}
                alt={alt}
                width={200 * (original.width / original.height)}
                height={200}
                role="presentation"
              />
              {wordpress_id && (
                <div className="added-to-library">
                  {(currentBlockImageId === wordpress_id) ? (
                    <span>{__('Added to Media Library & Post', 'planet4-master-theme-backend')}</span>
                  ) : (
                    <span>{__('Added to Media Library', 'planet4-master-theme-backend')}</span>
                  )}
                </div>
              )}
              {bulkSelect && !wordpress_id && !processingIds.includes(image.id) && (
                <div
                  role="button"
                  aria-hidden="true"
                  className={classNames('bulk-select-checkbox', {'is-checked': selectedImagesIds.includes(id)})}
                />
              )}
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
    [
      images,
      loading,
      bulkSelect,
      loaded,
      selectedImages,
      selectedImagesIds,
      selectedIndexes,
      processingIds,
      processedIds,
      dispatch,
    ]);
}
