const {useState} = wp.element;

export const useLightbox = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [index, setIndex] = useState(0);

  const openLightbox = evt => {
    evt.preventDefault();
    setIsOpen(true);
    setIndex(parseInt(evt.currentTarget.dataset.index));
  };

  const closeLightbox = () => {
    setIsOpen(false);
  };

  return {isOpen, index, openLightbox, closeLightbox};
};
