import { useEffect, useState } from 'react';

export const useServerThemes = (config) => {
  const {
    fetchThemes,
    uploadTheme,
    deleteTheme,
  } = config;

  const [serverThemes, setServerThemes] = useState([]);
  const [loading, setLoading] = useState(true);
  const [dirty, setDirty] = useState(false);

  useEffect(() => {
    const doApiCall = async () => {
      const themes = await fetchThemes();
      setServerThemes({
        'default': {},
        ...themes,
      });
      setLoading(false);
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

