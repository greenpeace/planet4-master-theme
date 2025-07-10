import {POSTS_LIST_BLOCK_NAME, POSTS_LISTS_LAYOUT_TYPES} from '../../blocks/PostsList';
import {ACTIONS_LIST_BLOCK_NAME, ACTIONS_LIST_LAYOUT_TYPES} from '../../blocks/ActionsList';
import {PostSelector} from '../PostSelector';

const {InspectorControls} = wp.blockEditor;
const {RadioControl, PanelBody} = wp.components;
const {useCallback, useEffect, useMemo, useState} = wp.element;
const {addFilter} = wp.hooks;
const {useSelect} = wp.data;
const {__} = wp.i18n;

/**
 * Compares 2 taxonomy filter values to see if they are different.
 *
 * @param {Array} tax1 - The first taxonomy filters.
 * @param {Array} tax2 - The second taxonomy filters.
 *
 * @return {boolean} - Whether or not the taxonomies are different.
 */
const areTaxonomiesDifferent = (tax1, tax2) => {
  if (!tax1 && !tax2) {
    return false;
  }
  if (!tax1 || !tax2) {
    return true;
  }

  return tax1?.post_tag?.length !== tax2?.post_tag?.length ||
    tax1['p4-page-type']?.length !== tax2['p4-page-type']?.length ||
    tax1?.category?.length !== tax2?.category?.length;
};

const targetP4Blocks = [ACTIONS_LIST_BLOCK_NAME, POSTS_LIST_BLOCK_NAME];
const newsPageLink = window.p4_vars.news_page_link;

/**
 * Build a custom "see all" link url based on selected taxonomy filters.
 *
 * @param {Array} allTaxonomies - All the available taxonomies.
 * @param {Array} selected      - The selected taxonomy filters.
 *
 * @return {string} - The new "see all" link url.
 */
const buildCustomNewsPageLinkFromTaxonomies = (allTaxonomies, selected) => {
  if (!selected) {
    return newsPageLink;
  }
  let customSeeAllLink = newsPageLink + '?';
  const {category, post_tag, 'p4-page-type': postType} = selected;
  if (category?.length) {
    customSeeAllLink += `category=${allTaxonomies.categories.find(cat => cat.id === category[0]).slug}&`;
  }
  if (post_tag?.length) {
    customSeeAllLink += `tag=${allTaxonomies.tags.find(tag => tag.id === post_tag[0]).slug}&`;
  }
  if (postType?.length) {
    customSeeAllLink += `post-type=${allTaxonomies.postTypes.find(pt => pt.id === postType[0]).slug}`;
  }

  // Remove the '&' or '?' character at the end if needed.
  return customSeeAllLink.endsWith('&') || customSeeAllLink.endsWith('?') ? customSeeAllLink.slice(0, -1) : customSeeAllLink;
};

/**
 * Add "News & Stories" links one by one, recursively looking through inner blocks.
 *
 * @param {Array} innerBlocks - The inner blocks.
 * @param {Array} links       - The links that have already been found.
 */
const addNewsStoriesLink = (innerBlocks, links) => innerBlocks.forEach(innerBlock => {
  if (
    innerBlock.name === 'core/navigation-link' &&
    innerBlock.attributes?.className === 'see-all-link'
  ) {
    links.push(innerBlock);
  }

  if (innerBlock.innerBlocks?.length > 0) {
    addNewsStoriesLink(innerBlock.innerBlocks, links);
  }
});

/**
 * Sets up our custom implementation of the Query Loop block in the editor.
 */
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

        /**
         * All the taxonomy filters that are available for the Query Loop block.
         */
        const TAXONOMIES = useSelect(select => {
          const core = select('core');
          const postTypes = core.getEntityRecords('taxonomy', 'p4-page-type', {per_page: -1}) || [];
          const tags = core.getEntityRecords('taxonomy', 'post_tag', {per_page: -1}) || [];
          const categories = core.getEntityRecords('taxonomy', 'category', {per_page: -1}) || [];

          return {postTypes, tags, categories};
        },
        []);

        const [postTemplate, setPostTemplate] = useState();
        const [selectedBlock, setSelectedBlock] = useState();

        const {className, query, namespace} = attributes;
        const layoutTypes = isActionsList ? ACTIONS_LIST_LAYOUT_TYPES : POSTS_LISTS_LAYOUT_TYPES;
        const currentPostId = wp.data.select('core/editor').getCurrentPostId();

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

        // Make sure to exclude the current Post/Action from the list.
        useEffect(() => {
          setAttributes({
            query: {
              ...query,
              exclude: [currentPostId],
            },
          });
        }, []);

        // Update the News & Stories link based on the taxonomy filters selected in Posts List.
        useEffect(() => {
          if (!newsPageLink || !selectedBlock || namespace !== POSTS_LIST_BLOCK_NAME || !selectedBlock.innerBlocks) {
            return;
          }
          const newsStoriesLinks = [];
          addNewsStoriesLink(selectedBlock.innerBlocks, newsStoriesLinks);

          if (!newsStoriesLinks.length) {
            return;
          }
          const oldTaxonomies = selectedBlock.attributes.query.taxQuery || null;
          const newTaxonomies = query.taxQuery || null;
          if (!areTaxonomiesDifferent(oldTaxonomies, newTaxonomies)) {
            return;
          }
          newsStoriesLinks.forEach(link => link.attributes.url = buildCustomNewsPageLinkFromTaxonomies(TAXONOMIES, newTaxonomies));
        }, [attributes]);

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
                    label={__('CAUTION: Adding posts individually will override the automatic functionality of this block. For good user experience, please include at least 3 articles in list view, 4 articles in grid view, and 9 articles in carousel view, so that spacing and alignment of the design remains intact.', 'planet4-blocks-backend')}
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
              {
                <PanelBody title={__('Learn more about this block ', 'planet4-blocks-backend')} initialOpen={false}>
                  <p className="components-base-control__help">
                    {isActionsList && (
                      <>
                        <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/covers/" rel="noreferrer">
                        P4 Handbook Actions Lists
                        </a>
                        {' '} &#127745;
                      </>
                    )}
                    {isPostsList && (
                      <>
                        <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/posts-list/" rel="noreferrer">
                      P4 Handbook Posts List
                        </a>
                        {' '} &#128478;&#65039;
                      </>
                    )}
                  </p>
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
