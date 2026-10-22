import metadata from './block.json';
import {SecondaryNavigationEditor} from './edit';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <SecondaryNavigationEditor {...props} />
    </div>
  ),
  save: () => null,
});
