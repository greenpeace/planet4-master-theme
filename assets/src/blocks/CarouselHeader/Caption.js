const {useMemo} = wp.element;
const {RichText} = wp.blockEditor;

const {__} = wp.i18n;

export const Caption = ({slide, index, changeSlideAttribute}) => useMemo(() => (
  <div className="carousel-caption">
    <div className="caption-overlay"></div>
    <div className="container main-header">
      <div className="carousel-captions-wrapper">
        <RichText
          tagName="h2"
          placeholder={__('Enter title', 'planet4-master-theme-backend')}
          value={slide.header}
          onChange={changeSlideAttribute('header', index)}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
        <RichText
          tagName="p"
          placeholder={__('Enter description', 'planet4-master-theme-backend')}
          value={slide.description}
          onChange={changeSlideAttribute('description', index)}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </div>

      <div className="col-xs-12 col-sm-8 col-md-4 action-button">
        <RichText
          tagName="div"
          className="btn btn-primary"
          placeholder={__('Enter CTA text', 'planet4-master-theme-backend')}
          value={slide.link_text}
          onChange={changeSlideAttribute('link_text', index)}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
      </div>
    </div>
  </div>
), [changeSlideAttribute, index, slide]);
