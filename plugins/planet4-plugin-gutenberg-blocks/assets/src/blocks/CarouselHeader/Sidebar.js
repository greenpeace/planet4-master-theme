import {
  CheckboxControl,
  PanelBody,
  PanelRow,
} from '@wordpress/components';

import { InspectorControls } from '@wordpress/block-editor';
import { SidebarSlide } from './SidebarSlide';

const { __ } = wp.i18n;

export const Sidebar = ({
  carouselAutoplay,
  setAttributes,
  slides,
  currentSlide,
  changeSlideAttribute,
  goToSlide,
}) => (
  <InspectorControls>
    <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
      <CheckboxControl
        label={__('Carousel Autoplay', 'planet4-blocks-backend')}
        help={__('Select to trigger images autoslide', 'planet4-blocks-backend')}
        value={carouselAutoplay}
        checked={carouselAutoplay === true}
        onChange={value => setAttributes({ carousel_autoplay: value })}
      />
    </PanelBody>
    {slides.map((slide, index) => (
      <PanelBody
        key={index}
        title={`${__('Slide', 'planet4-blocks-backend')} ${index + 1}`}
        opened={currentSlide === index}
        onToggle={() => {
          if (currentSlide !== index) {
            goToSlide(index);
          }
        }}
      >
        <PanelRow>
          <SidebarSlide
            {...slide}
            changeSlideAttribute={changeSlideAttribute}
            index={index}
            key={index}
          />
        </PanelRow>
      </PanelBody>
    ))}
  </InspectorControls>
);
