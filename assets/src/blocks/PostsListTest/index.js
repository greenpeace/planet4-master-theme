import metadata from './block.json';
import template from './template';

const {registerBlockType} = wp.blocks;
const {useBlockProps, InnerBlocks} = wp.blockEditor;

export const registerPostsListTest = () => registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      {wp.element.createElement(InnerBlocks, {template: template(props.attributes ?? {})})}
    </div>
  ),
  save: () => wp.element.createElement(wp.blockEditor.InnerBlocks.Content, {}),
});
