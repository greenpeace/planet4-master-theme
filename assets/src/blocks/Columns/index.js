import metadata from './block.json';
import {ColumnsEditor} from './ColumnsEditor';
import {getStyleLabel} from '../../functions/getStyleLabel';
import {LAYOUT_NO_IMAGE, LAYOUT_IMAGES, LAYOUT_ICONS, LAYOUT_TASKS} from './ColumnConstants';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;
const {__} = wp.i18n;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <ColumnsEditor {...props} />
    </div>
  ),
  save: () => null,
  styles: [
    {
      name: LAYOUT_NO_IMAGE,
      label: getStyleLabel(
        'No Image',
        __('Optional headers, description text and buttons in a column display.', 'planet4-master-theme-backend')
      ),
      isDefault: true,
    },
    {
      name: LAYOUT_TASKS,
      label: getStyleLabel(
        'Tasks',
        __(
          'Used on Take Action pages, this display has ordered tasks, and call to action buttons.',
          'planet4-master-theme-backend'
        )
      ),
    },
    {
      name: LAYOUT_ICONS,
      label: getStyleLabel(
        'Icons',
        __(
          'For more static content, this display has an icon, header, description and text link.',
          'planet4-master-theme-backend'
        )
      ),
    },
    {
      name: LAYOUT_IMAGES,
      label: getStyleLabel(
        'Images',
        __(
          'For more static content, this display has an image, header, description and text link.',
          'planet4-master-theme-backend'
        )
      ),
    },
  ],
});
