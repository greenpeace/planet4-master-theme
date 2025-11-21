import metadata from './block.json';
import deprecated from './deprecated';
import edit from './edit';
import save from './save';
import './style.css';

const {registerBlockType} = wp.blocks;

registerBlockType(metadata, {
  title: metadata.title,
  icon: metadata.icon,
  category: 'planet4-blocks',
  supports: {
    html: false, // Disable "Edit as HTML" block option.
    multiple: false, // Use the block just once per post.
  },
  edit,
  save,
  deprecated,
});
