const {__} = wp.i18n;

export const ImagePlaceholder = () => <div className="carousel-header-image-placeholder">
  <svg width="80" height="80" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 512 376">
    <path d="M0,0v376h512V0H0z M480,344H32V32h448V344z"/>
    <circle cx="409.1" cy="102.9" r="40.9"/>
    <polygon
      points="480,344 32,344 118.3,179.8 140,191.1 189,113.8 289,226.9 297.9,217.6 315,239.9 341,193.5 393.9,264.7 409,248.8"/>
  </svg>
  <p>
    {__('No image selected.', 'p4ge')}
  </p>
</div>
