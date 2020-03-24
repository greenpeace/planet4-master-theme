document.addEventListener( 'DOMContentLoaded', () => {
  let spreadsheetBlocks = document.querySelectorAll( '.block-spreadsheet' );

  [...spreadsheetBlocks].forEach( block => {

    // Filter Spreadsheet table by entered value in search field
    let searchInput = block.querySelector( '.spreadsheet-search' );
    let table = block.querySelector( '.spreadsheet-table' );
    let rows = [...table.querySelectorAll( 'tr:not(:first-child)' )];
    let headerCells = [...table.querySelectorAll( 'tr:first-child th' )];
    let defaultSortButton = block.querySelector( '.spreadsheet-default-sort' );
    let emptyMessage = block.querySelector( '.spreadsheet-empty-message' );

    searchInput.addEventListener( 'input', () => {
      let searchTerm = searchInput.value;
      let hasMatches = false;
      rows.forEach( row => {
        const hasTerm = row.textContent.includes( searchTerm );
        hasMatches = hasMatches || hasTerm;
        row.style.display = hasTerm ? '' : 'none';
      } );
      emptyMessage.style.display = hasMatches ? 'none' : 'block';
    } );

    const removeSortingAttributes = () => {
      headerCells.forEach( cell => {
        cell.classList.remove( 'spreadsheet-sorted-by' );
        cell.dataset.sortDir = null;
      } );
    };

    const getTextAtCell = ( row, index ) => row.children[ index ].textContent;

    headerCells.forEach( ( headerCell, index ) => {
      headerCell.addEventListener( 'click', () => {
        defaultSortButton.style.display = 'inline-block';
        let prevDir = headerCell.dataset.sortDir;
        removeSortingAttributes();
        headerCell.classList.add( 'spreadsheet-sorted-by' );

        let sortedRows = rows.sort(
          ( a, b ) => getTextAtCell( a, index ).localeCompare( getTextAtCell( b, index ) )
        );
        let newDir;
        if ( prevDir ) {
          newDir = prevDir === 'asc' ? 'desc' : 'asc';
        } else {
          newDir = 'asc';
        }
        headerCell.dataset.sortDir = newDir;
        if ( newDir === 'desc' ) {
          sortedRows = sortedRows.reverse();
        }
        table.append( ...sortedRows );
      } );
    } );

    defaultSortButton.addEventListener( 'click', () => {
      removeSortingAttributes();
      let sortedRows = rows.sort( ( a, b ) => a.dataset.order - b.dataset.order );
      table.append( ...sortedRows );
      defaultSortButton.style.display = 'none';
    } );
  } );

} );
