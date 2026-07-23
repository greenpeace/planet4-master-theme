export const getUniqueId = prefix => {
  const r = Math.floor(Math.random() * 10000);
  const t = Date.now();
  return `${prefix}-${t}-${r}`;
};
