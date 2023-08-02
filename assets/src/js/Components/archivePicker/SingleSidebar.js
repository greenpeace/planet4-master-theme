import {useMemo, useEffect} from '@wordpress/element';
import {toSrcSet} from './sizeFunctions';
import {useArchivePickerContext} from '../ArchivePicker';

const {__, sprintf} = wp.i18n;

const PREVIEW_MAX_SIZE = 1300;

const wpImageLink = id => `${window.location.href.split('/wp-admin')[0]}/wp-admin/post.php?post=${id}&action=edit`;

const renderDefinition = (key, value) => (
  <div>
    <dt>{key}</dt>
    <dd>{value}</dd>
  </div>
);

export default function SingleSidebar() {
  const {processingImages, selectedImagesAmount, error, errors, showAddedMessage, dispatch, selectedImages, includeInWp} = useArchivePickerContext();

  const image = Object.values(selectedImages)[0];
  const original = image ? image.original : {};

  const renderImage = () => (
    <img
      key={original.url}
      srcSet={toSrcSet(image.sizes, {maxWidth: PREVIEW_MAX_SIZE})}
      title={image.title}
      alt={image.title}
    />
  );

  // useEffect(() => {
  //   if(!!errors && errors['PROCESS_IMAGES']) {
  //     const timeout = setTimeout(() => {
  //       dispatch({type: 'REMOVE_ERROR', payload: {errorType: 'PROCESS_IMAGES'}});
  //     }, 5000);

  //     return () => {
  //       clearTimeout(timeout);
  //     }
  //   }
  // }, [errors]);

  useEffect(() => {
    dispatch({type: 'REMOVE_ERROR', payload: {errorType: 'PROCESS_IMAGES'}});
  }, []);

  return useMemo(() => (
    <>
      <button
        className="close-sidebar"
        onClick={() => dispatch({type: 'CLOSE_SIDEBAR'})}
      />

      {!!errors && errors['PROCESS_IMAGES'] && (
        <div className="error" dangerouslySetInnerHTML={{__html: errors['PROCESS_IMAGES'].message}} />
      )}

      {processingImages && (
        <div className="info">{__('Processing...', 'planet4-master-theme-backend')}</div>
      )}

      {showAddedMessage && (
        <div className="info">{__('Added to Library', 'planet4-master-theme-backend')}</div>
      )}

      {image.wordpress_id ? (
        <a
          className="sidebar-action"
          href={wpImageLink(image.wordpress_id)}
        >
          {sprintf(__('Wordpress image #%s', 'planet4-master-theme-backend'), image.wordpress_id)}
        </a>
      ) : (
        <button
          disabled={!!processingImages}
          className="button sidebar-action"
          onClick={async () => await includeInWp([image.id])}
        >
          {__('Import to Library', 'planet4-master-theme-backend')}
        </button>
      )}
      {renderImage()}
      <dl className={'picker-sidebar-fields'}>
        {renderDefinition(
          __('URL', 'planet4-master-theme-backend'),
          <a href={original.url}>{original.url}</a>
        )}
        {renderDefinition(
          __('Dimensions', 'planet4-master-theme-backend'),
          `${original.width} x ${original.height}`
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
          href={wpImageLink(image.wordpress_id)}
        >
          {__('Edit image', 'planet4-master-theme-backend')}
        </a>
      )}
    </>
  ), [
    image,
    processingImages,
    error,
    errors,
    showAddedMessage,
    selectedImages,
    selectedImagesAmount,
  ]);
}
