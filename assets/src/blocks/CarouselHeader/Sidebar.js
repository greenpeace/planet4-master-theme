import {SidebarSlide} from './SidebarSlide';

const {InspectorControls} = wp.blockEditor;
const {CheckboxControl, PanelBody} = wp.components;
const {useState, useRef, useEffect, useMemo, useCallback} = wp.element;
const {__} = wp.i18n;

const getNode = index => (
  document.querySelector(`.sidebar-slide[data-index="${index}"]`)
);

export const Sidebar = ({
  carouselAutoplay,
  setAttributes,
  slides,
  changeSlideAttribute,
  goToSlide,
}) => {
  const [dragTarget, setDragTarget] = useState(null);
  const [draggedSlide, setDraggedSlide] = useState(null);
  const slidesRef = useRef(null);

  const upOrDownHandler = useCallback(evt => {
    evt.stopPropagation();

    if (!slidesRef.current) {
      return;
    }

    const currentIndex = parseInt(evt.currentTarget.dataset.index);
    const currentNode = getNode(currentIndex);
    let siblingIndex = -1;

    switch (evt.currentTarget.dataset.type) {
    case 'up':
      siblingIndex = currentIndex - 1;
      break;
    case 'down':
      siblingIndex = currentIndex + 1;
      break;
    }

    const siblingNode = getNode(siblingIndex);

    // Get the data-index from sidebar-slide
    // Set to position absolute and find it sibbling element
    const currentNodeCloned = currentNode.cloneNode(true);
    const siblingNodeCloned = siblingNode.cloneNode(true);

    currentNodeCloned.classList.add('cloned-slide');
    siblingNodeCloned.classList.add('cloned-slide');

    // Append before setting the top
    slidesRef.current.append(currentNodeCloned);
    slidesRef.current.append(siblingNodeCloned);

    currentNode.style.opacity = 0;
    siblingNode.style.opacity = 0;

    currentNodeCloned.style.top = `${currentNode.offsetTop}px`;
    siblingNodeCloned.style.top = `${siblingNode.offsetTop}px`;

    const timeout = setTimeout(() => {
      // Switch positions
      currentNodeCloned.style.top = `${siblingNodeCloned.offsetTop}px`;
      siblingNodeCloned.style.top = `${currentNodeCloned.offsetTop}px`;

      setTimeout(() => {
        // Re order slides
        // eslint-disable-next-line array-callback-return
        setAttributes({slides: slides.reduce((prev, curr, idx) => {
          if (idx !== currentIndex && idx !== siblingIndex) {
            if (prev) {
              return prev.concat(curr);
            }
          } else {
            if (idx === currentIndex) {
              return prev.concat(slides[siblingIndex]);
            }
            if (idx === siblingIndex) {
              return prev.concat(slides[currentIndex]);
            }
            return prev;
          }
        }, [])});

        // Add a delay
        setTimeout(() => {
          currentNode.style.opacity = 1;
          siblingNode.style.opacity = 1;

          slidesRef.current.removeChild(currentNodeCloned);
          slidesRef.current.removeChild(siblingNodeCloned);
        }, 100);
      }, 500);
    });

    return () => {
      clearTimeout(timeout);
    };
  }, [setAttributes, slides]);

  const onDragStartHandler = useCallback(evt => {
    // This is a workaround that avoids to show the `not-allowed` icon on Chrome/Windows
    evt.dataTransfer.effectAllowed = 'move';
    setDraggedSlide(getNode(parseInt(evt.currentTarget.dataset.index)));
  }, [setDraggedSlide]);

  const onDragEndHandler = useCallback(evt => {
    evt.preventDefault();

    if (draggedSlide) {
      draggedSlide.classList.remove('dragged-slide');
    }

    setDragTarget(null);
    setDraggedSlide(null);

    if (slidesRef.current) {
      setAttributes({
        slides: Object.values(slidesRef.current.children).map(node => slides[parseInt(node.dataset.index)]),
      });
    }
  }, [setAttributes, draggedSlide, slides]);

  const onDragOverHandler = evt => {
    evt.preventDefault();
    evt.dataTransfer.dropEffect = 'move';
    setDragTarget(getNode(parseInt(evt.currentTarget.dataset.index)));
  };

  useEffect(() => {
    if (draggedSlide) {
      draggedSlide.classList.add('dragged-slide');
    }
  }, [draggedSlide]);

  useEffect(() => {
    if (slidesRef.current && draggedSlide && dragTarget) {
      // Check if it's the last node into the list
      const lastNode = slidesRef.current.children[slidesRef.current.children.length - 1];
      if (dragTarget.dataset.index === lastNode.dataset.index) {
        lastNode.insertAdjacentElement('afterend', draggedSlide);
      } else {
        slidesRef.current.insertBefore(draggedSlide, dragTarget);
      }
    }
  }, [dragTarget, draggedSlide]);

  return useMemo(() => <InspectorControls>
    <PanelBody title={__('Settings', 'planet4-master-theme-backend')}>
      <CheckboxControl
        __nextHasNoMarginBottom
        label={__('Carousel Autoplay', 'planet4-master-theme-backend')}
        help={__('Select to trigger images autoslide', 'planet4-master-theme-backend')}
        value={carouselAutoplay}
        checked={carouselAutoplay === true}
        onChange={value => setAttributes({carousel_autoplay: value})}
      />
    </PanelBody>
    <div ref={slidesRef} className="sidebar-slides">
      { slides.map((slide, index) => <SidebarSlide
        {...slide}
        index={index}
        key={`${slide.image_srcset}${index}`}
        isLastItem={index === slides.length - 1}
        changeSlideAttribute={changeSlideAttribute}
        goToSlideHandler={goToSlide}
        onDragStartHandler={onDragStartHandler}
        onDragEndHandler={onDragEndHandler}
        onDragOverHandler={onDragOverHandler}
        upOrDownHandler={upOrDownHandler}
      />) }
    </div>
    <PanelBody title={__('Learn more about this block ', 'planet4-master-theme-backend')} initialOpen={false}>
      <p className="components-base-control__help">
        <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/carousel-header/" rel="noreferrer">
          P4 Handbook Carousel Header
        </a>
        {' '} &#127904;
      </p>
    </PanelBody>
  </InspectorControls>,
  [
    carouselAutoplay,
    changeSlideAttribute,
    goToSlide,
    setAttributes,
    slides,
    onDragEndHandler,
    upOrDownHandler,
    onDragStartHandler,
  ]);
};
