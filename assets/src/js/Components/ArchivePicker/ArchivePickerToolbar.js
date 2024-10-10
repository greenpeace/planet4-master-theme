const {useMemo} = wp.element;
import classNames from 'classnames';
import {ACTIONS, useArchivePickerContext} from './ArchivePicker';
import MultiSearchOption from './MultiSearchOption';

const {sprintf, __} = wp.i18n;

export default function ArchivePickerToolbar() {
  const {
    images,
    loading,
    bulkSelect,
    selectedImages,
    selectedImagesIds,
    processingIds,
    processing,
    dispatch,
    includeInWp,
  } = useArchivePickerContext();

  return useMemo(() => (
    <div className="archive-picker-toolbar">
      {!bulkSelect && <h3 className="archive-picker-title">{__('Greenpeace Media', 'planet4-master-theme-backend')}</h3>}
      <nav className={classNames('nav-bulk-select', {'bulk-enabled': bulkSelect})}>
        {(bulkSelect && processingIds.length) ? (
          // translators: images count
          <span className="info">{sprintf(__('Processing %d images', 'planet4-master-theme-backend'), processingIds.length)}</span>
        ) : null}
        {bulkSelect && (
          <button
            disabled={processing}
            onClick={() => {
              dispatch({type: ACTIONS.BULK_SELECT_CANCEL});
            }}
            type="button"
            className="button btn-cancel-bulk-select"
          >{__('Cancel', 'planet4-master-theme-backend')}</button>
        )}

        {bulkSelect ?
          <button
            disabled={!selectedImagesIds.length || processing}
            onClick={async () => {
              if (window.confirm(`You are about to import [${selectedImagesIds.length}] photos to the media library. 'Cancel' to stop, 'OK' to import.`)) { // eslint-disable-line no-alert
                await includeInWp(selectedImagesIds);
              }
            }}
            type="button"
            className="button"
          >{__('Bulk Upload', 'planet4-master-theme-backend')}</button> :
          <button
            disabled={!images.length}
            onClick={() => dispatch({type: ACTIONS.BULK_SELECT_ENABLE})}
            type="button"
            className="button"
          >{__('Bulk Select', 'planet4-master-theme-backend')}</button>
        }
      </nav>
      {!bulkSelect && <MultiSearchOption />}
    </div>
  ), [images, loading, bulkSelect, selectedImages, selectedImagesIds, processing, processingIds]);
}
