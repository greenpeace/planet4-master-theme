import { HTMLSidebarHelp } from '../../components/HTMLSidebarHelp/HTMLSidebarHelp';

const { __ } = wp.i18n;

const MailingListIframeHelpTranslation =
	__(
		'By default this block uses the Happy point Subscribe Form URL from ',
		'planet4-blocks-backend'
	)
	+ '<a href="admin.php?page=planet4_settings_defaults_content" target="_blank" rel="noopener noreferrer">'
	+ __(
		'Planet 4 Settings - Default content',
		'planet4-blocks-backend'
	)
	+ '</a>. '
  + __(
    'If a URL is set in this field, it will override this setting.',
    'planet4-blocks-backend'
  );

export const MailingListIframeHelp = () => <HTMLSidebarHelp>
  { MailingListIframeHelpTranslation }
</HTMLSidebarHelp>;
