import {useMemo} from '@wordpress/element';
import {toSrcSet} from './sizeFunctions';
import {useArchivePickerContext} from '../ArchivePicker';

const {__} = wp.i18n;

export default function MultiSidebar() {
  const {selectedImages, selectedImagesAmount, dispatch} = useArchivePickerContext();
  return useMemo(
    () => <>
      <p>{selectedImagesAmount} {__('images selected', 'planet4-master-theme-backend')}</p>
      {Object.values(selectedImages).map(selected => (
        <img
          srcSet={toSrcSet(selected.sizes, {maxWidth: 700})}
          title={selected.title}
          alt={selected.title}
          key={selected.id}
          width={80}
          onClick={() => dispatch({type: 'TOGGLE_IMAGE', payload: {image: selected}})}
          role="presentation"
        />
      ))}
    </>,
    [selectedImages]
  );
}
