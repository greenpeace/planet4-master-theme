import {
  CheckboxControl,
  PanelBody,
  PanelRow,
  Panel,
} from '@wordpress/components';

import { InspectorControls } from '@wordpress/block-editor';
import { SidebarSlide } from './SidebarSlide';

const {__} = wp.i18n;

export const Sidebar = ({
  carouselAutoplay,
  setAttributes,
  slides,
  currentSlide,
}) => {
  function onFocalPointsChange(index, value) {
    let slidesCopy = JSON.parse(JSON.stringify(slides));
    if (null !== value) {
      const focalPoints = JSON.parse(JSON.stringify(value));
      slidesCopy[index].focal_points = focalPoints;
    } else {
      slidesCopy[index].focal_points = null;
    }
    setAttributes({slides: slidesCopy});
  }

  return <InspectorControls>
    <CheckboxControl
      label={__('Carousel Autoplay', 'p4ge')}
      help={__('Select to trigger images autoslide', 'p4ge')}
      value={carouselAutoplay}
      checked={carouselAutoplay === true}
      onChange={value => setAttributes({carousel_autoplay: value})}
    />

    {slides.map((slide, index) => {
      return (
        <Panel key={index}>
          <PanelBody title={`${__('Slide', 'planet4-blocks-backend')} ${index + 1}`}
            className={ currentSlide == index ? 'carousel-header-current-slide-panel' : ''}
            initialOpen={ currentSlide == index }
            >
            <PanelRow>
              <SidebarSlide
                {...slide}
                onFocalPointsChange={onFocalPointsChange}
                setAttributes={setAttributes}
                index={index}
                key={index}
                slides={slides}
              />
            </PanelRow>
          </PanelBody>
        </Panel>
        );
      })
    }
  </InspectorControls>;
}
