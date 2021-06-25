import { getStyleFromClassName } from '../getStyleFromClassName'

const GALLERY_LAYOUTS = ['slider', 'three-columns', 'grid'];

export const GALLERY_BLOCK_CLASSES = {
  'slider': 'carousel-wrap',
  'three-columns': 'split-three-column',
  'grid': 'gallery-grid'
};

export const getGalleryLayout = (className, style) => {
  let layout = style > 0 ? GALLERY_LAYOUTS[style - 1] : 'slider';
  const styleClass = getStyleFromClassName(className);
  if (styleClass) {
    layout = styleClass;
  }
  return layout;
};
