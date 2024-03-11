import {RadioControl, PanelBody} from '@wordpress/components';
import {InspectorControls} from '@wordpress/block-editor';
import {useCallback, useEffect, useMemo, useState} from '@wordpress/element';
import {POSTS_LIST_BLOCK_NAME, POSTS_LISTS_LAYOUT_TYPES} from '../../blocks/PostsList';
import {ACTIONS_LIST_BLOCK_NAME, ACTIONS_LIST_LAYOUT_TYPES} from '../../blocks/ActionsList';
import {PostSelector} from '../../block-editor/PostSelector/PostSelector';

const {addFilter} = wp.hooks;
const {__} = wp.i18n;

const targetP4Blocks = [ACTIONS_LIST_BLOCK_NAME, POSTS_LIST_BLOCK_NAME];

export const setupQueryLoopBlockExtension = () => {
  const {createHigherOrderComponent} = wp.compose;

  addFilter(
    'editor.BlockEdit',
    'planet4-blocks/overrides/query-loop-layout',
    createHigherOrderComponent(
      BlockEdit => props => {
        const {attributes, setAttributes} = props;
        const isActionsList = attributes.namespace === ACTIONS_LIST_BLOCK_NAME;
        const isPostsList = attributes.namespace === POSTS_LIST_BLOCK_NAME;

        if (!isActionsList && !isPostsList) {
          return <BlockEdit {...props} />;
        }

        const [postTemplate, setPostTemplate] = useState();
        const [selectedBlock, setSelectedBlock] = useState();

        const {className, query} = attributes;
        const layoutTypes = isActionsList ? ACTIONS_LIST_LAYOUT_TYPES : POSTS_LISTS_LAYOUT_TYPES;

        const updateLayoutType = useCallback(type => {
          const layoutType = layoutTypes.find(t => t.value === type);

          if (!layoutType) {
            return;
          }

          const pattern = 'is-custom-layout-';

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
        }, [attributes]);

        const updateQuery = value => setAttributes({query: {
          ...query,
          postIn: value && value.length > 0 ? value : [],
        }});

        useEffect(() => {
          if (postTemplate) {
            const timeout = setTimeout(() => {
              // Find all toolbar groups only when the block is selected
              for (const group of document.querySelectorAll('.components-toolbar-group')) {
                // Find the views group from toolbar
                if (group.innerHTML.includes('List view') || group.innerHTML.includes('Grid view')) {
                  group.style.display = 'none';
                }
              }
            }, 10);

            return () => clearTimeout(timeout);
          }
        }, [postTemplate]);

        useEffect(() => {
          if (selectedBlock && selectedBlock.name === 'core/post-template') {
            const parentBlock = wp.data.select('core/block-editor')
              .getBlock(wp.data.select('core/block-editor')
                .getBlockParents(selectedBlock.clientId)[0]);

            if (parentBlock && targetP4Blocks.includes(parentBlock.attributes.namespace)) {
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
              {attributes.layout && (
                <PanelBody
                  title={__('Layout view', 'planet4-blocks-backend')}
                  initialOpen={true}
                >
                  <RadioControl
                    label={__('Display as', 'planet4-blocks-backend')}
                    help={__('The layout of the action|post list', 'planet4-blocks-backend')}
                    selected={attributes.layout.type}
                    options={layoutTypes}
                    onChange={updateLayoutType}
                  />
                </PanelBody>
              )}
              {
                <PanelBody title={__('Manual override', 'planet4-blocks-backend')} initialOpen={query.postIn.length > 0}>
                  <PostSelector
                    label={__('CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains intact.', 'planet4-blocks-backend')}
                    selected={query.postIn || []}
                    onChange={updateQuery}
                    postType={query.postType || 'post'}
                    postParent={query?.postParent || null}
                    placeholder={__('Select articles', 'planet4-blocks-backend')}
                    maxLength={10}
                    maxSuggestions={20}
                  />
                </PanelBody>
              }
            </InspectorControls>
            <BlockEdit {...props} />
          </>
        ), [
          props,
          postTemplate,
          selectedBlock,
          wp.data.select('core/block-editor').getSelectedBlock(),
        ]);
      }
    )
  );
};
