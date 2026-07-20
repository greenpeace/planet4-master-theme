import {__, sprintf} from '@wordpress/i18n';

function Paginator({currentPage, totalPages, onPageChange}) {
  if (totalPages <= 1) {
    return null;
  }

  return (
    <nav className="listing-page-paginator" aria-label="Pagination">
      <button
        type="button"
        className="btn btn-secondary"
        disabled={currentPage <= 1}
        onClick={() => onPageChange(currentPage - 1)}
      >
        { __('Previous', 'planet4-master-theme') }
      </button>

      <span className="listing-page-paginator-status">
        { sprintf(
          /* translators: %d: page number */
          __('Page %1$d of %2$d', 'planet4-master-theme'),
          currentPage,
          totalPages
        ) }
      </span>

      <button
        type="button"
        className="btn btn-secondary"
        disabled={currentPage >= totalPages}
        onClick={() => onPageChange(currentPage + 1)}
      >
        { __('Next', 'planet4-master-theme') }
      </button>
    </nav>
  );
}

export default Paginator;
