/* eslint-disable jsx-a11y/anchor-is-valid */

import {__} from '@wordpress/i18n';

function getPageNumbers(currentPage, totalPages) {
  const delta = 2; // how many pages to show around the current page
  const range = [];
  const rangeWithDots = [];
  let last;

  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentPage - delta && i <= currentPage + delta)) {
      range.push(i);
    }
  }

  range.forEach(i => {
    if (last) {
      if (i - last === 2) {
        rangeWithDots.push(last + 1);
      } else if (i - last > 2) {
        rangeWithDots.push('...');
      }
    }
    rangeWithDots.push(i);
    last = i;
  });

  return rangeWithDots;
}

function handleClick(event, page, onPageChange) {
  event.preventDefault();
  onPageChange(page);
}

function PageNumber({page, currentPage, onPageChange}) {
  if (page === '...') {
    return (
      <span className="page-numbers dots">
				…
      </span>
    );
  }

  if (page === currentPage) {
    return (
      <span aria-current="page" className="page-numbers current">
        { page }
      </span>
    );
  }

  return (
    <a
      href="#"
      className="page-numbers"
      onClick={event => handleClick(event, page, onPageChange)}
    >
      { page }
    </a>
  );
}

function Paginator({currentPage, totalPages, onPageChange}) {
  if (totalPages <= 1) {
    return null;
  }

  const isFirstPage = currentPage <= 1;
  const isLastPage = currentPage >= totalPages;
  const pageNumbers = getPageNumbers(currentPage, totalPages);

  return (
    <nav className="wp-block-query-pagination is-layout-flex wp-block-query-pagination-is-layout-flex" aria-label="Pagination">
      <a
        href="#"
        className={`wp-block-query-pagination-previous${isFirstPage ? ' disabled' : ''}`}
        tabIndex={isFirstPage ? -1 : undefined}
        aria-hidden={isFirstPage ? 'true' : undefined}
        onClick={event => {
          if (isFirstPage) {
            event.preventDefault();
            return;
          }
          handleClick(event, currentPage - 1, onPageChange);
        }}
      >
        { __('Prev', 'planet4-master-theme') }
      </a>

      <div className="wp-block-query-pagination-numbers">
        { pageNumbers.map((page, index) => (
          <PageNumber
            // eslint-disable-next-line react/no-array-index-key
            key={page === '...' ? `dots-${index}` : page}
            page={page}
            currentPage={currentPage}
            onPageChange={onPageChange}
          />
        )) }
      </div>

      <a
        href="#"
        className={`wp-block-query-pagination-next${isLastPage ? ' disabled' : ''}`}
        onClick={event => {
          if (isLastPage) {
            event.preventDefault();
            return;
          }
          handleClick(event, currentPage + 1, onPageChange);
        }}
      >
        { __('Next', 'planet4-master-theme') }
      </a>
    </nav>
  );
}

export default Paginator;
