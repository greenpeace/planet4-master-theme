const { addFilter } = wp.hooks;

import P4ButtonEdit from './components/p4_button/edit';
import P4TableEdit from './components/p4_table/edit';
import { ImageBlockEdit } from './components/Image/ImageBlockEdit';

export const addBlockFilters = () => {
  addFileBlockFilter();
  addButtonBlockFilter();
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

const addButtonBlockFilter = () => {

  const updateButtonBlockEditMethod = (settings, name) => {

    if ('core/button' !== name) {
      return settings;
    }

    if ( settings.name === 'core/button' ) {
      // Override the Native button block styles and use p4 button styles.
      const p4ButtonStyle = [
        {
          name: 'secondary',
          label: 'Secondary',
          isDefault: true
        },
        {
          name: 'cta',
          label: 'CTA'
        },
        {
          name: 'donate',
          label: 'Donate'
        }
      ];

      const newAttributes = settings.attributes;
      // Set button block default style.
      newAttributes.className = Object.assign({ default: "is-style-secondary" }, newAttributes.className);
      // Temporary fix for Gutenberg's bug
      // https://github.com/WordPress/gutenberg/pull/33116
      const newExample = settings.example;
      newExample.attributes.backgroundColor = '';

      lodash.assign( settings, {
        example: newExample,
        attributes: newAttributes,
        styles: p4ButtonStyle,
      } );
    }

    return settings;
  };

  addFilter('blocks.registerBlockType', 'planet4-blocks/filters/button', updateButtonBlockEditMethod);
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
