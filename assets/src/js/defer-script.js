(() => {
  /*eslint-disable no-console*/
  console.log('defer-script loaded.');
  for (const element of [['defer', 'purple']]) {
    document.querySelector(`.${element[0]}`).style.color = element[1];
  }
})();
