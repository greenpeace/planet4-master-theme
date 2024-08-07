import {URLInput} from '../../block-editor/URLInput/URLInput';

const {PanelRow, PanelBody, FocalPointPicker, CheckboxControl} = wp.components;
const {useState} = wp.element;
const {__} = wp.i18n;

export const SidebarSlide = ({
  focal_points,
  link_url,
  link_url_new_tab,
  index,
  image_url,
  image_alt,
  image_srcset,
  isLastItem,
  header,
  changeSlideAttribute,
  onDragStartHandler,
  onDragEndHandler,
  onDragOverHandler,
  upOrDownHandler,
  goToSlideHandler,
}) => {
  const [isOpened, setIsOpened] = useState(false);

  const onFocalPointsChange = (idx, value) => {
    let focalPoints = null;
    if (null !== value) {
      focalPoints = JSON.parse(JSON.stringify(value));
    }
    changeSlideAttribute('focal_points', idx)(focalPoints);
  };

  const onToggleHandler = opened => {
    setIsOpened(opened);
    if (opened) {
      goToSlideHandler(index);
    }
  };

  return (
    <div
      className={`sidebar-slide ${isOpened ? 'opened-slide' : ''}`}
      data-index={index}
      draggable={!isOpened}
      onDragStart={onDragStartHandler}
      onDragEnd={onDragEndHandler}
      onDragOver={onDragOverHandler}
    >
      <PanelBody
        key={index}
        initialOpen={isOpened}
        onToggle={onToggleHandler}
        title={<div className="slide-item-wrapper">
          <div
            className="slide-item-draggable-button"
            onClick={evt => {
              // This method avoids to propagate the toggle event
              evt.stopPropagation();
            }}
            role="presentation"
          >
            <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="2" height="2" fill="black" /><rect x="6" width="2" height="2" fill="black" /><rect y="6" width="2" height="2" fill="black" /><rect x="6" y="6" width="2" height="2" fill="black" /><rect y="12" width="2" height="2" fill="black" /><rect x="6" y="12" width="2" height="2" fill="black" /></svg>
          </div>
          <div className="slide-item-order-arrows">
            <div
              className={`arrow-button ${index === 0 ? 'disabled' : ''}`}
              onClick={upOrDownHandler}
              data-index={index}
              data-type="up"
              role="presentation" />
            <div
              className={`arrow-button ${isLastItem ? 'disabled' : ''}`}
              onClick={upOrDownHandler}
              data-index={index}
              data-type="down"
              role="presentation" />
          </div>
          <img
            className="slide-item-image"
            draggable={false}
            srcSet={image_srcset}
            alt={image_alt}
          />
          <span className="slide-item-text">{ header || <i>{__('No title', 'planet4-blocks-backend')}</i> }</span>
          <div className="arrow-button" data-type="toggle" />
        </div>}
      >
        <PanelRow>
          <div>
            <div>
              <div className="row">
                <div className="col">
                  <URLInput
                    label={__('Url for link', 'planet4-blocks-backend')}
                    value={link_url}
                    onChange={changeSlideAttribute('link_url', index)}
                  />
                </div>
              </div>
              <div className="row">
                <div className="col">
                  <CheckboxControl
                    label={__('Open in a new tab', 'planet4-blocks-backend')}
                    value={link_url_new_tab}
                    checked={link_url_new_tab}
                    onChange={changeSlideAttribute('link_url_new_tab', index)}
                  />
                </div>
              </div>
              {image_url && (
                <div className="row">
                  <div className="col">
                    <FocalPointPicker
                      label={__('Image focal point', 'planet4-blocks-backend')}
                      url={image_url}
                      dimensions={{width: 300, height: 100}}
                      value={focal_points?.x && focal_points?.y ? focal_points : {x: .5, y: .5}}
                      onChange={value => onFocalPointsChange(index, value)}
                      key={index}
                    />
                  </div>
                </div>
              )}
            </div>
          </div>
        </PanelRow>
      </PanelBody>
    </div>
  );
};
