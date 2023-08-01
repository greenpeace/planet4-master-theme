import {useMemo} from '@wordpress/element';
import MultiSearchOption from './MultiSearchOption';
import {useArchivePickerContext} from '../ArchivePicker';

const {__} = wp.i18n;

export default function ArchivePickerToolbar() {
  const {
    bulkSelect,
    selectedImages,
    selectedImagesAmount,
    dispatch,
    includeInWp,
  } = useArchivePickerContext();

  return useMemo(() => (
    <div className="archive-picker-toolbar">
      {!bulkSelect && <MultiSearchOption />}
      <nav className="nav-bulk-select">
        {bulkSelect && (
          <button
            disabled={false}
            onClick={() => {
              dispatch({type: 'DISABLE_BULK_SELECT'});
            }}
            type="button"
            className="button"
          >{__('Cancel', 'planet4-master-theme-backend')}</button>
        )}

        {bulkSelect ?
          <button
            onClick={async () => {
              if (window.confirm(`You are about to import [${Object.keys(selectedImages).length}] photos to the media library. 'Cancel' to stop, 'OK' to import.`)) { // eslint-disable-line no-alert
                await includeInWp(Object.keys(selectedImages));
              }
            }}
            type="button"
            className="button"
          >{__('Bulk Upload', 'planet4-master-theme-backend')}</button> :
          <button
            onClick={() => {
              dispatch({type: 'ENABLE_BULK_SELECT'});
            }}
            type="button"
            className="button"
          >{__('Bulk Select', 'planet4-master-theme-backend')}</button>
        }
      </nav>
    </div>
  ), [bulkSelect, selectedImages, selectedImagesAmount]);
}
