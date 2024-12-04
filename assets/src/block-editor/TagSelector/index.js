import {compose} from '@wordpress/compose';
import {withSelect} from '@wordpress/data';
import {TaxonomySelector} from '../TaxonomySelector';

const TagSelector = props => <TaxonomySelector label="Select Tags" {...props} />;

export default compose(
  withSelect(select => ({
    suggestions: select('core').getEntityRecords(
      'taxonomy',
      'post_tag',
      {hide_empty: false, per_page: 100}
    ) || [],
  }))
)(TagSelector);
