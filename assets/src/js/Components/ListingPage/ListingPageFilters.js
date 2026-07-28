import {useState, useEffect} from '@wordpress/element';
import {__} from '@wordpress/i18n';

/**
 * Renders a dropdown filter with an "all" option plus one option per item in `options`.
 *
 * @param {Object}                            props           Component props.
 * @param {string}                            props.id        The `id`/`htmlFor` used to associate the label and select.
 * @param {string}                            props.label     Label text displayed above the select.
 * @param {string}                            props.allLabel  Text for the default "all" option (value `''`).
 * @param {Array<{id: number, name: string}>} [props.options] List of selectable options (e.g. terms), each with an `id` and `name`.
 * @param {number|string}                     props.value     The currently selected value (`''` for the "all" option, otherwise a numeric id).
 * @param {Function}                          props.onChange  Callback invoked with the new value (a number, or `''` for "all") on change.
 *
 * @return {JSX.Element} The rendered filter select.
 */
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

/**
 * Renders the listing page's filter controls (content type, category, and
 * keyword/tag dropdowns) plus an "Apply filters" button.
 *
 * @param {Object}                            props                   Component props.
 * @param {Array<{id: number, name: string}>} props.postTypes         List of available post type terms.
 * @param {Array<{id: number, name: string}>} props.categories        List of available category terms.
 * @param {Array<{id: number, name: string}>} props.tags              List of available tag terms.
 * @param {number|string}                     [props.currentPostType] Currently applied post type filter (`''` if none).
 * @param {number|string}                     [props.currentCategory] Currently applied category filter (`''` if none).
 * @param {number|string}                     [props.currentTag]      Currently applied tag filter (`''` if none).
 * @param {Function}                          props.onApply           Callback invoked with `{ postType, category, tag }` when the user applies filters.
 *
 * @return {JSX.Element} The rendered filter controls.
 */
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

  // Keep the dropdown in sync when the parent's filters change from outside of this component.
  useEffect(() => {
    setPostType(currentPostType);
  }, [currentPostType]);

  // Keep the dropdown in sync when the parent's filters change from outside of this component.
  useEffect(() => {
    setCategory(currentCategory);
  }, [currentCategory]);

  // Keep the dropdown in sync when the parent's filters change from outside of this component.
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
