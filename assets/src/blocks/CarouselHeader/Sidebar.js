import {
  CheckboxControl,
  PanelBody,
  PanelRow,
} from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import { InspectorControls } from '@wordpress/block-editor';
import { SidebarSlide } from './SidebarSlide';
import { useState, useRef } from 'react';

const { __ } = wp.i18n;

const reOrderSlides = (slides, oldIndex, newIndex) => {
  const movingSlide = slides[oldIndex];
  return slides.reduce((orderedSlides, slide, index) => {
    if (index === newIndex) {
      orderedSlides.push(movingSlide);
    }
    if (index !== oldIndex) {
      orderedSlides.push(slide);
    }
    if (index === slides.length -1 && newIndex > index) {
      orderedSlides.push(movingSlide);
    }
    return orderedSlides;
  }, []);
}

export const Sidebar = ({
  carouselAutoplay,
  setAttributes,
  slides,
  currentSlide,
  changeSlideAttribute,
  goToSlide,
}) => {
  const [dragTarget, setDragTarget] = useState(null);
  const [draggedSlide, setDraggedSlide] = useState(null);
  const timeoutRef = useRef(null);

  return <InspectorControls>
    <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
      <CheckboxControl
        label={__('Carousel Autoplay', 'planet4-blocks-backend')}
        help={__('Select to trigger images autoslide', 'planet4-blocks-backend')}
        value={carouselAutoplay}
        checked={carouselAutoplay === true}
        onChange={value => setAttributes({ carousel_autoplay: value })}
      />
    </PanelBody>
    { slides.map((slide, index) => <div
      style={ {
        // When you start dragging an element, what is shown while dragging is a bitmap snapshot of what the element
        // looked like the moment the drag started. The .01s delay should be plenty to ensure that snapshot is taken
        // before we hide the element.
        transform: index === draggedSlide ? 'scaleY(0)' : 'none',
        transitionDelay: '.01s',
        transitionProperty: 'transform',
      } }
      className={ index === dragTarget ? 'carousel-sidebar-insert-before' : 'carousel-sidebar-slide' }
      key={ `${ slide.image_srcset }${ index }` }
      draggable
      onDragOver={ () => {
        timeoutRef.current && window.clearTimeout(timeoutRef.current);
        setDragTarget(index);
      } }
      onDragLeave={ () => {
        // The event fires also when going over descendant elements, then immediately fires on the parent again.
        // Without this timeout it continuously set and unset the drag target.
        timeoutRef.current = window.setTimeout(() => {
          setDragTarget(null);
        }, 100);
      } }
      onDragStart={ () => {
        setDraggedSlide(index);
      } }
      onDragEnd={ () => {
        setDraggedSlide(null);
        if (dragTarget === null) {
          return;
        }
        setAttributes({ slides: reOrderSlides(slides, index, dragTarget) });
        setDragTarget(null);
      } }
    >
      <PanelBody
        key={ index }
        title={
          <Fragment>
            <img
              draggable={ false }
              srcSet={ slide.image_srcset }
              height={ 50 }
              alt={ slide.image_alt }
              style={ { marginRight: '8px', maxHeight: '50px' } }
            />
            <span>{ slide.header || <i>{__('No title', 'planet4-blocks-backend')}</i> }</span>
          </Fragment>
        }
        initialOpen={ false }
        onToggle={ (isOpened) => {
          if (isOpened) {
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
    </div>) }
    { draggedSlide !== null && draggedSlide !== slides.length - 1 && <div
      className={slides.length === dragTarget ? 'carousel-sidebar-insert-before' : ''}
      onDragOver={ () => setDragTarget(slides.length) }
      onDragLeave={ () => setDragTarget(null)}
      style={{height: '80px'}}/> }
  </InspectorControls>;
};
