import {RichText} from '@wordpress/block-editor';
import {debounce} from '@wordpress/compose';

const {__} = wp.i18n;

// WYSIWYG in-place editor

export const SplitTwoColumnsInPlaceEdit = ({attributes, setAttributes}) => {
  const {
    title,
    issue_description,
    issue_link_path,
    issue_link_text,
    issue_image_src,
    issue_image_title,
    focus_issue_image,
    tag_description,
    button_text,
    tag_name,
    tag_link,
    tag_image_src,
    tag_image_title,
    focus_tag_image,
    edited,
    className,
  } = attributes;

  const onTextChange = field_name => debounce(content => {
    setAttributes({
      [field_name]: content,
      edited: {
        ...edited,
        [field_name]: content.length > 0,
      },
    });
  }, 400);

  return (
    <section className={`alignfull split-two-column ${className ?? ''}`}>
      <div className="split-two-column-item item--left">
        {issue_image_src &&
          <div className="split-two-column-item-image">
            <img
              src={issue_image_src}
              alt={issue_image_title}
              title={issue_image_title}
              style={{objectPosition: focus_issue_image}}
            />
          </div>
        }
        <div className="split-two-column-item-content">
          <RichText
            tagName="h2"
            className="split-two-column-item-title"
            placeholder={__('Enter Title', 'planet4-blocks-backend')}
            value={title}
            onChange={onTextChange('title')}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
          <RichText
            tagName="p"
            className="split-two-column-item-subtitle"
            placeholder={__('Enter Description', 'planet4-blocks-backend')}
            value={issue_description}
            onChange={onTextChange('issue_description')}
            allowedFormats={['core/bold', 'core/italic']}
          />
          {issue_link_path &&
            <RichText
              tagName="a"
              className="split-two-column-item-link"
              placeholder={__('Enter Link Text', 'planet4-blocks-backend')}
              value={issue_link_text}
              onChange={onTextChange('issue_link_text')}
              withoutInteractiveFormatting
              allowedFormats={[]}
            />
          }
        </div>
      </div>
      <div className="split-two-column-item item--right">
        {tag_image_src &&
          <div className="split-two-column-item-image">
            <img
              src={tag_image_src}
              alt={tag_image_title}
              title={tag_image_title}
              style={{objectPosition: focus_tag_image}}
            />
          </div>
        }
        <div className="split-two-column-item-content">
          {tag_name &&
            <a className="split-two-column-item-tag" href={tag_link}>
              #{tag_name}
            </a>
          }
          <RichText
            tagName="p"
            className="split-two-column-item-subtitle"
            placeholder={__('Enter Description', 'planet4-blocks-backend')}
            value={tag_description}
            onChange={onTextChange('tag_description')}
            allowedFormats={['core/bold', 'core/italic']}
          />
          <RichText
            tagName="a"
            className="btn btn-primary btn-block split-two-column-item-button"
            placeholder={__('Enter button text', 'planet4-blocks-backend')}
            value={button_text}
            onChange={onTextChange('button_text')}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
        </div>
      </div>
    </section>
  );
};
