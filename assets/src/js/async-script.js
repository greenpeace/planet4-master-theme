(() => {
  /*eslint-disable no-console*/
  console.log('async-script loaded.');
  for (const element of [['async', 'green']]) {
    document.querySelector(`.${element[0]}`).style.color = element[1];
  }
})();
