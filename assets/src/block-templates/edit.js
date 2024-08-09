const {useBlockProps, InnerBlocks} = wp.blockEditor;

export default function(template, templateLock = false) {
  return props => {
    return (
      <div {...useBlockProps()}>
        {
          wp.element.createElement(InnerBlocks, {
            template: template(props.attributes ?? {}),
            templateLock,
          })
        }
      </div>
    );
  };
}
