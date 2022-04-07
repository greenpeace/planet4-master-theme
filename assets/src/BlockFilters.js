const { addFilter } = wp.hooks;

import P4TableEdit from './components/p4_table/edit';
import { ImageBlockEdit } from './components/Image/ImageBlockEdit';

export const addBlockFilters = () => {
  addFileBlockFilter();
  addTableBlockFilter();
  addImageBlockFilter();
};

const addFileBlockFilter = () => {
  const setDownloadButtonToggleDefualtFalse = (settings, name) => {

    if ('core/file' !== name) {
      return settings;
    }

    settings.attributes['showDownloadButton']['default'] = false;

    return settings;
  };

  addFilter('blocks.registerBlockType', 'planet4-blocks/filters/file', setDownloadButtonToggleDefualtFalse);
};

const addTableBlockFilter = () => {

  const updateTableBlockEditMethod = (settings, name) => {

    if ('core/table' !== name) {
      return settings;
    }

    if ( settings.name === 'core/table' ) {
      lodash.assign( settings, {
        edit: P4TableEdit,
      } );
    }

    return settings;
  };

  addFilter('blocks.registerBlockType', 'planet4-blocks/filters/table', updateTableBlockEditMethod);
};

const addImageBlockFilter = () => addFilter('editor.BlockEdit', 'core/image/edit', ImageBlockEdit);
