import { Fragment } from '@wordpress/element';
import { toSrcSet } from './sizeFunctions';

const { __ } = wp.i18n;

export const MultiSidebar = ({ selectedImages, toggleMultiSelection }) => (
  <Fragment>
    <p>{selectedImages.length} {__('images selected', 'planet4-master-theme-backend')}</p>
    <ul>
      {selectedImages.map(selected => (
        <li
          key={selected.id}
        >
          <img
            srcSet={toSrcSet(selected.sizes, { maxWidth: 700 })}
            title={selected.title}
            alt={selected.title}
            width={80}
            onClick={() => toggleMultiSelection(selected)}
          />
        </li>
      ))}
    </ul>
  </Fragment>
);
