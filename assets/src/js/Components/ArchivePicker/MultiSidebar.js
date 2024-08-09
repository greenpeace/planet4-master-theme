import {toSrcSet} from './sizeFunctions';
import {ACTIONS, useArchivePickerContext} from './ArchivePicker';

const {__} = wp.i18n;
const {useMemo} = wp.element;

export default function MultiSidebar() {
  const {selectedImages, dispatch} = useArchivePickerContext();
  return useMemo(
    () => <>
      <div className="picker-sidebar-header">
        <span>{selectedImages.length} {__('images selected', 'planet4-master-theme-backend')}</span>
        <button
          className="close-sidebar"
          onClick={() => {
            dispatch({type: ACTIONS.CLOSE_SIDEBAR});
          }}
        />
      </div>
      {selectedImages.map(image => (
        <img
          srcSet={toSrcSet(image.sizes, {maxWidth: 700})}
          title={image.title}
          alt={image.title}
          key={image.id}
          width={80}
          onClick={() => {
            dispatch({type: ACTIONS.DESELECT_IMAGE, payload: {selection: image}});
          }}
          role="presentation"
        />
      ))}
    </>,
    [selectedImages]
  );
}
