export const setupTaxonomyBreadcrumbBlock = () => {
  wp.blocks.registerBlockType(
    'p4/taxonomy-breadcrumb',
    {
      apiVersion: 2,
      title: 'Taxonomy Breadcrumb',
      icon: 'tag',
      category: 'widgets',
      attributes: {
        taxonomy: {
          type: 'string',
          default: 'category',
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
  const {taxonomy, post_type} = attributes;
  const postId = context.postId;
  const [term, setTerm] = wp.element.useState(null);

  wp.element.useEffect(() => {
    if (!postId || !taxonomy || !post_type) {return;}

    const postTypeField = post_type === 'post' ? 'posts' : post_type;
    const taxonomyField = taxonomy === 'category' ? 'categories' : taxonomy;

    wp.apiFetch({path: `/wp/v2/${postTypeField}/${postId}`}).then(post => {
      const termIds = post[taxonomyField];
      if (termIds && termIds.length > 0) {
        wp.apiFetch({path: `/wp/v2/${taxonomyField}/${termIds[0]}`}).then(termData => {
          setTerm(termData.name);
        });
      }
    });
  }, [postId, taxonomy]);

  const linkAttrs = {href: '', onClick: e => e.preventDefault()};
  const contentAttrs = {className: 'taxonomy-category wp-block-post-terms'};

  const link = wp.element.createElement('a', linkAttrs, term || 'Loading...');
  const content = wp.element.createElement('div', contentAttrs, link);

  return content;
}

function saveFunction() {
  return null;
}
