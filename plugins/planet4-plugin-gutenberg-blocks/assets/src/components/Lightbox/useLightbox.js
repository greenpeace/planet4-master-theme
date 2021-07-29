import { useState } from '@wordpress/element';

export const useLightbox = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [index, setIndex] = useState(0);

  const openLightbox = index => {
    setIsOpen(true);
    setIndex(index);
  };

  const closeLightbox = () => {
    setIsOpen(false);
  };

  return { isOpen, index, openLightbox, closeLightbox };
}
