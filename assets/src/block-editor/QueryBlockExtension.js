import {ToolbarGroup, ToolbarButton, RadioControl, PanelBody} from '@wordpress/components';
import {InspectorControls, BlockControls} from '@wordpress/block-editor';
import {useCallback, useEffect, useMemo} from '@wordpress/element';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

const targetBlocks = [
  'core/query',
];

const layoutOptions = [
  {label: 'List', value: 'list'},
  {label: 'Grid', value: 'flex'},
  {label: 'Carousel', value: 'carousel'},
];

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

        const onChangeHandler = useCallback(view => {
          let layoutType = 'list';
          let layoutColumns = 3;
          let queryPerPage = 3;

          switch (view) {
          case 'flex':
            layoutType = 'flex';
            break;
          case 'carousel':
            layoutType = 'carousel';
            layoutColumns = 6;
            queryPerPage = 6;
            break;
          }

          setAttributes({
            displayLayout: {
              type: layoutType,
              columns: layoutColumns,
            },
            query: {
              ...attributes.query,
              perPage: queryPerPage,
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
          if (props.isSelected) {
            const timeout = setTimeout(() => {
              // Move the carousel view button into the group of views
              const carouselBtn = document.querySelector('#carousel-toolbar-button');

              if (carouselBtn) {
                const parentToolbarGroup = carouselBtn.parentNode.parentNode.parentNode;
                const siblingToolbarGroup = parentToolbarGroup.previousSibling.querySelectorAll('.components-toolbar-group');

                if (parentToolbarGroup && siblingToolbarGroup.length) {
                  for (const group of siblingToolbarGroup) {
                    // Make sure to find and append into the proper group
                    if (group.innerHTML.includes('List view') && group.innerHTML.includes('Grid view')) {
                      carouselBtn.parentNode.style.padding = 0;
                      parentToolbarGroup.style.padding = 0;
                      group.append(parentToolbarGroup);
                    }
                  }
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
            <InspectorControls>
              <PanelBody
                title={__('Layout view', 'planet4-blocks-backend')}
                initialOpen={true}
              >
                <RadioControl
                  label={__('Display as', 'planet4-blocks-backend')}
                  help={__('The layout of the action|post list', 'planet4-blocks-backend')}
                  selected={attributes.displayLayout.type}
                  options={layoutOptions}
                  onChange={value => onChangeHandler(value)}
                />
              </PanelBody>
            </InspectorControls>
            <BlockControls group="other">
              <ToolbarGroup>
                <ToolbarButton
                  id="carousel-toolbar-button"
                  icon="slides"
                  label={__('Carousel view', 'core-block-custom-attributes')}
                  isActive={attributes.displayLayout.type === 'carousel'}
                  onClick={() => onChangeHandler('carousel')}
                />
              </ToolbarGroup>
            </BlockControls>
            <BlockEdit {...props} />
          </>
        ), [attributes]);
      }
    )
  );
};
