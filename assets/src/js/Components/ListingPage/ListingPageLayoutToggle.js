import {__} from '@wordpress/i18n';

/**
 * Renders a toggle button for switching the listing page between grid and list layouts.
 *
 * @param {Object}   props          Component props.
 * @param {string}   props.layout   The current layout, either `'grid'` or `'list'`.
 * @param {Function} props.onToggle Callback invoked when the button is clicked, to switch the layout.
 *
 * @return {JSX.Element} The rendered layout toggle button.
 */
function ListingPageLayoutToggle({layout, onToggle}) {
  const isGrid = layout === 'grid';

  return (
    <button
      type="button"
      className={`layout-toggle layout-toggle-${isGrid ? 'list' : 'grid'}`}
      title={isGrid ? __('List View', 'planet4-master-theme') : __('Grid View', 'planet4-master-theme')}
      aria-label={isGrid ?
        __('Switch to list view', 'planet4-master-theme') :
        __('Switch to grid view', 'planet4-master-theme')}
      data-layout={isGrid ? 'list' : 'grid'}
      onClick={onToggle}
    />
  );
}

export default ListingPageLayoutToggle;
