import { useEffect, useState } from 'react';

const uploadTheme = async (name, theme) => {
  return wp.apiFetch({
    path: 'planet4/v1/add-theme/',
    method: 'POST',
    data: {
      name,
      theme,
    }
  });
}

const deleteTheme = async (name) => {
  return wp.apiFetch({
    path: 'planet4/v1/delete-theme/',
    method: 'POST',
    data: {
      name,
    }
  });
}

export const useServerThemes = () => {
  const [serverThemes, setServerThemes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [dirty, setDirty] = useState(false);

  useEffect(() => {
    const doApiCall = async () => {
      const themes = await wp.apiFetch({
        path: 'planet4/v1/themes/',
        method: 'GET',
      });
      setLoading(false);
      setServerThemes({
        'default': {},
        ...themes,
      });
    }
    doApiCall();
  }, [dirty]);

  return {
    serverThemes,
    loading,
    uploadTheme: async (name, theme) => {
      setLoading(true);
      await uploadTheme(name, theme);
      setDirty(!dirty);
    },
    deleteTheme: async (name) => {
      setLoading(true);
      await deleteTheme(name);
      setDirty(!dirty);
    }
  };
}

