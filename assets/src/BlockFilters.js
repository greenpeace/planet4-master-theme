const { addFilter } = wp.hooks;

import P4ButtonEdit from './components/p4_button/edit';

export const addBlockFilters = () => {
  addFileBlockFilter();
  addButtonBlockFilter();
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

const addButtonBlockFilter = () => {

  const updateButtonBlockEditMethod = (settings, name) => {

    if ('core/button' !== name) {
      return settings;
    }

    if ( settings.name === 'core/button' ) {
      lodash.assign( settings, {
        edit: P4ButtonEdit,
      } );
    }

    return settings;
  };

  addFilter('blocks.registerBlockType', 'planet4-blocks/filters/button', updateButtonBlockEditMethod);
};
