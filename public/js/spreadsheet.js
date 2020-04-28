document.addEventListener( 'DOMContentLoaded', () => {
  const debounce = ( func, wait = 100 ) => {
    let timeout;
    return function ( ...args ) {
      clearTimeout( timeout );
      timeout = setTimeout( () => {
        func.apply( this, args );
      }, wait );
    };
  };

  let spreadsheetBlocks = document.querySelectorAll( '.block-spreadsheet' );

  [...spreadsheetBlocks].forEach( block => {

    // Filter Spreadsheet table by entered value in search field
    const searchInput = block.querySelector( '.spreadsheet-search' );
    const table = block.querySelector( '.spreadsheet-table' );
    let tableBody = table.querySelector( 'tbody' );
    let rows = [...tableBody.querySelectorAll( 'tr' )];
    const headerCells = [...table.querySelectorAll( 'thead th' )];
    const emptyMessage = block.querySelector( '.spreadsheet-empty-message' );

    const filterRows = ( event ) => {
      let hasMatches = false;

      const searchTerm = event.target.value.toLowerCase().trim();

      rows.forEach( row => {
        const hasTerm = searchTerm === '' || row.textContent.toLowerCase().includes( searchTerm );
        hasMatches = hasMatches || hasTerm;
        if ( !hasTerm && row.parentNode === tableBody ) {
          tableBody.removeChild( row );
        }
        if ( hasTerm ) {
          tableBody.appendChild( row );
        }
      } );
      emptyMessage.style.display = hasMatches ? 'none' : 'block';
    };

    searchInput.addEventListener( 'input', debounce( filterRows, 100 ) );

    const removeSortingAttributes = () => {
      headerCells.forEach( cell => {
        cell.classList.remove( 'spreadsheet-sorted-by' );
        cell.dataset.sortDir = null;
      } );
    };

    const getTextAtCell = ( index, row ) => row.children[ index ].textContent;

    const sortRows = ( headerCell, index ) => () => {
      let prevDir = headerCell.dataset.sortDir;
      removeSortingAttributes();
      headerCell.classList.add( 'spreadsheet-sorted-by' );

      rows = rows.sort( ( rowA, rowB ) => {
        const textCompare = getTextAtCell( index, rowA ).localeCompare( getTextAtCell( index, rowB ) );
        if ( textCompare !== 0 ) {
          return textCompare;
        }
        // If text is the same preserve the original sort order.
        return rowA.dataset.order - rowB.dataset.order;
      } );
      const newDir = prevDir !== 'asc' ? 'asc' : 'desc';
      headerCell.dataset.sortDir = newDir;
      if ( newDir === 'desc' ) {
        rows = rows.reverse();
      }

      tableBody.append( ...rows.filter( row => row.parentNode === tableBody ) );
    };
    headerCells.forEach( ( headerCell, index ) => {

      headerCell.addEventListener( 'click', sortRows( headerCell, index ) );
    } );
  } );

} );
