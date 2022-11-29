// Dashboard page.
const onClickActionButton = actionButton => {
  const confirmationText = actionButton.dataset.confirm;
  let answer = confirmationText;

  if (confirmationText) {
    answer = confirm(confirmationText);
    if (!answer) {
      return;
    }
  }

  const action = actionButton.dataset.action;
  const responseSpan = actionButton.nextElementSibling;
  responseSpan.style.display = 'none';
  const ajaxurl = actionButton.dataset.ajaxurl;

  const url = new URL(ajaxurl);
  url.searchParams.append('action', action);
  url.searchParams.append('cp-action', action);
  url.searchParams.append('_wpnonce', document.querySelector('#_wpnonce').value);

  fetch(url)
    .then(response => response.json())
    .then(data => {
      if (!data.message) {
        return;
      }
      responseSpan.classList.remove('cp-error', 'cp-success');
      responseSpan.textContent = data.message;
      if (data.class) {
        responseSpan.classList.add(data.class);
      }
      responseSpan.style.display = 'block';
    }).catch(error => {
      console.log(error); //eslint-disable-line no-console
    });
};

const actionButtons = document.querySelectorAll('.btn-cp-action');

actionButtons.forEach(actionButton => {
  const action = actionButton.dataset.action;
  if (!action) {
    return;
  }

  actionButton.onclick = event => {
    event.preventDefault();
    onClickActionButton(actionButton);
  };
});
