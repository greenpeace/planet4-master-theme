(() => {
  /*eslint-disable no-console*/
  console.log('defer-element loaded.');
  const elements = ['defer'];

  for (const element of elements) {
    const el = document.createElement('h3');
    el.className = element;
    el.innerHTML = `${element} element`;
    document.querySelector('.page-header-title').insertAdjacentElement('afterend', el);
  }
})();
