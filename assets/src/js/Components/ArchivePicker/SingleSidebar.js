import {toSrcSet} from './sizeFunctions';
import {ACTIONS, ADMIN_VIEW, EDITOR_VIEW, useArchivePickerContext} from './ArchivePicker';

const {__, sprintf} = wp.i18n;
const {useMemo, useEffect, useState} = wp.element;

const renderDefinition = (key, value) => (
  <div>
    <dt>{key}</dt>
    <dd>{value}</dd>
  </div>
);

export default function SingleSidebar({image}) {
  const {
    error,
    errors,
    processing,
    processingIds,
    processedIds,
    dispatch,
    selectedImages,
    includeInWp,
    images,
    imageAdded,
    processImageToAddToEditor,
    currentBlockImageId,
    view,
  } = useArchivePickerContext();
  const [wpImageLink, setWpImageLink] = useState('');
  const [showAddedMessage, setShowAddedMessage] = useState(false);

  useEffect(() => {
    if (image) {
      setWpImageLink(`${window.location.href.split('/wp-admin')[0]}/wp-admin/post.php?post=${image.wordpress_id}&action=edit`);
    }
  }, [image]);

  useEffect(() => {
    if ((image && processedIds.includes(image.id))) {
      setShowAddedMessage(true);

      const timeout = setTimeout(() => {
        setShowAddedMessage(false);
      }, 3000);

      return () => {
        setShowAddedMessage(false);
        clearTimeout(timeout);
      };
    }
  }, [processedIds]);

  useEffect(() => {
    dispatch({type: ACTIONS.REMOVE_ERROR, payload: {errorType: ACTIONS.PROCESS_IMAGES}});
  }, []);

  return useMemo(() => (
    <>
      <div className="picker-sidebar-header">

        <div className="info">
          {(image && view === ADMIN_VIEW) && (
            <>
              {processingIds.includes(image.id) && !image.wordpress_id && __('Processing…', 'planet4-master-theme-backend')}
              {showAddedMessage && image.wordpress_id && __('Added to Library', 'planet4-master-theme-backend')}
            </>
          )}
          {(image && view === EDITOR_VIEW) && (
            <>
              {processing && __('Processing…', 'planet4-master-theme-backend')}
              {imageAdded && __('Added!', 'planet4-master-theme-backend')}
              {error && __('Error Adding Image to Post!!!', 'planet4-master-theme-backend')}
            </>
          )}
        </div>
        <button
          className="close-sidebar"
          aria-label={__('Close', 'planet4-master-theme-backend')}
          onClick={() => {
            dispatch({type: ACTIONS.CLOSE_SIDEBAR});
          }}
        />
      </div>

      {!!errors && errors[ACTIONS.PROCESS_IMAGES] && (
        <div className="error" dangerouslySetInnerHTML={{__html: errors[ACTIONS.PROCESS_IMAGES].message}} />
      )}

      {image && (
        <>
          {view === ADMIN_VIEW && (
            <>
              {image.wordpress_id ? (
                <a
                  className="sidebar-action"
                  href={wpImageLink}
                >
                  {
                    // translators: 1: image ID
                    sprintf(__('Wordpress image #%s', 'planet4-master-theme-backend'), image.wordpress_id)
                  }
                </a>
              ) : (
                <button
                  disabled={processingIds.includes(image.id)}
                  className="button sidebar-action"
                  onClick={async () => await includeInWp([image.id])}
                >
                  {__('Import to Library', 'planet4-master-theme-backend')}
                </button>
              )}
            </>
          )}
          {view === EDITOR_VIEW && (
            <>
              {!image.wordpress_id ? (
                <button
                  disabled={!!processing}
                  className="button sidebar-action"
                  onClick={async () => await includeInWp([image.id], view)}
                >
                  {__('Import to Library & Post', 'planet4-master-theme-backend')}
                </button>
              ) : (
                <button
                  disabled={!!processing}
                  className="button sidebar-action"
                  style={{display: currentBlockImageId === image.wordpress_id ? 'none' : ''}}
                  onClick={async () => await processImageToAddToEditor(image.wordpress_id)}
                >
                  {__('Add image to Post', 'planet4-master-theme-backend')}
                </button>
              )}
            </>
          )}
          <img
            srcSet={toSrcSet(image.sizes, {maxWidth: 600})}
            title={image.title}
            alt={image.title}
          />
          {(image.wordpress_id && view === EDITOR_VIEW) && (
            <a
              className="sidebar-action"
              href={wpImageLink}
            >
              {
                // translators: 1: image ID
                sprintf(__('Wordpress image #%s', 'planet4-master-theme-backend'), image.wordpress_id)
              }
            </a>
          )}
          <dl className={'picker-sidebar-fields'}>
            {renderDefinition(
              __('URL', 'planet4-master-theme-backend'),
              <a href={image.original.url}>{image.original.url}</a>
            )}
            {renderDefinition(
              __('Dimensions', 'planet4-master-theme-backend'),
              `${image.original.width} x ${image.original.height}`
            )}
            {renderDefinition(
              __('Title', 'planet4-master-theme-backend'),
              image.title
            )}
            {renderDefinition(
              __('Caption', 'planet4-master-theme-backend'),
              image.caption
            )}
            {renderDefinition(
              __('Credit', 'planet4-master-theme-backend'),
              image.credit
            )}
            {renderDefinition(
              __('Original language title', 'planet4-master-theme-backend'),
              image.original_language_title
            )}
            {renderDefinition(
              __('Original language description', 'planet4-master-theme-backend'),
              image.original_language_description
            )}
          </dl>
          {image.wordpress_id && (
            <a
              className="button edit-image"
              href={wpImageLink}
            >
              {__('Edit image', 'planet4-master-theme-backend')}
            </a>
          )}
        </>
      )}
    </>
  ), [
    image,
    images,
    processingIds,
    processedIds,
    error,
    errors,
    showAddedMessage,
    selectedImages,
    wpImageLink,
    processing,
    imageAdded,
    view,
  ]);
}
