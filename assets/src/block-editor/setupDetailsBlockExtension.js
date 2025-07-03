import {ToggleControl, PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

// Enable spacing control on the following blocks
const targetBlocks = [
  'core/details',
];

export const setupDetailsBlockExtension = () => {
  addFilter(
    'blocks.registerBlockType',
    'planet4-blocks/overrides/details',
    addCaptionStyleAttributes
  );
  addFilter(
    'editor.BlockEdit',
    'planet4-blocks/overrides/details-controls',
    withCaptionStyle
  );
};

const addCaptionStyleAttributes = (settings, name) => {
  // Do nothing if it's another block than our defined ones.
  if (!targetBlocks.includes(name)) {
    return settings;
  }
  settings.attributes = {
    ...settings.attributes,
    captionAlignment: {
      type: 'string',
      default: '',
    },
  };
  return settings;
};

const withCaptionStyle = wp.compose.createHigherOrderComponent(BlockEdit => props => {
  if (!targetBlocks.includes(props.name)) {
    return <BlockEdit {...props} />;
  }

  const updateCaptionAlignment = enabled => {
    const className = props.attributes.className || '';
    const classList = className.split(' ').filter(Boolean);
    const hasClass = classList.includes('p4-details');

    if (enabled && !hasClass) {
      classList.push('p4-details');
    } else if (!enabled && hasClass) {
      // Remove the class
      const index = classList.indexOf('p4-details');
      classList.splice(index, 1);
    }

    props.setAttributes({className: classList.join(' ')});
  };

  return (
    <>
      <BlockEdit {...props} />
      <InspectorControls>
        <PanelBody title={__('Styles')} initialOpen={true}>
          {/* eslint-disable-next-line jsx-a11y/label-has-for, jsx-a11y/label-has-associated-control */}
          <label className="mb-4">
              Apply the P4 Styles to the Details block.
          </label>
          <ToggleControl
            label={__('Use P4 Styles')}
            checked={(props.attributes.className || '').includes('p4-details')}
            onChange={isChecked => {
              updateCaptionAlignment(isChecked);
            }}
          />
        </PanelBody>
      </InspectorControls>
    </>
  );
}, 'withCaptionStyle');
