import { Fragment } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';
import { debounce } from 'lodash';

const { __ } = wp.i18n;

/**
 * WYSIWYG in-place editor 
 */
export const SplittwocolumnsInPlaceEdit = ({attributes, charLimit, setAttributes}) => {
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
    focus_tag_image
  } = attributes;

  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value
  });
  const debounceToAttribute = attributeName => debounce(
    toAttribute(attributeName), 400
  );

  return (
    <section className="block-wide split-two-column">
      <div className="split-two-column-item item--left">
        {issue_image_src &&
          <div className="split-two-column-item-image">
            <img
              src={issue_image_src}
              alt={issue_image_title || ''}
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
            onChange={debounceToAttribute('title')}
            characterLimit={charLimit.title}
            multiline="false"
            withoutInteractiveFormatting
            allowedFormats={[]}
            />
          <RichText
            tagName="p"
            className="split-two-column-item-subtitle"
            placeholder={__('Enter Description', 'planet4-blocks-backend')}
            value={issue_description}
            onChange={debounceToAttribute('issue_description')}
            characterLimit={charLimit.description}
            multiline="false"
            allowedFormats={['core/bold', 'core/italic']}
            />  
          {issue_link_path &&
            <RichText
              tagName="a"
              className="split-two-column-item-link"
              placeholder={__('Enter Link Text', 'planet4-blocks-backend')}
              value={issue_link_text}
              onChange={debounceToAttribute('issue_link_text')}
              characterLimit={100}
              multiline="false"
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
              alt={tag_image_title || ''}
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
            onChange={debounceToAttribute('tag_description')}
            characterLimit={charLimit.description}
            multiline="false"
            allowedFormats={['core/bold', 'core/italic']}
            />
          <RichText
            tagName="a"
            className="btn btn-small btn-primary btn-block split-two-column-item-button"
            placeholder={__('Enter button text', 'planet4-blocks-backend')}
            value={button_text}
            onChange={debounceToAttribute('button_text')}
            characterLimit={charLimit.title}
            multiline="false"
            withoutInteractiveFormatting
            allowedFormats={[]}
            />
        </div>
      </div>
    </section>
  )
}
 