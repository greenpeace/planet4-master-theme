import { HTMLSidebarHelp } from '../../components/HTMLSidebarHelp/HTMLSidebarHelp';

const { __ } = wp.i18n;

const EmbedCodeHelpTranslation =
	__(
		'By default this block uses the "Happy Point HubSpot embed code" in ',
		'planet4-blocks-backend'
	)
	+ '<a href="admin.php?page=planet4_settings_defaults_content" target="_blank" rel="noopener noreferrer">'
	+ __(
		'Planet 4 Settings - Default content',
		'planet4-blocks-backend'
	)
	+ '</a>. '
  + __(
    'If an embed code is set here, it will override this setting.',
    'planet4-blocks-backend'
  );

export const EmbedCodeHelp = () => <HTMLSidebarHelp>
  { EmbedCodeHelpTranslation }
</HTMLSidebarHelp>;
