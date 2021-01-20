import {Fragment} from '@wordpress/element';
import {
  SelectControl,
  ToggleControl,
  FocalPointPicker,
} from '@wordpress/components';
import {URLInput} from '@wordpress/block-editor'

export const SidebarSlide = ({
    header_size,
    focal_points,
    link_url,
    link_url_new_tab,
    index,
    slides,
    setAttributes,
    image_url,
    onFocalPointsChange,
  }) => {
  const {__} = wp.i18n;
  const dimensions = { width: 300, height: 100 };

  const changeSlideAttribute = (slideAttributeName, index, value, slides) => {
    let slidesCopy = JSON.parse(JSON.stringify(slides));
    slidesCopy[index][slideAttributeName] = value;
    setAttributes({slides: slidesCopy});
  }

  return (
    <Fragment>
      <div className='carousel-header-slide-container'>
        <div className='carousel-header-slide-options-wrapper'>
          <div>{__('Select image and focal point', 'p4ge')}</div>

          <div className="row">
            <div className="col">
              <SelectControl
                label={__('Header text size', 'p4ge')}
                value={header_size}
                options={[
                  {label: 'h1', value: 'h1'},
                  {label: 'h2', value: 'h2'},
                  {label: 'h3', value: 'h3'},
                ]}
                onChange={value => changeSlideAttribute('header_size', index, value, slides)}
              />
            </div>
          </div>
          <div className="row">
            <div className="col">
              <URLInput
                label={__('Url for link', 'p4ge')}
                value={link_url}
                onChange={value => changeSlideAttribute('link_url', index, value, slides)}
                autoFocus={false}
              />
            </div>
          </div>
          <div className="row">
            <div className="col">
              <div className="InlineToggleControl">
                <ToggleControl
                  help={__('Open link in a new tab', 'p4ge')}
                  checked={link_url_new_tab}
                  onChange={value => changeSlideAttribute('link_url_new_tab', index, value, slides)}
                />
              </div>
            </div>
          </div>
          <div className="row">
            <div className="col">
              <FocalPointPicker
                label={__('Select focal point', 'p4ge')}
                url={image_url}
                dimensions={dimensions}
                value={focal_points?.x && focal_points?.y ? focal_points : { x: .5, y: .5 }}
                onChange={value => onFocalPointsChange(index, value)}
                key={index}
              />
            </div>
          </div>
        </div>
      </div>
    </Fragment>
  );
}
