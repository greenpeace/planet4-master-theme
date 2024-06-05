(() => {
  /*eslint-disable no-console*/
  console.log('async-elements loaded.');
  const elements = ['async'];

  for (const element of elements) {
    const el = document.createElement('h3');
    el.className = element;
    el.innerHTML = `${element} element`;
    document.querySelector('.page-header-title').insertAdjacentElement('afterend', el);
  }
})();
