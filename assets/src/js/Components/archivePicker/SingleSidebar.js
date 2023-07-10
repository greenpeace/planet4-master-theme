import {Fragment} from '@wordpress/element';
import {toSrcSet} from './sizeFunctions';

const {__, sprintf} = wp.i18n;

const PREVIEW_MAX_SIZE = 1300;

const wpImageLink = id => `${window.location.href.split('/wp-admin')[0]}/wp-admin/post.php?post=${id}&action=edit`;

const renderDefinition = (key, value) => (
  <div>
    <dt>{key}</dt>
    <dd>{value}</dd>
  </div>
);

export const SingleSidebar = ({image, processingError, processingImages, includeInWp, closeSidebar}) => {
  const original = image ? image.original : {};

  const renderImage = () => (
    <img
      key={original.url}
      srcSet={toSrcSet(image.sizes, {maxWidth: PREVIEW_MAX_SIZE})}
      title={image.title}
      alt={image.title}
    />
  );

  return (
    <Fragment>
      <button
        className="close-sidebar"
        onClick={closeSidebar}
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
          <path
            fillRule="evenodd"
            clipRule="evenodd"
            d="M0.255182 0.610127L0.334085 0.508533L0.427136 0.410084C0.957432 -0.0990005 1.80189 -0.137694 2.37896 0.314142L2.48953 0.410084L10 7.62013L17.5105 0.410084L17.621 0.314142C18.1981 -0.137694 19.0426 -0.0990005 19.5729 0.410084L19.6659 0.508533L19.7448 0.610127C20.137 1.16455 20.072 1.91084 19.5729 2.38998L11.8542 9.79923L19.5729 17.2101C20.1424 17.7568 20.1424 18.6432 19.5729 19.19C19.0033 19.7367 18.08 19.7367 17.5105 19.19L10 11.9799L2.48953 19.19C1.92002 19.7367 0.99665 19.7367 0.427136 19.19C-0.142379 18.6432 -0.142379 17.7568 0.427138 17.2101L8.14583 9.79923L0.427136 2.38998C-0.0719664 1.91084 -0.137029 1.16455 0.255182 0.610127Z"
            fill="#020202"
          />
        </svg>
      </button>
      {!!processingError && (
        <div className="error">{
          sprintf(__('Error: %s', 'planet4-master-theme-backend'), processingError.message)}
        </div>
      )}
      {!!processingImages && (
        <div className="info">{__('Processing...', 'planet4-master-theme-backend')}</div>
      )}
      {image.wordpress_id && (
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
    </Fragment>
  );
};
