import metadata from './block.json';
import {TimelineEditor} from './edit';
import {save} from './save';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <TimelineEditor {...props} />
    </div>
  ),
  save,
});
