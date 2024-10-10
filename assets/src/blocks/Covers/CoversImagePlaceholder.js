import {ImagePlaceholderIcon} from '../../block-editor/ImagePlaceholderIcon';

export const CoversImagePlaceholder = ({height}) => (
  <div className="covers-image-placeholder" style={{height}}>
    <ImagePlaceholderIcon width={20} height={20} fill="#fff" />
  </div>
);
