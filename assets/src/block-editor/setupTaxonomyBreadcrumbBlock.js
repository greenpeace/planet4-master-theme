const {__} = wp.i18n;
const {useBlockProps} = wp.blockEditor;

import {LISTS_BREADCRUMBS} from '../blocks/PostsList';

export const TAX_BREADCRUMB_BLOCK_NAME = 'p4/taxonomy-breadcrumb';

export const setupTaxonomyBreadcrumbBlock = () => {
  wp.blocks.registerBlockType(
    TAX_BREADCRUMB_BLOCK_NAME,
    {
      apiVersion: 3,
      title: __('Taxonomy Breadcrumb', 'planet4-master-theme-backend'),
      icon: 'tag',
      category: 'widgets',
      attributes: {
        taxonomy: {
          type: 'string',
          default: LISTS_BREADCRUMBS[0].value,
        },
        isLink: {
          type: 'boolean',
          default: true,
        },
      },
      supports: {html: false},
      usesContext: ['postId', 'postType'],
      edit: editFunction,
      save: saveFunction,
    }
  );
};

function editFunction({attributes, context}) {
  const {isLink} = attributes;
  const {postId, postType} = context;
  const [term, setTerm] = wp.element.useState(null);
  const TAXONOMY = window.p4_vars.options.taxonomy_breadcrumbs || null;

  wp.element.useEffect(() => {
    if (!postId || !TAXONOMY || !postType) {return;}

    let postTypeField = '';

    if (postType === 'post') {
      postTypeField = 'posts';
    }
    else if (postType === 'page') {
      postTypeField = 'pages';
    }
    else {
      postTypeField = postType;
    }

    const taxonomyField = (postType === 'p4_action') || (TAXONOMY === 'category') ? 'categories' : TAXONOMY;

    wp.apiFetch({path: `/wp/v2/${postTypeField}/${postId}`}).then(post => {
      const termIds = post[taxonomyField];
      if (termIds && termIds.length > 0) {
        wp.apiFetch({path: `/wp/v2/${taxonomyField}/${termIds[0]}`}).then(termData => {
          setTerm(termData.name);
        });
      }
    });
  }, [postId, TAXONOMY]);

  const contentAttrs = {className: 'wp-block-post-terms'};
  if(!isLink) {
    contentAttrs['aria-hidden'] = true;
  }
  let content = wp.element.createElement('div', contentAttrs, term);
  if (isLink) {
    const linkAttrs = {href: '', onClick: e => e.preventDefault()};
    const link = term ? wp.element.createElement('a', linkAttrs, term || 'Loading...') : null;
    content = link ? wp.element.createElement('div', contentAttrs, link) : null;
  }

  return <div {...useBlockProps()}>{content}</div>;
}

function saveFunction() {
  return null;
}
