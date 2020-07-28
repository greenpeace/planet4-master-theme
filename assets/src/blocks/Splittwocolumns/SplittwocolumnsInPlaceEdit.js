import { Fragment } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';
import { debounce } from 'lodash';

const { __ } = wp.i18n;

/**
 * WYSIWYG in-place editor 
 */
export const SplittwocolumnsInPlaceEdit = ({attributes, charLimit, setAttributes}) => {
  const toAttribute = attributeName => value => setAttributes({
    [attributeName]: value
  });
  const debounceToAttribute = attributeName => debounce(
    toAttribute(attributeName), 400
  );

  return (
    <Fragment>
    <section className="block-wide split-two-column">
      <div className="split-two-column-item item--left">
        {attributes.issue_image_src &&
          <div className="split-two-column-item-image">
            <img
              src={attributes.issue_image_src}
              alt={attributes.issue_image_title || ''}
              style={{objectPosition: attributes.focus_issue_image}}
            />
          </div>
        }
        <div className="split-two-column-item-content">
          <RichText
            tagName="h2"
            className="split-two-column-item-title"
            placeholder={__('Enter Title', 'planet4-blocks-backend')}
            value={attributes.title}
            onChange={debounceToAttribute('title')}
            characterLimit={charLimit.title}
            multiline={false}
            withoutInteractiveFormatting
            allowedFormats={[]}
            />
          <RichText
            tagName="p"
            className="split-two-column-item-subtitle"
            placeholder={__('Enter Description', 'planet4-blocks-backend')}
            value={attributes.issue_description}
            onChange={debounceToAttribute('issue_description')}
            characterLimit={charLimit.description}
            multiline={false}
            allowedFormats={['core/bold', 'core/italic']}
            />  
          {attributes.issue_link_path &&
            <RichText
              tagName="a"
              className="split-two-column-item-link"
              placeholder={__('Enter Link Text', 'planet4-blocks-backend')}
              value={attributes.issue_link_text}
              onChange={debounceToAttribute('issue_link_text')}
              characterLimit={100}
              multiline={false}
              withoutInteractiveFormatting
              allowedFormats={[]}
              />
          }
        </div>
      </div>
      <div className="split-two-column-item item--right">
        {attributes.tag_image_src &&
          <div className="split-two-column-item-image">
            <img
              src={attributes.tag_image_src}
              alt={attributes.tag_image_title || ''}
              style={{objectPosition: attributes.focus_tag_image}}
            />
          </div>
        }
        <div className="split-two-column-item-content">
          {attributes.tag_name &&
            <a className="split-two-column-item-tag" href={attributes.tag_link}>
              #{attributes.tag_name}
            </a>
          }
          <RichText
            tagName="p"
            className="split-two-column-item-subtitle"
            placeholder={__('Enter Description', 'planet4-blocks-backend')}
            value={attributes.tag_description}
            onChange={debounceToAttribute('tag_description')}
            characterLimit={charLimit.description}
            multiline={false}
            allowedFormats={['core/bold', 'core/italic']}
            />
          <RichText
            tagName="a"
            className="btn btn-small btn-primary btn-block split-two-column-item-button"
            placeholder={__('Enter button text', 'planet4-blocks-backend')}
            value={attributes.button_text}
            onChange={debounceToAttribute('button_text')}
            characterLimit={charLimit.title}
            multiline={false}
            withoutInteractiveFormatting
            allowedFormats={[]}
            />
        </div>
      </div>
    </section>
    </Fragment>
  )
}
 