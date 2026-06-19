import {HTMLSidebarHelp} from '../../block-editor/HTMLSidebarHelp/HTMLSidebarHelp';

const {__} = wp.i18n;

const URLDescriptionHelpTranslation =
  __(
    'Enter the URL of the Google Sheets spreadsheet containing your timeline data.',
    'planet4-master-theme-backend'
  ) +
  '<br><br>Make a copy from <a href="https://docs.google.com/spreadsheets/d/1JSY5DMFu9axpgjw2nOZMDF7C3ZpzYbM7DoSAR_WWx_k/" target="_blank" rel="noopener noreferrer">' +
  __(
    'our template spreadsheet',
    'planet4-master-theme-backend'
  ) +
  __(
    '</a>. Add your own timeline data and change its sharing permissions to "Anyone with the link" and "Viewer". ',
    'planet4-master-theme-backend'
  ) +
  __(
    'Once you have done so, use the URL from the address bar.',
    'planet4-master-theme-backend'
  );

export const URLDescriptionHelp = () => <HTMLSidebarHelp>
  { URLDescriptionHelpTranslation }
</HTMLSidebarHelp>;
