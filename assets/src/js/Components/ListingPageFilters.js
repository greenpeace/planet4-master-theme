import {useState, useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';

function FilterSelect({id, label, allLabel, options = [], value, onChange}) {
  return (
    <div className="listing-page-select">
      <label htmlFor={id} className="listing-page-select-label">
        { label }
      </label>
      <select
        className="form-select"
        id={id}
        value={value}
        onChange={e => onChange(e.target.value ? Number(e.target.value) : '')}
      >
        <option value="">{ allLabel }</option>
        { options.map(option => (
          <option key={option.id} value={option.id}>
            { option.name }
          </option>
        )) }
      </select>
    </div>
  );
}

export default function ListingPageFilters({
  postTypes,
  categories,
  tags,
  currentPostType = '',
  currentCategory = '',
  currentTag = '',
  onApply,
}) {
  const [postType, setPostType] = useState(currentPostType);
  const [category, setCategory] = useState(currentCategory);
  const [tag, setTag] = useState(currentTag);

  // Keep the dropdowns in sync when the parent's filters change from
  // somewhere other than this component (e.g. read from the URL on load).
  useEffect(() => {
    setPostType(currentPostType);
  }, [currentPostType]);

  useEffect(() => {
    setCategory(currentCategory);
  }, [currentCategory]);

  useEffect(() => {
    setTag(currentTag);
  }, [currentTag]);

  const handleApply = () => {
    onApply({postType, category, tag});
  };

  return (
    <>
      <FilterSelect
        id="post-type"
        label={__('Content type', 'planet4-master-theme')}
        allLabel={__('All posts', 'planet4-master-theme')}
        options={postTypes}
        value={postType}
        onChange={setPostType}
      />

      <FilterSelect
        id="category"
        label={__('Category', 'planet4-master-theme')}
        allLabel={__('All topics', 'planet4-master-theme')}
        options={categories}
        value={category}
        onChange={setCategory}
      />

      <FilterSelect
        id="tag"
        label={__('Keywords', 'planet4-master-theme')}
        allLabel={__('All keywords', 'planet4-master-theme')}
        options={tags}
        value={tag}
        onChange={setTag}
      />

      <div className="filter-btn">
        <button
          id="apply-filters"
          className="btn btn-primary"
          onClick={handleApply}
        >
          { __('Apply filters', 'planet4-master-theme') }
        </button>
      </div>
    </>
  );
}
