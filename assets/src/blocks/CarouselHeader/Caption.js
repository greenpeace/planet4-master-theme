const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

export const Caption = ({ slide, index, changeSlideAttribute }) => (
  <div className='carousel-caption'>
    <div className='caption-overlay'></div>
    <div className='container main-header'>
      <div className='row'>
        <div className='col'>
          <div className='carousel-captions-wrapper'>
            <RichText
              tagName='h1'
              placeholder={__('Enter title', 'planet4-blocks-backend')}
              value={slide.header}
              onChange={changeSlideAttribute('header', index)}
              keepPlaceholderOnFocus={true}
              withoutInteractiveFormatting
              characterLimit={32}
              allowedFormats={[]}
              multiline='false'
            />
            <RichText
              tagName='p'
              placeholder={__('Enter description', 'planet4-blocks-backend')}
              value={slide.description}
              onChange={changeSlideAttribute('description', index)}
              keepPlaceholderOnFocus={true}
              withoutInteractiveFormatting
              allowedFormats={[]}
              characterLimit={200}
            />
          </div>

          <div className='col-xs-12 col-sm-8 col-md-4 action-button'>
            <a href={slide.link_url}
              target={slide.link_url_new_tab ? '_blank' : '_self'}
              className='btn btn-primary btn-block'
              data-ga-category='Carousel Header'
              data-ga-action='Call to Action'
              rel='noopener noreferrer'
              data-ga-label={slide.index}
              onClick={e => e.preventDefault()}
            >
              <RichText
                tagName='span'
                className=''
                placeholder={__('Enter CTA text', 'planet4-blocks-backend')}
                value={slide.link_text}
                onChange={changeSlideAttribute('link_text', index)}
                keepPlaceholderOnFocus={true}
                withoutInteractiveFormatting
                allowedFormats={[]}
                characterLimit={40}
              />
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
);
