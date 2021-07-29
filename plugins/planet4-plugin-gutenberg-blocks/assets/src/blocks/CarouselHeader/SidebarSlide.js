import {
  SelectControl,
  FocalPointPicker,
  CheckboxControl,
} from '@wordpress/components';
import { URLInput } from '../../components/URLInput/URLInput';
const { __ } = wp.i18n;

export const SidebarSlide = ({
  focal_points,
  link_url,
  link_url_new_tab,
  index,
  image_url,
  changeSlideAttribute,
}) => {

  const onFocalPointsChange = (index, value) => {
    let focalPoints = null;
    if (null !== value) {
      focalPoints = JSON.parse(JSON.stringify(value));
    }
    changeSlideAttribute('focal_points', index)(focalPoints);
  }

  return (
    <>
      <div className='carousel-header-slide-container'>
        <div className='carousel-header-slide-options-wrapper'>
          <div className='row'>
            <div className='col'>
              <URLInput
                label={__('Url for link', 'planet4-blocks-backend')}
                value={link_url}
                onChange={changeSlideAttribute('link_url', index)}
              />
            </div>
          </div>
          <div className='row'>
            <div className='col'>
              <CheckboxControl
                label={__('Open in a new tab', 'planet4-blocks-backend')}
                value={link_url_new_tab}
                checked={link_url_new_tab}
                onChange={changeSlideAttribute('link_url_new_tab', index)}
              />
            </div>
          </div>
          {image_url && (
            <div className='row'>
              <div className='col'>
                <FocalPointPicker
                  label={__('Image focal point', 'planet4-blocks-backend')}
                  url={image_url}
                  dimensions={{ width: 300, height: 100 }}
                  value={focal_points?.x && focal_points?.y ? focal_points : { x: .5, y: .5 }}
                  onChange={value => onFocalPointsChange(index, value)}
                  key={index}
                />
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
