import {  createPortal } from 'react-dom';
import { Fragment, useEffect } from 'react';
import { RadioControl, RangeControl } from '@wordpress/components';
import { useLocalStorage } from './useLocalStorage';

const screenOptions = [
  { dims: [360, 640], label: 'Phone' },
  { dims: [360, 780], label: 'Apple iPhone 12 mini' },
  { dims: [390, 844], label: 'Apple iPhone 12 Pro', },
  { dims: [768, 1024], label: 'Tablet Portrait' },
  { dims: [1024, 768], label: 'Tablet Landscape' },
  { dims: [1366, 768], label: 'Laptop Small' },
  { dims: [1440, 900], label: 'Laptop Medium' },
  { dims: [1536, 864], label: 'Laptop Wide' },
  { dims: [1920, 1080], label: 'Full HD' },
  { dims: [2560, 1080], label: 'Ultrawide HD' },
  { dims: [2560, 1440], label: 'UHD' },
  { dims: [3440, 1440], label: 'Ultrawide UHD' },
  { dims: [3840, 2160], label: '4K' },
].map(({ dims, label }) => ({
  label: `${label} (${dims.join(' x ')})`,
  value: dims.join(),
}));

const wrapperMargin = 28;

export const ResizableFrame = props => {
  const {
    src,
  } = props;

  const [
    width,
    setWidth,
  ] = useLocalStorage('responsive-width', 360);
  const [
    height,
    setHeight,
  ] = useLocalStorage('responsive-height', 640);
  const [
    scale,
    setScale,
  ] = useLocalStorage('responsive-scale', 1);

  useEffect(() => {
    const orig = document.body.style.maxHeight;
    const scrollPosition = window.pageYOffset;
    document.body.style.top = `-${ scrollPosition }px`;
    document.body.style.postition = 'sticky';
    document.body.style.maxHeight = '100vh';

    return () => {
      document.body.style.maxHeight = orig;
      document.body.style.position = 'static';
      document.body.style.top = 0;
      window.scrollTo(0, scrollPosition);
      window.scrollTo({
        top: scrollPosition,
        behavior: 'auto'
      });

    }
  }, [])

  return createPortal(<Fragment>
    <div
      className="responsive-overlay"
      style={ {
        zIndex: 999,
        background: 'grey',
        opacity: 0.99,
        position: 'fixed',
        top: 0,
        right: 0,
        bottom: 0,
        left: 0,
      } }
    />
    <div
      style={ {
        position: 'fixed',
        top: '8px',
        left: '400px',
        zIndex: 1000,
      } }
    >
      <RangeControl
        value={scale}
        onChange={ value => setScale(value) }
        min={.2}
        max={1}
        step={.02}
        initialPosition={scale}
      />
      <span>Dimensions: <input
        type="number" onChange={ event => setWidth(parseInt(event.target.value)) } value={ width }
      /> x <input
        type="number" onChange={ event => setHeight(parseInt(event.target.value)) } value={ height }
      /></span>
    </div>

    <div style={{
      zIndex: 1000,
      position: 'fixed',
      top: '20px',
      right: '100px',
    }}>
      <RadioControl
        options={ screenOptions }
        selected={ [width, height].join() }
        onChange={ value => {
          const [newWidth, newHeight] = value.split(',');
          setWidth(parseInt(newWidth));
          setHeight(parseInt(newHeight));
        } }
      />
    </div>

    <div
      className='responsive-frame-container'
      onMouseMove={ (event) => {
        if (event.buttons !== 1 || event.currentTarget.className !== 'responsive-frame-container') {
          return;
        }
        const newHeight = parseInt(event.currentTarget.style.height.replace('px', ''));
        const newWidth = parseInt(event.currentTarget.style.width.replace('px', ''));
        setHeight(newHeight - wrapperMargin);
        setWidth(newWidth - wrapperMargin);
      } }
      style={ {
        transform: `scale(${scale})`,
        position: 'fixed',
        top: '100px',
        left: '400px',
        zIndex: 1000,
        resize: 'both',
        overflow: 'scroll',
        minWidth: '200px',
        width: `${ wrapperMargin + parseInt(width) }px`,
        minHeight: '200px',
        height: `${ wrapperMargin + parseInt(height) }px`,
        padding: '0',
        boxSizing: 'border-box',
      } }
    >
      <iframe
        className='responsive-frame'
        { ...{ src, width: parseInt(width) + 12, height: parseInt(height) + 12 } }
      />
    </div>
  </Fragment>, document.body);
};
