import {useBlockProps} from '@wordpress/block-editor';

export default function(template, templateLock = false) {
  return props => {
    return (
      <div {...useBlockProps()}>
        {
          wp.element.createElement(wp.blockEditor.InnerBlocks, {
            template: template(props.attributes ?? {}),
            templateLock,
          })
        }
      </div>
    );
  };
}
