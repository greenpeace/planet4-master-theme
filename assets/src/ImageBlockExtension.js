import { Button, ButtonGroup, PanelBody, SelectControl } from '@wordpress/components';
import assign from 'lodash.assign';

const { addFilter } = wp.hooks;
const { __ } = wp.i18n;

// Enable spacing control on the following blocks
const targetBlocks = [
  'core/image',
];

const captionStyleOptions = [
  {
  label: __( 'Blue Overlay (default)' ),
  value: 'blue-overlay',
  },
  {
  label: __( 'Medium' ),
  value: 'medium',
  },
];

const captionAlignmentOptions = [
  {
  label: __( 'Left' ),
  value: 'left',
  },
  {
  label: __( 'Center' ),
  value: 'center',
  },
  {
  label: __( 'Right' ),
  value: 'right',
  },
];

export const setupImageBlockExtension = function() {
  addExtraAttributes();
  addExtraControls();
}

const addExtraAttributes = function() {
  const addCaptionStyleAttributes = ( settings, name ) => {
  // Do nothing if it's another block than our defined ones.
  if ( ! targetBlocks.includes( name ) ) {
    return settings;
  }

  // Use Lodash's assign to gracefully handle if attributes are undefined
  settings.attributes = assign( settings.attributes, {
    captionStyle: {
    type: 'string',
    default: captionStyleOptions[ 0 ].value,
    },
    captionAlignment: {
    type: 'string',
    default: captionAlignmentOptions[ 1 ].value,
    }
  } );

  return settings;
  };

  addFilter( 'blocks.registerBlockType', 'planet4-blocks/overrides/image', addCaptionStyleAttributes );
}

const addExtraControls = function() {
  const { createHigherOrderComponent } = wp.compose;
  const { Fragment } = wp.element;
  const { InspectorControls } = wp.editor;

  const withCaptionStyle = createHigherOrderComponent( ( BlockEdit ) => {
    return ( props ) => {
      // Do nothing if it's another block than our defined ones.
      if ( ! targetBlocks.includes( props.name ) ) {
        return (
          <BlockEdit { ...props } />
        );
      }

      const { captionStyle, captionAlignment } = props.attributes;

			props.attributes.className = '';

      if ( captionStyle ) {
      	props.attributes.className += ` caption-style-${ captionStyle }`;
      }

      if ( captionAlignment ) {
      	props.attributes.className += ` caption-alignment-${ captionAlignment }`;
      }

      return (
        <Fragment>
          <BlockEdit { ...props } />
          <InspectorControls>
            <PanelBody
              title={ __( 'Planet4 Image Options' ) }
              initialOpen={ true }
            >
              <SelectControl
                label={ __( 'Caption Style' ) }
                value={ captionStyle }
                options={ captionStyleOptions }
                onChange={ ( selectedCaptionStyle ) => {
                  props.setAttributes( {
                    captionStyle: selectedCaptionStyle,
                  } );
                } }
              />

              <label>Caption alignment</label>
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
										}}
										isPrimary={ captionAlignment == option.value }
										isLarge isDefault>
											{ option.label }
									</Button>;
                })
              }
              </ButtonGroup>
            </PanelBody>
          </InspectorControls>
        </Fragment>
      );
    };
  }, 'withCaptionStyle' );

  addFilter( 'editor.BlockEdit', 'planet4-blocks/overrides/image-controls', withCaptionStyle );
}
