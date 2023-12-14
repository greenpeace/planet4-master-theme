import {ToolbarButton, RadioControl, PanelBody} from '@wordpress/components';
import {InspectorControls, BlockControls} from '@wordpress/block-editor';
import {useCallback, useEffect, useMemo} from '@wordpress/element';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

const targetBlocks = ['core/query'];

export const setupQueryBlockExtension = function() {
  const {createHigherOrderComponent} = wp.compose;

  addFilter(
    'editor.BlockEdit',
    'planet4-blocks/overrides/query-controls',
    createHigherOrderComponent(
      BlockEdit => props => {
        if (!targetBlocks.includes(props.name)) {
          return (
            <BlockEdit {...props} />
          );
        }

        const {attributes, setAttributes} = props;

        const isSelectedBlock = namespace => {
          const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();
          return (selectedBlock && selectedBlock.attributes.namespace === namespace);
        };

        const onChangeHandler = useCallback(view => {
          setAttributes({
            displayLayout: {
              type: view,
              columns: view === 'carousel' ? 6 : 3,
            },
            query: {
              ...attributes.query,
              perPage: view === 'carousel' ? 6 : 3,
            },
          });
        }, []);

        useEffect(() => {
          const pattern = 'is-custom-layout-';
          const {className, displayLayout} = attributes;

          if (typeof className !== 'undefined') {
            setAttributes({
              className: (className.includes(pattern)) ?
                className.replace(/\is-custom-layout-.*/, `${pattern}${displayLayout.type}`) :
                `${className} ${pattern}${displayLayout.type}`,
            });
          }
        }, [
          attributes,
          setAttributes,
        ]);

        useEffect(() => {
          // Reset in case of retrieving wrong values
          if (attributes.displayLayout.type === 'flex' && attributes.displayLayout.columns === 6) {
            setAttributes({
              displayLayout: {
                ...attributes.displayLayout,
                columns: 3,
              },
              query: {
                ...attributes.query,
                perPage: 3,
              },
            });
          }
        }, [attributes.displayLayout.type]);

        useEffect(() => {
          if (props.isSelected && isSelectedBlock('planet4-blocks/actions-list')) {
            const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();

            const timeout = setTimeout(() => {
              const carouselBtn = document.querySelector('#carousel-toolbar-button');
              let viewsGroup;

              // Find all toolbar groups only when the block is selected
              for (const group of document.querySelectorAll('.components-toolbar-group')) {
                // Find the views group from toolbar
                if (group.innerHTML.includes('List view') || group.innerHTML.includes('Grid view')) {
                  viewsGroup = group;

                  // Remove the list view for Actions List block
                  if (selectedBlock.attributes.namespace && selectedBlock.attributes.namespace.includes('planet4-blocks/actions-list')) {
                    for (const children of group.children) {
                      if (children.ariaLabel.includes('List view')) {
                        group.removeChild(children);
                      }
                    }
                  }
                }

                // Move the carousel button to the views group if exists
                if (carouselBtn && group.contains(carouselBtn) && viewsGroup) {
                  viewsGroup.append(group);
                }
              }
            }, 50);

            return () => {
              clearTimeout(timeout);
            };
          }
        }, [props.isSelected]);

        return useMemo(() => (
          <>
            {isSelectedBlock('planet4-blocks/actions-list') && (
              <>
                <InspectorControls>
                  <PanelBody
                    title={__('Layout view', 'planet4-blocks-backend')}
                    initialOpen={true}
                  >
                    <RadioControl
                      label={__('Display as', 'planet4-blocks-backend')}
                      help={__('The layout of the action|post list', 'planet4-blocks-backend')}
                      selected={attributes.displayLayout.type}
                      options={[
                        {label: 'Grid', value: 'flex'},
                        {label: 'Carousel', value: 'carousel'},
                      ]}
                      onChange={value => {
                        onChangeHandler(value);
                      }}
                    />
                  </PanelBody>
                </InspectorControls>
                <BlockControls group="other">
                  <ToolbarButton
                    id="carousel-toolbar-button"
                    icon="slides"
                    label={__('Carousel view', 'core-block-custom-attributes')}
                    isActive={attributes.displayLayout.type === 'carousel'}
                    onClick={() => {
                      onChangeHandler('carousel');
                    }}
                  />
                </BlockControls>
              </>
            )}
            <BlockEdit {...props} />
          </>
        ), [attributes, wp.data.select('core/block-editor').getSelectedBlock()]);
      }
    )
  );
};
