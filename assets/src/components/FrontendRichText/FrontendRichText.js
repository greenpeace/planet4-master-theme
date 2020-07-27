import { Fragment } from '@wordpress/element';

const RichText = wp.blockEditor ? wp.blockEditor.RichText : null;

export const FrontendRichText = ({ editable, ...richTextProps }) => {
  const renderAsRichText = RichText && editable;
  const TagName = richTextProps.tagName;

  return <Fragment>
    {renderAsRichText ?
      <RichText { ...richTextProps } /> :
      <TagName className={ richTextProps.className }>
        { richTextProps.value }
      </TagName>
    }
  </Fragment>
}
