const {forwardRef, useMemo} = wp.element;

export const SlideWithRef = ({
  children,
  active,
  focusable,
}, ref) => useMemo(() => (
  <li
    className={`carousel-item ${active ? 'active' : ''}`}
    tabIndex={focusable ? 0 : -1}
    ref={ref}
    role="tabpanel"
    // aria-hidden={!active}
    aria-label="Text goes here!"
  >
    <div className="carousel-item-mask">
      {children}
    </div>
  </li>
), [children, active, focusable, ref]);

export const Slide = forwardRef(SlideWithRef);
