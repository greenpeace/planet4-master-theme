const {forwardRef, useMemo} = wp.element;

export const SlideWithRef = ({
  children,
  active,
  focusable,
}, ref) => useMemo(() => (
  <li
    className={`carousel-item ${active ? 'active' : ''}`}
    tabIndex={focusable ? 0 : -1}
    // eslint-disable-next-line react/no-unknown-property
    area-hidden={focusable ? 'false' : 'true'}
    ref={ref}
    role="tabpanel"
    alt=""
  >
    <div className="carousel-item-mask">
      {children}
    </div>
  </li>
), [children, active, focusable, ref]);

export const Slide = forwardRef(SlideWithRef);
