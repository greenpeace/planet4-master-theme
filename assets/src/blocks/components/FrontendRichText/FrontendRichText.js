const RichText = wp.blockEditor ? wp.blockEditor.RichText : null;

export const FrontendRichText = ({editable, ...richTextProps}) => {
  const renderAsRichText = RichText && editable;
  const TagName = richTextProps.tagName;

  return renderAsRichText ?
    <RichText {...richTextProps} /> :
    <TagName
      className={richTextProps.className}
      dangerouslySetInnerHTML={{__html: richTextProps.value}}
    />;
};
