const { addFilter } = wp.hooks;

import { ImageBlockEdit } from './components/Image/ImageBlockEdit';

export const addBlockFilters = () => {
  addFileBlockFilter();
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

const addImageBlockFilter = () => addFilter('editor.BlockEdit', 'core/image/edit', ImageBlockEdit);
