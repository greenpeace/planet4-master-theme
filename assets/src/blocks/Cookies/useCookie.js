const {useState, useEffect} = wp.element;

export const readCookie = name => {
  const declarations = document.cookie.split(';');
  let match = null;
  declarations.forEach(part => {
    const [key, value] = part.split('=');
    if (key.trim() === name) {
      match = value;
    }
  });
  return match;
};

export const writeCookie = (name, value, days = 365) => {
  const date = new Date();
  date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
  const secureMode = document.location.protocol === 'http:' ?
    ';SameSite=Lax' :
    ';SameSite=None;Secure';
  document.cookie = encodeURI(name) + '=' + encodeURI(value) + ';domain=.' + document.domain + ';path=/;' + '; expires=' + date.toGMTString() + secureMode;
};

// Value should not matter as cookie is expired.
export const removeCookie = name => writeCookie(name, '0', -1);

export const useCookie = name => {
  const [value, setValue] = useState(() => readCookie(name));

  const saveCookie = () => {
    if (value === null) {
      removeCookie(name);
      return;
    }
    writeCookie(name, value);
  };
  useEffect(saveCookie, [value]);

  return [
    value,
    setValue,
  ];
};

