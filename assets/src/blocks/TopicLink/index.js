import metadata from './block.json';
import edit from './edit';
import save from './save';
import './style.css';

const {registerBlockType} = wp.blocks;

registerBlockType(metadata, {
  category: 'planet4-blocks',
  edit,
  save,
  deprecated: [{
    save() {
      return null;
    },
  }],
});
