import { Fragment } from '@wordpress/element';

export const HighlightMatches = (cellValue, searchText, className = 'highlighted-text') => {
  let reg = new RegExp('(' + searchText.trim() + ')', 'gi');
  let parts = cellValue.split(reg);

  // Skips the first empty value and the intermediate parts
  for (let i = 1; i < parts.length; i += 2) {
    parts[i] = (
      <span key={ i } className={ className }>
        { parts[i] }
      </span>
    );
  }

  return <Fragment>{ parts }</Fragment>;
};
