import {compose} from '@wordpress/compose';
import {withSelect} from '@wordpress/data';
import {TaxonomySelector} from '../TaxonomySelector';

const PostTypeSelector = props => <TaxonomySelector label="Select Post Types" {...props} />;

export default compose(
  withSelect(select => ({
    suggestions: select('core').getEntityRecords('taxonomy', 'p4-page-type') || [],
  }))
)(PostTypeSelector);
