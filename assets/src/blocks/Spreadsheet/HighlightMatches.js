export const HighlightMatches = (cellValue, searchText, className = 'highlighted-text') => {
  const reg = new RegExp('(' + searchText.trim() + ')', 'gi');
  const parts = cellValue.split(reg);

  // Skips the first empty value and the intermediate parts
  for (let i = 1; i < parts.length; i += 2) {
    parts[i] = (
      <span key={i} className={className}>
        { parts[i] }
      </span>
    );
  }

  return <>{ parts }</>;
};
