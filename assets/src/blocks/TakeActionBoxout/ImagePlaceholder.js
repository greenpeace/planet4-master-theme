import {ImagePlaceholderIcon} from '../../components/ImagePlaceholderIcon';

export const ImagePlaceholder = ({children}) =>
  <div className="boxout-image-placeholder">
    <ImagePlaceholderIcon width={20} height={20} fill="#fff" />
    {children}
  </div>;
