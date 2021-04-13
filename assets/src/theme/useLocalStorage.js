import { useEffect, useState } from 'react';

export const useLocalStorage = (key, defaultValue) => {
  const [value, setValue] = useState(() => {
    const stored = localStorage.getItem(key);

    return stored === null ? defaultValue : stored;
  });

  useEffect(() => {
    localStorage.setItem(key, value);
  }, [value]);

  return [
    value,
    setValue
  ];
}
