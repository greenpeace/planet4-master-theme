import {Button, ButtonGroup, PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

/**
 * List of block names this extension applies to.
 * @type {string[]}
 */
const targetBlocks = [
  'core/details',
];

/**
 * Style variant options for the details block.
 * @type {{label: string, value: string}[]}
 */
const styleVariants = [
  {
    label: __('Default'),
    value: 'default',
  },
  {
    label: __('Dark'),
    value: 'dark',
  },
  {
    label: __('Light'),
    value: 'light',
  },
  {
    label: __('Outline'),
    value: 'outline',
  },
];

/**
 * Initializes the style variant extension for the core/details block.
 */
export const setupDetailsBlockExtension = () => {
  addFilter(
    'blocks.registerBlockType',
    'planet4-blocks/overrides/details',
    addStyleVariantsAttributes
  );
  addFilter(
    'editor.BlockEdit',
    'planet4-blocks/overrides/details-controls',
    addStyleVariantsControls
  );
};

/**
 * Adds the `customStyle` attribute to the target block.
 *
 * @param {Object} settings - The block settings.
 * @param {string} name     - The block name.
 * @return {Object} Modified block settings.
 */
const addStyleVariantsAttributes = (settings, name) => {
  if (!targetBlocks.includes(name)) {
    return settings;
  }
  settings.attributes = {
    ...settings.attributes,
    customStyle: {
      type: 'string',
      default: styleVariants[0].value,
    },
  };
  return settings;
};

/**
 * Adds inspector controls and class name updates for the selected style variant.
 *
 * @param {Function} BlockEdit - The original block edit component.
 * @return {Function} Enhanced block edit component.
 */
const addStyleVariantsControls = wp.compose.createHigherOrderComponent(BlockEdit => props => {
  if (!targetBlocks.includes(props.name)) {
    return <BlockEdit {...props} />;
  }

  /**
   * Updates the block's className based on the selected style variant.
   *
   * @param {string} alignment - The selected style variant value.
   */
  const updateStyleVariants = alignment => {
    const className = props.attributes.className || '';
    const classList = className.split(' ').filter(Boolean);

    // Remove any existing caption alignment class
    styleVariants.forEach(option => {
      const existingClass = `p4-details--${option.value}`;
      const index = classList.indexOf(existingClass);
      if (index !== -1) {
        classList.splice(index, 1);
      }
    });

    // Add new class only if it's not "default"
    if (alignment !== 'default') {
      classList.push(`p4-details--${alignment}`);
    }

    props.setAttributes({className: classList.join(' ')});
  };

  const {customStyle} = props.attributes;

  return (
    <>
      <BlockEdit {...props} />
      <InspectorControls>
        <PanelBody title={__('Block Styles')} initialOpen={true}>
          <ButtonGroup>
            {
              styleVariants.map((option, key) => {
                return <Button
                  key={key}
                  value={option.value}
                  onClick={() => {
                    props.setAttributes({
                      customStyle: option.value,
                    });
                    updateStyleVariants(option.value);
                  }}
                  variant={customStyle === option.value ? 'primary' : 'secondary'}>
                  {option.label}
                </Button>;
              })
            }
          </ButtonGroup>
        </PanelBody>
      </InspectorControls>
    </>
  );
}, 'addStyleVariantsControls');
