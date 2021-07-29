import { setupThemeEditor } from 'use-theme-editor/build/initializeThemeEditor';
import { p4ServerThemes } from './theme/p4ServerThemes';

document.addEventListener('DOMContentLoaded', async () => {
  setupThemeEditor({
    serverThemes: p4ServerThemes,
  });
});
