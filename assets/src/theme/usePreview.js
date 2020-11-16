import { useState, useEffect} from 'react';

const read = prop => document.documentElement.style.getPropertyValue(prop);
const write = (prop, value) => document.documentElement.style.setProperty(prop, value);
const unset = prop => document.documentElement.style.removeProperty(prop);

const getInitialValue = (property) => {
  if (!property) {
    return null;
  }

  try {
    return read(property);
  } catch (e) {
    console.log(property, e);
    return null;
  }
}

export const usePreview = (property, initialValue) => {
  const isHoverProperty = /hover--\w+(-\w+)*/.test(property);
  const regularProperty = property.replace('-hover', '');
  const [origValue, setOrigValue] = useState(getInitialValue(isHoverProperty ? regularProperty : property));

  const [isPreviewing, setIsPreviewing] = useState(false);

  const [value, setValue] = useState(initialValue);
  const [wasUsed, setWasUsed] = useState(false);

  const undoPropertyChange = () => {
    if (!wasUsed) {
      return;
    }
    const prop = isHoverProperty ? regularProperty : property;

    if (typeof origValue === 'string') {
      write(prop, origValue);
    } else {
      unset(prop);
    }
  }

  useEffect(() => {
    if (isPreviewing && typeof value === 'string') {
      setWasUsed(true);

      const prop = isHoverProperty ? regularProperty : property;
      setOrigValue(read(prop));
      write(prop, value)
    }

    return undoPropertyChange;
  }, [value, isPreviewing])

  useEffect(() => {
    if (wasUsed && !isPreviewing) {
      undoPropertyChange();
    }
  },[isPreviewing]);

  return {
    setIsPreviewing,
    setValue,
    isPreviewing,
    origValue,
    setOrigValue: (value) => {
      console.log(value);
      setOrigValue(value);
    },
  };
}
