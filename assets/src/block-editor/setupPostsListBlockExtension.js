export const setupPostsListBlockExtension = () => {
  wp.blocks.registerBlockType('p4/taxonomy-breadcrumb', {
    apiVersion: 2,
    title: 'Taxonomy Breadcrumb',
    icon: 'tag',
    category: 'widgets',
    attributes: {
      taxonomy: {
        type: 'string',
        default: 'category',
      },
    },
    supports: {html: false},
    usesContext: ['postId'],
    edit({attributes, context}) {
      const {taxonomy} = attributes;
      const postId = context.postId;
      const [term, setTerm] = wp.element.useState(null);

      wp.element.useEffect(() => {
        if (!postId || !taxonomy) {return;}

        wp.apiFetch({path: `/wp/v2/posts/${postId}`}).then(post => {
          // console.log(post);
          const taxonomyField = taxonomy === 'category' ? 'categories' : taxonomy;
          const termIds = post[taxonomyField];
          if (termIds && termIds.length > 0) {
            wp.apiFetch({path: `/wp/v2/${taxonomyField}/${termIds[0]}`}).then(termData => {
              setTerm(termData.name);
            });
          }
        });
      }, [postId, taxonomy]);

      return wp.element.createElement('p', {}, term || 'Loading...');
    },
    save() {
      return null;
    },
  });
};
