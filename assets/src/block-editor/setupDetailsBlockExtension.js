import {Button, ButtonGroup, PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

const targetBlocks = [
  'core/details',
];

const captionAlignmentOptions = [
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
  if (!targetBlocks.includes(name)) {
    return settings;
  }
  settings.attributes = {
    ...settings.attributes,
    captionAlignment: {
      type: 'string',
      default: captionAlignmentOptions[0].value,
    },
  };
  return settings;
};

const withCaptionStyle = wp.compose.createHigherOrderComponent(BlockEdit => props => {
  if (!targetBlocks.includes(props.name)) {
    return <BlockEdit {...props} />;
  }

  const updateCaptionAlignment = alignment => {
    const className = props.attributes.className || '';
    const classList = className.split(' ').filter(Boolean);

    // Remove any existing caption alignment class
    captionAlignmentOptions.forEach(option => {
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

  const {captionAlignment} = props.attributes;

  return (
    <>
      <BlockEdit {...props} />
      <InspectorControls>
        <PanelBody title={__('Block Styles')} initialOpen={true}>
          <ButtonGroup>
            {
              captionAlignmentOptions.map((option, key) => {
                return <Button
                  key={key}
                  value={option.value}
                  onClick={() => {
                    props.setAttributes({
                      captionAlignment: option.value,
                    });
                    updateCaptionAlignment(option.value);
                  }}
                  variant={captionAlignment === option.value ? 'primary' : 'secondary'}>
                  {option.label}
                </Button>;
              })
            }
          </ButtonGroup>
        </PanelBody>
      </InspectorControls>
    </>
  );
}, 'withCaptionStyle');
