import {Fragment} from '@wordpress/element';
import {toSrcSet} from './sizeFunctions';

const {__} = wp.i18n;

export const MultiSidebar = ({selectedImages, toggleMultiSelection}) => (
  <Fragment>
    <p>{selectedImages.length} {__('images selected', 'planet4-master-theme-backend')}</p>
    {selectedImages.map(selected => (
      <img
        srcSet={toSrcSet(selected.sizes, {maxWidth: 700})}
        title={selected.title}
        alt={selected.title}
        key={selected.id}
        width={80}
        onClick={() => toggleMultiSelection(selected)}
        role="presentation"
      />

    ))}
  </Fragment>
);
