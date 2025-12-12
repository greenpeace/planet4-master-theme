import {HTMLSidebarHelp} from '../../block-editor/HTMLSidebarHelp/HTMLSidebarHelp';

const {__} = wp.i18n;

const URLDescriptionHelpTranslation =
  __(
    'Enter the URL of the Google Sheets spreadsheet containing your timeline data.',
    'planet4-master-theme-backend'
  ) +
  '<br><a href="https://timeline.knightlab.com/#make" target="_blank" rel="noopener noreferrer">' +
  __(
    'See the TimelineJS website for a template GSheet.',
    'planet4-master-theme-backend'
  ) +
  '</a><br>' +
  __(
    'Copy this, add your own timeline data, and publish to the web.',
    'planet4-master-theme-backend'
  ) +
  '<br>' +
  __(
    'Once you have done so, use the URL from your address bar (not the one provided in Google\'s \'publish to web\' dialog).',
    'planet4-master-theme-backend'
  );

export const URLDescriptionHelp = () => <HTMLSidebarHelp>
  { URLDescriptionHelpTranslation }
</HTMLSidebarHelp>;
