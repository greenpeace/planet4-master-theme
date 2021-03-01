import { LOCAL_STORAGE_KEY } from './VarPicker';

export const exportJson = (fileName) => {
  const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
  const json = JSON.stringify(JSON.parse(raw), null, 2);
  const blob = new Blob([json], {type: "application/json"});
  const url  = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.download    = `${fileName || 'theme'}.json`;
  a.href        = url;
  a.textContent = "Download backup.json";
  a.click();
}

const formatCss = vars => {
  const lines = Object.keys(vars).map(k => `${ k }: ${ vars[k] };`);

  return lines.join('\n');
};

export const exportCss = (fileName) => {
  const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
  const vars = JSON.parse(raw);
  const css = formatCss(vars);
  const blob = new Blob([css], {type: "application/css"});
  const url  = URL.createObjectURL(blob);

  const a = document.createElement('a');
  a.download    = `${fileName || 'theme'}.css`;
  a.href        = url;
  a.textContent = "Download backup.json";
  a.click();
}

