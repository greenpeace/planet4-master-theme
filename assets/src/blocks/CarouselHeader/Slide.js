const {forwardRef, useMemo} = wp.element;

export const SlideWithRef = ({
  children,
  active,
  focusable,
  handleUserInteraction,
}, ref) => useMemo(() => (
  <li
    className={`carousel-item ${active ? 'active' : ''}`}
    tabIndex={focusable ? 0 : -1}
    aria-hidden={focusable ? 'false' : 'true'}
    aria-current={active ? 'true' : 'false'}
    ref={ref}
    role="tabpanel"
    alt=""
    onFocus={evt => {
      evt.preventDefault();
      evt.stopPropagation();
      handleUserInteraction();
    }}
  >
    <div className="carousel-item-mask">
      {children}
    </div>
  </li>
), [children, active, focusable, handleUserInteraction, ref]);

export const Slide = forwardRef(SlideWithRef);
