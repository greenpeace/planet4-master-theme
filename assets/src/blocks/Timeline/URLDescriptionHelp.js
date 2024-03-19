import {HTMLSidebarHelp} from '../../block-editor/HTMLSidebarHelp/HTMLSidebarHelp';

const {__} = wp.i18n;

const URLDescriptionHelpTranslation =
  __(
    'Enter the URL of the Google Sheets spreadsheet containing your timeline data.',
    'planet4-blocks-backend'
  ) +
  '<br><a href="https://timeline.knightlab.com/#make" target="_blank" rel="noopener noreferrer">' +
  __(
    'See the TimelineJS website for a template GSheet.',
    'planet4-blocks-backend'
  ) +
  '</a><br>' +
  __(
    'Copy this, add your own timeline data, and publish to the web.',
    'planet4-blocks-backend'
  ) +
  '<br>' +
  __(
    'Once you have done so, use the URL from your address bar (not the one provided in Google\'s \'publish to web\' dialog).',
    'planet4-blocks-backend'
  );

export const URLDescriptionHelp = () => <HTMLSidebarHelp>
  { URLDescriptionHelpTranslation }
</HTMLSidebarHelp>;
