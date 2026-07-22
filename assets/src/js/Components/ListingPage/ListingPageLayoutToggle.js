import {__} from '@wordpress/i18n';

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
