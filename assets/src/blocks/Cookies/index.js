import metadata from './block.json';
import {CookiesEditor} from './edit';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <CookiesEditor {...props} />
    </div>
  ),
  save: () => null,
});
