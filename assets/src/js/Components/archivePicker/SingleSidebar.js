import {useMemo, useEffect, useState} from '@wordpress/element';
import {toSrcSet} from './sizeFunctions';
import {ACTIONS, useArchivePickerContext} from '../ArchivePicker';

const {__, sprintf} = wp.i18n;

const renderDefinition = (key, value) => (
  <div>
    <dt>{key}</dt>
    <dd>{value}</dd>
  </div>
);

export default function SingleSidebar({image}) {
  const {
    errors,
    processingIds,
    processedIds,
    dispatch,
    selectedImages,
    includeInWp,
    images,
  } = useArchivePickerContext();
  const [wpImageLink, setWpImageLink] = useState('');
  const [showAddedMessage, setShowAddedMessage] = useState(false);

  useEffect(() => {
    if(image) {
      setWpImageLink(`${window.location.href.split('/wp-admin')[0]}/wp-admin/post.php?post=${image.id}&action=edit`)
    }
  }, [image]);

  useEffect(() => {
    if(image && processedIds.includes(image.id)) {
      setShowAddedMessage(true);

      const timeout = setTimeout(() => {
        setShowAddedMessage(false);
      }, 3000);

      return () => {
        setShowAddedMessage(false);
        clearTimeout(timeout);
      }
    }
  }, [processedIds]);

  useEffect(() => {
    dispatch({type: ACTIONS.REMOVE_ERROR, payload: {errorType: ACTIONS.PROCESS_IMAGES}});
  }, []);

  return useMemo(() => (
    <>
      <div className='picker-sidebar-header'>

        <div className='info'>
          {image && (
            <>
              {processingIds.includes(image.id) && !image.wordpress_id && __('Processing...', 'planet4-master-theme-backend')}
              {showAddedMessage && image.wordpress_id && __('Added to Library', 'planet4-master-theme-backend')}
            </>
          )}
        </div>
        <button
          className="close-sidebar"
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
          {image.wordpress_id ? (
            <a
              className="sidebar-action"
              href={wpImageLink}
            >
              {sprintf(__('Wordpress image #%s', 'planet4-master-theme-backend'), image.wordpress_id)}
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

          <img
            srcSet={toSrcSet(image.sizes, {maxWidth: 600})}
            title={image.title}
            alt={image.title}
          />

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
    errors,
    showAddedMessage,
    selectedImages,
    wpImageLink,
  ]);
}
