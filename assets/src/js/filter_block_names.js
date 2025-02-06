document.addEventListener('DOMContentLoaded', () => {
  const nsSelect = document.getElementById('filter-by-ns');
  const nameSelect = document.getElementById('filter-by-name');

  nsSelect.addEventListener('change', () => {
    const selectedNs = nsSelect.selectedOptions[0].value;

    for (const option of nameSelect.options) {
      const display = selectedNs.length === 0 ||
                      option.value.length === 0 ||
                      option.value.startsWith(`${selectedNs}/`);
      option.style.display = display ? 'block' : 'none';
    }

    if (selectedNs.length >= 1) {
      nameSelect.value = '';
    }
  });
});

