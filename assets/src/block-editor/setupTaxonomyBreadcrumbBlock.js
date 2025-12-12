const {__} = wp.i18n;

import {LISTS_BREADCRUMBS} from '../blocks/PostsList';

export const TAX_BREADCRUMB_BLOCK_NAME = 'p4/taxonomy-breadcrumb';

export const setupTaxonomyBreadcrumbBlock = () => {
  wp.blocks.registerBlockType(
    TAX_BREADCRUMB_BLOCK_NAME,
    {
      apiVersion: 2,
      title: __('Taxonomy Breadcrumb', 'planet4-master-theme-backend'),
      icon: 'tag',
      category: 'widgets',
      attributes: {
        taxonomy: {
          type: 'string',
          default: LISTS_BREADCRUMBS[0].value,
        },
        post_type: {
          type: 'string',
          default: 'post',
        },
      },
      supports: {html: false},
      usesContext: ['postId'],
      edit: editFunction,
      save: saveFunction,
    }
  );
};

function editFunction({attributes, context}) {
  const {post_type} = attributes;
  const postId = context.postId;
  const [term, setTerm] = wp.element.useState(null);
  const TAXONOMY = window.p4_vars.options.taxonomy_breadcrumbs || null;

  wp.element.useEffect(() => {
    if (!postId || !TAXONOMY || !post_type) {return;}

    const postTypeField = post_type === 'post' ? 'posts' : post_type;
    const taxonomyField = (post_type === 'p4_action') || (TAXONOMY === 'category') ? 'categories' : TAXONOMY;

    wp.apiFetch({path: `/wp/v2/${postTypeField}/${postId}`}).then(post => {
      const termIds = post[taxonomyField];
      if (termIds && termIds.length > 0) {
        wp.apiFetch({path: `/wp/v2/${taxonomyField}/${termIds[0]}`}).then(termData => {
          setTerm(termData.name);
        });
      }
    });
  }, [postId, TAXONOMY]);

  const linkAttrs = {href: '', onClick: e => e.preventDefault()};
  const contentAttrs = {className: 'taxonomy-category wp-block-post-terms'};

  const link = term ? wp.element.createElement('a', linkAttrs, term || 'Loading...') : null;
  const content = link ? wp.element.createElement('div', contentAttrs, link) : null;

  return content;
}

function saveFunction() {
  return null;
}
