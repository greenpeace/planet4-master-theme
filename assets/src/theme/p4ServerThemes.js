const fetchThemes = async () => {
  return wp.apiFetch({
    path: 'planet4/v1/themes/',
    method: 'GET',
  });
};

const uploadTheme = async (name, theme) => {
  return wp.apiFetch({
    path: 'planet4/v1/add-theme/',
    method: 'POST',
    data: {
      name,
      theme,
    }
  });
};

const deleteTheme = async (name) => {
  return wp.apiFetch({
    path: 'planet4/v1/delete-theme/',
    method: 'POST',
    data: {
      name,
    }
  });
};

export const p4ServerThemes = { fetchThemes, uploadTheme, deleteTheme };
