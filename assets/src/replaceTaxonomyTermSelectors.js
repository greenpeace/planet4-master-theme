import AssignOnlyFlatTermSelector from "./components/AssignOnlyFlatTermSelector/AssignOnlyFlatTermSelector"

function customizeTaxonomySelectors( OriginalComponent ) {
  return function( props ) {
    // For following taxonomies it should not be possible to create new terms on the post edit page
    const isAssignOnlyTaxonomy = ['p4-page-type', 'post_tag'].includes(props.slug)

    return wp.element.createElement(
      isAssignOnlyTaxonomy ? AssignOnlyFlatTermSelector : OriginalComponent,
      props
    );
  }

}

export const replaceTaxonomyTermSelectors = () =>  {
  wp.hooks.addFilter(
    'editor.PostTaxonomyType',
    'planet4-gutenberg-blocks',
    customizeTaxonomySelectors
  );
}
