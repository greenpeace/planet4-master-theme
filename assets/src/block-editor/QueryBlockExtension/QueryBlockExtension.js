import {RadioControl, PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {useCallback, useEffect, useMemo, useState} from '@wordpress/element';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

const targetBlocks = ['core/post-template'];

const layoutTypes = [
  {label: 'List', value: 'default', columnCount: 3},
  {label: 'Grid', value: 'grid', columnCount: 3},
  {label: 'Carousel', value: 'flex', columnCount: 6},
];

export const setupQueryBlockExtension = function() {
  const {createHigherOrderComponent} = wp.compose;

  addFilter(
    'editor.BlockEdit',
    'planet4-blocks/overrides/query-loop-layout',
    createHigherOrderComponent(
      BlockEdit => props => {
        if (props.attributes.namespace === 'planet4-blocks/actions-list' || targetBlocks.includes(props.name)) {
          const [isPostTemplate, setPostTemplate] = useState();
          const [selectedBlock, setSelectedBlock] = useState();
          const {attributes, setAttributes} = props;

          const onChangeHandler = useCallback(type => {
            const {className, query} = attributes;

            const pattern = 'is-custom-layout-';
            const layoutType = layoutTypes.find(t => t.value === type);

            if (layoutType) {
              setAttributes({
                layout: {
                  type: layoutType.value,
                  columnCount: layoutType.columnCount,
                },
                query: {
                  ...query,
                  perPage: layoutType.columnCount,
                },
                className: ((className.includes(pattern)) ?
                  className.replace(/\is-custom-layout-.*/, `${pattern}${layoutType.label}`) :
                  `${className} ${pattern}${layoutType.label}`).toLowerCase(),
              });
            }
          }, [attributes]);

          useEffect(() => {
            if (isPostTemplate) {
              const timeout = setTimeout(() => {
                // Find all toolbar groups only when the block is selected
                for (const group of document.querySelectorAll('.components-toolbar-group')) {
                  // Find the views group from toolbar
                  if (group.innerHTML.includes('List view') || group.innerHTML.includes('Grid view')) {
                    group.style.display = 'none';
                  }
                }
              }, 10);

              return () => {
                clearTimeout(timeout);
              };
            }
          }, [isPostTemplate]);

          useEffect(() => {
            if (selectedBlock && selectedBlock.name === 'core/post-template') {
              const parentBlock = wp.data.select('core/block-editor')
                .getBlock(wp.data.select('core/block-editor')
                  .getBlockParents(selectedBlock.clientId)[0]);

              if (parentBlock && parentBlock.attributes.namespace === 'planet4-blocks/actions-list') {
                setPostTemplate(selectedBlock);
              }
            }
          }, [selectedBlock]);

          useEffect(() => {
            // Reset every time a new block is selected
            setPostTemplate(null);
            setSelectedBlock(wp.data.select('core/block-editor').getSelectedBlock());
          }, [
            wp.data.select('core/block-editor').getSelectedBlock(),
          ]);

          return useMemo(() => (
            <>
              <InspectorControls>
                <PanelBody
                  title={__('Layout view', 'planet4-blocks-backend')}
                  initialOpen={true}
                >
                  {attributes.layout && (
                    <>
                      <RadioControl
                        label={__('Display as', 'planet4-blocks-backend')}
                        help={__('The layout of the action|post list', 'planet4-blocks-backend')}
                        selected={attributes.layout.type}
                        options={layoutTypes}
                        onChange={type => onChangeHandler(type)}
                      />
                    </>
                  )}
                </PanelBody>
              </InspectorControls>
              <BlockEdit {...props} />
            </>
          ), [
            attributes,
            isPostTemplate,
            selectedBlock,
            wp.data.select('core/block-editor').getSelectedBlock(),
          ]);
        }

        return (
          <BlockEdit {...props} />
        );
      }
    )
  );
};
