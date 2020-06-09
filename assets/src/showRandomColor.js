
export const showRandomColor = (cssVar) => {

  const randomColor = '#' + Math.floor( Math.random() * 16777215 ).toString( 16 );
  const propertyName = cssVar.usages[ 0 ].property;
  if ( ![ 'background' ].includes( propertyName ) && !propertyName.match( /color/ ) ) {
    return;
  }
  // const value = randomColor;
  document.documentElement.style.setProperty( cssVar.name, randomColor );
}


