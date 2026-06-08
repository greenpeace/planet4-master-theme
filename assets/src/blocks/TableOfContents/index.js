import {TableOfContentsEditor} from './edit';
import {getStyleLabel} from '../../functions/getStyleLabel';
import {example} from './example';
import metadata from './block.json';
import './style.scss';

const {__} = wp.i18n;
const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  styles: [
    {
      name: 'long',
      label: getStyleLabel(
        __('Long full-width', 'planet4-master-theme-backend'),
        __('Use: on long pages (more than 5 screens) when list items are long (+ 10 words). No max items recommended.', 'planet4-master-theme-backend')
      ),
      isDefault: true,
    },
    {
      name: 'short',
      label: getStyleLabel(
        __('Short full-width', 'planet4-master-theme-backend'),
        __('Use: on long pages (more than 5 screens) when list items are short (up to 5 words). No max items recommended.', 'planet4-master-theme-backend')
      ),
    },
    {
      name: 'sidebar',
      label: getStyleLabel(
        __('Short sidebar', 'planet4-master-theme-backend'),
        __('Use: on long pages (more than 5 screens) when list items are short (up to 10 words). Max items recommended: 9', 'planet4-master-theme-backend')
      ),
    },
  ],
  edit: props => (
    <div {...useBlockProps()}>
      <TableOfContentsEditor {...props} />
    </div>
  ),
  save() {
    return null;
  },
  example,
});
