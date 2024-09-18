import {HTMLSidebarHelp} from '../../block-editor/HTMLSidebarHelp/HTMLSidebarHelp';

const {__} = wp.i18n;

const OverrideFormHelpTranslation =
  __(
    'By default this block uses the "Default Happy Point Form" in ',
    'planet4-blocks-backend'
  ) +
  '<a href="admin.php?page=planet4_settings_defaults_content" target="_blank" rel="noopener noreferrer">' +
  __(
    'Planet 4 Settings - Default content',
    'planet4-blocks-backend'
  ) +
  '</a>. ' +
  __(
    'If this box is checked, it will override this setting with the form specified below.',
    'planet4-blocks-backend'
  );

export const OverrideFormHelp = () => <HTMLSidebarHelp>
  { OverrideFormHelpTranslation }
</HTMLSidebarHelp>;
