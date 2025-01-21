const filterBlockNames = () => {
  const selectedNs = document.getElementById('filter-by-ns').selectedOptions[0].value;
  const select = document.getElementById('filter-by-name');
  for (const option of select.options) {
    const display = selectedNs.length <= 0 ||
						option.value.length <= 0 ||
						option.value.startsWith('${selectedNs}/');
    option.style.display = display ? 'inline' : 'none';
  }
  if (selectedNs.length >= 1) {
    select.value = '';
  }
};
filterBlockNames();
