import {ImagePlaceholderIcon} from '../../block-editor/ImagePlaceholderIcon';

const {__} = wp.i18n;

export const ImagePlaceholder = () => (
  <div className="carousel-header-image-placeholder">
    <ImagePlaceholderIcon width={80} height={80} />
    <p>
      {__('No image selected.', 'planet4-master-theme-backend')}
    </p>
  </div>
);
