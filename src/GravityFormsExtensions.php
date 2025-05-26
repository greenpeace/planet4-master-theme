<?php

namespace P4\MasterTheme;

use GFAPI;
use GFCommon;
use GFFormsModel;
use DOMDocument;
use Timber\Timber;

/**
 * Class P4\MasterTheme\GravityFormsExtensions
 * The Gravity form plugin extension, use to add custom functionality like Planet4 form type.
 */
class GravityFormsExtensions
{
    public const DEFAULT_GF_TYPE = 'Other';

    public const P4_GF_TYPES = [
        [
            'label' => 'Other',
            'value' => 'Other',
        ],
        [
            'label' => 'Petition',
            'value' => 'Petition Signup',
        ],
        [
            'label' => 'Email Signup',
            'value' => 'Newsletter Signup',
        ],
        [
            'label' => 'Quiz/Poll',
            'value' => 'Quiz or Poll',
        ],
        [
            'label' => 'Email-to-target',
            'value' => 'Email to Target',
        ],
        [
            'label' => 'Contact',
            'value' => 'Contact',
        ],
        [
            'label' => 'Survey',
            'value' => 'Survey',
        ],
        [
            'label' => 'Feedback',
            'value' => 'Feedback',
        ],
        [
            'label' => 'Donation',
            'value' => 'Donation',
        ],
    ];

    public const P4_SHARE_BUTTONS = [
        [
            'label' => 'WhatsApp',
            'name' => 'whatsapp',
            'default_value' => 1,
        ],
        [
            'label' => 'Facebook',
            'name' => 'facebook',
            'default_value' => 1,
        ],
        [
            'label' => 'Twitter',
            'name' => 'twitter',
            'default_value' => 1,
        ],
        [
            'label' => 'Email',
            'name' => 'email',
            'default_value' => 1,
        ],
        [
            'label' => 'Native share (mobile only)',
            'name' => 'native',
            'default_value' => 1,
        ],
        [
            'label' => 'Bluesky',
            'name' => 'bluesky',
            'default_value' => 0,
        ],
    ];

    public array $current_entry = [];

    public string $is_payment_successful = '';

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->hooks();
        $this->fix_file_links();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        add_filter('gform_form_settings_fields', [ $this, 'p4_gf_form_settings' ], 5, 2);
        add_filter('gform_secure_file_download_url', [ $this, 'p4_gf_file_download_url' ], 10, 1);
        add_action('gform_after_save_form', [ $this, 'p4_gf_custom_initial_settings' ], 10, 2);
        add_filter('gform_confirmation_settings_fields', [ $this, 'p4_gf_confirmation_settings' ], 10, 3);
        add_filter('gform_confirmation', [ $this, 'p4_gf_custom_confirmation' ], 10, 3);
        add_filter('gform_field_css_class', [ $this, 'p4_gf_custom_field_class' ], 10, 3);
        add_filter('gform_form_args', [ $this, 'p4_gf_enforce_ajax' ], 10, 3);
        add_action('gform_after_save_form', [ $this, 'p4_gf_clear_page_caches' ], 10, 2);
        // Suppress the redirect in forms to use custom redirect handling.
        add_filter('gform_suppress_confirmation_redirect', '__return_true');
        add_filter('gform_confirmation', [ $this, 'p4_gf_custom_confirmation_redirect' ], 11, 3);
        add_filter('gform_pre_render', [ $this, 'p4_client_side_gravityforms_prefill' ], 10, 1);
        add_filter('gform_form_post_get_meta', [$this, 'p4_gf_enable_default_meta_settings'], 10, 1);
        add_filter('gform_hubspot_form_object_pre_save_feed', [$this, 'p4_gf_hb_form_object_pre_save_feed'], 10, 1);
        add_action('gform_after_submission', [$this, 'p4_send_gp_pixel_counter'], 10, 2);
        add_action('gform_stripe_fulfillment', [ $this, 'record_fulfillment_entry' ], 10, 2);
        add_action('gform_post_payment_action', [ $this, 'check_stripe_payment_status' ], 10, 2);
        add_action('gform_pre_render', [$this, 'enqueue_share_buttons'], 10, 2);
    }

    /**
     * Fix the link to files uploaded in a form.
     *
     * We need this because WP-Stateless duplicates the "year/month" path added as part of the file URL.
     * For example, "https://www.greenpeace.org/static/bucket/2025/05/gravity_forms/some_id/2025/05/filename"
     * has to be turned into "https://www.greenpeace.org/static/bucket/gravity_forms/some_id/2025/05/filename"
     * as the "year/month" sub-folders ("2025/05") are duplicated.
     */
    private function fix_file_links(): void
    {
        add_filter('gform_merge_tag_filter', [$this, 'fix_email_file_link'], 10, 6);
        add_filter('gform_entry_field_value', [$this, 'fix_entry_file_link'], 10, 4);
        add_filter('gform_entries_field_value', [$this, 'fix_entries_file_link'], 10, 4);
    }

    // @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Fix the link in the email body that connects to files uploaded in a form.
     */
    public function fix_email_file_link(
        string $value,
        string $merge_tag,
        string $modifier,
        object $field,
        mixed $raw_value,
        string $format
    ): string {
        if ($field->type !== 'fileupload' || $merge_tag !== 'all_fields') {
            return $value;
        }
        return $this->fix_google_cloud_link($value);
    }

    /**
     * Fix the link in a single entry that connects to files uploaded in a form.
     */
    public function fix_entry_file_link(
        string $value,
        object $field,
        mixed $entry,
        mixed $form
    ): string {
        if ($field->type !== 'fileupload') {
            return $value;
        }
        return $this->fix_google_cloud_link($value);
    }

    /**
     * Fix the link in the entries page that connects to files uploaded in a form.
     */
    public function fix_entries_file_link(
        string $value,
        int $form_id,
        mixed $field_id,
        mixed $entry
    ): string {
        $form = GFAPI::get_form($form_id);
        $field = GFFormsModel::get_field($form, $field_id);

        if ($field->type !== 'fileupload') {
            return $value;
        }
        return $this->fix_google_cloud_link($value);
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Remove the duplicated path from a link.
     *
     * For example, "https://www.greenpeace.org/static/bucket/2025/05/gravity_forms/some_id/2025/05/filename"
     * is turned into "https://www.greenpeace.org/static/bucket/gravity_forms/some_id/2025/05/filename"
     * as the "year/month" sub-folders ("2025/05") are duplicated.
     */
    private function fix_google_cloud_link(string $value): string
    {
        // Match all href='...'
        preg_match_all("/href='([^']+)'/", $value, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $href) {
                // Parse the URL
                $parts = parse_url($href);
                if (!isset($parts['scheme'], $parts['host'], $parts['path'])) {
                    continue; // Skip invalid URLs
                }

                $scheme = $parts['scheme'];
                $host = $parts['host'];
                $path = $parts['path'];

                $segments = explode('/', trim($path, '/'));

                // Check for duplicated folders (index 2 == 6 and 3 == 7)
                if (
                    !isset($segments[2], $segments[3], $segments[6], $segments[7]) ||
                    $segments[2] !== $segments[6] ||
                    $segments[3] !== $segments[7]
                ) {
                    continue;
                }

                unset($segments[2], $segments[3]);
                $segments = array_values($segments);
                $new_path = '/' . implode('/', $segments);
                $new_url = $scheme . '://' . $host . $new_path;

                // Replace only the href portion inside the value
                $value = str_replace($href, $new_url, $value);
            }
        }
        return $value;
    }

    /**
     * Enqueue the share buttons script only if the confirmation type is a message.
     * Pass the social data to the script.
     *
     * @param array $form The form setting.
     * @return array The form setting.
     *
     */
    public function enqueue_share_buttons(array $form): array
    {
        $social = reset($form['confirmations']);
        if ($social['type'] === 'message') {
            do_action('enqueue_share_buttons_script', $social);
        }
        return $form;
    }

    /**
     * Increase in one unit the total number of submissions for a counter by
     * making an API call to the Greenpeace Pixel Counter, or
     * adding an iframe.
     *
     * @link https://counter.greenpeace.org/documentation
     * @param array $form The form setting.
     * @param array $entry A form entry.
     *
     */
    public function p4_send_gp_pixel_counter(array $entry, array $form): void
    {
        if (!isset($form['p4_gf_counter'])) {
            return;
        }

        $endpoint_url = 'https://counter.greenpeace.org/count';
        $endpoint_url .= '?id=' . $form['p4_gf_counter'];
        $endpoint_url .= '&email_hash=' . $this->p4_gf_get_email_hash($form, $entry);

        // Pass the referer explicitily.
        $wp_remote_get_args = array(
            'headers' => array(
                'Referer' => home_url(),
            ),
        );

        $response = wp_remote_get($endpoint_url, $wp_remote_get_args);

        GFCommon::log_debug('Pixel Counter Response Message: ' . $response['body']);
        GFCommon::log_debug('Pixel Counter Response Status Code: ' . $response['response']['code']);
        GFCommon::log_debug('Pixel Counter Response Status Message: ' . $response['response']['message']);
        return;
    }

    /**
     * Save resulting entry after Stripe successful submission.
     *
     * @param mixed $session
     * @param array $entry
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     */
    public function record_fulfillment_entry($session, array $entry): void
    {
        $this->current_entry = $entry;
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

     /**
     * Save result of Stripe payment status.
     *
     * @param mixed $action
     * @param array $entry
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     */
    public function check_stripe_payment_status($action, array $entry): void
    {
        $this->is_payment_successful = rgar($action, 'payment_status');
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Add form setting to Gravity Forms to set the form type.
     *
     * @param array $fields The form settings fields.
     *
     * @return array The new fields array.
     */
    public function p4_gf_form_settings(array $fields): array
    {

        if (! array_key_exists('p4_options', $fields)) {
            $new_fields['p4_options'] = [
                'title' => __('Planet 4 Options', 'planet4-master-theme-backend'),
            ];

            // Add new field to beginning of the $fields array.
            $fields = array_merge($new_fields, $fields);
        }

        $fields['p4_options']['fields'][] = [
            'type' => 'select',
            'name' => 'p4_gf_type',
            'label' => __('Form Type', 'planet4-master-theme-backend'),
            'tooltip' => __(
                'Please select a form type below so you can track and analyze each form type separately',
                'planet4-master-theme-backend'
            ),
            'required' => true,
            'default_value ' => self::DEFAULT_GF_TYPE,
            'choices' => self::P4_GF_TYPES,
        ];

        $fields['p4_options']['fields'][] = [
            'type' => 'text',
            'name' => 'p4_gf_counter',
            'label' => __('Global Counter ID', 'planet4-master-theme-backend'),
            'tooltip' => __(
                'Add the Counter Name from counter.greenpeace.org',
                'planet4-master-theme-backend'
            ),
            'required' => false,
        ];

        return $fields;
    }

    /**
     * Update Gravity form file path before output.
     *
     * @param string $file_path The file path of the download file.
     *
     * @return string The new file path.
     */
    public function p4_gf_file_download_url(string $file_path): string
    {
        if (strpos($file_path, '/gravity_forms/') !== false) {
            // The default gravity form uploaded files path gives error.
            // eg. https://www.greenpeace.org/static/planet4-test-titan-stateless-develop/gravity_forms/8-23c5dc88bb5af48eb293c4c780a5ed0a/2022/09/e26f3fe9-2022_08_gravity_forms_3-1b36ac6eddacf20087d29746b297b384_2022_08_99ef18e1-predator.jpg
            // By updating a part[/year/month/] of file path('/gravity_forms/' => '/2022/09/gravity_forms/')
            // fix the issue.

            // Extract year and month from file path.
            $year_month = array_slice(explode('/', $file_path), -3, 2);

            // Update the gravity form file download path with year and month.
            return str_replace('/gravity_forms/', '/' . implode('/', $year_month) . '/gravity_forms/', $file_path);
        }

        return $file_path;
    }

    /**
     * Update initial settings for Gravity Forms (sub-label and description placement).
     *
     * @param array $form The form settings.
     * @param bool  $is_new Whether the form is newly created or not.
     */
    public function p4_gf_custom_initial_settings(array $form, bool $is_new): void
    {
        if (!$is_new) {
            return;
        }

        // Update sub-label and description placement initial settings.
        $form['subLabelPlacement'] = 'above';
        $form['descriptionPlacement'] = 'above';

        // Make form active immediately.
        $form['is_active'] = '1';

        GFAPI::update_form($form);

        // Default analytics feed meta.
        $feed_meta = [
            "feedName" => "Google Analytics",
            "gaeventgoalid_thickbox" => "",
            "gaeventgoal_thickbox" => "Submission: " . $form['title'],
            "gaeventcategory_thickbox" => "",
            "gaeventaction_thickbox" => "",
            "gaeventlabel_thickbox" => "{form_title} ID: {form_id}",
            "gaeventgoalid" => "",
            "gaeventgoal" => "Submission: " . $form['title'],
            "channel" => "",
            "gaeventcategory" => "",
            "gaeventaction" => "",
            "gaeventlabel" => "{form_title} ID: {form_id}",
            "gaeventvalue" => "",
            "feed_condition_conditional_logic_object" => [],
            "feed_condition_conditional_logic" => "0",
            "select_goal" => "",
            "feed_event_category" => "",
            "feed_event_action" => "",
            "feed_event_label" => "",
            "conditionalLogic" => "",
        ];

        GFAPI::add_feed($form_id = $form['id'], $feed_meta, $addon_slug = 'gravityformsgoogleanalytics');
    }

    /**
     * Add confirmation settings to Gravity Forms: the ability to add share buttons.
     *
     * @param array $fields The general confirmation settings fields.
     *
     * @return array The new fields array.
     */
    public function p4_gf_confirmation_settings(array $fields): array
    {
        echo '
			<style>
				.hidden {
					display: none !important;
				}
			</style>
		';

        // This bit of code is to hide the "Share Buttons" section
        // if editors select "Page" or "Redirect" as confirmation message.
        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        echo '
			<script>
				addEventListener("DOMContentLoaded", () => {
					const confirmationTypeCheckboxes = [...document.querySelectorAll("input[name=\"_gform_setting_type\"]")];
					const textTypeCheckbox = confirmationTypeCheckboxes.find(input => input.value === "message");
					const shareButtonsSettings = document.querySelector("#gform-settings-section-share-buttons");

					const onChange = checkbox => {
						if (checkbox.value === "message" && checkbox.checked) {
							shareButtonsSettings.classList.remove("hidden");
						} else {
							shareButtonsSettings.classList.add("hidden");
						}
					}

					confirmationTypeCheckboxes.forEach(input => input.addEventListener("change", event => onChange(event.currentTarget)));

					onChange(textTypeCheckbox);
				});
			</script>
		';
        // phpcs:enable Generic.Files.LineLength.MaxExceeded

        if (! array_key_exists('p4_share_buttons', $fields)) {
            $share_buttons['p4_share_buttons'] = [
                'title' => __('Share buttons', 'planet4-master-theme-backend'),
            ];

            // Add new field to end of the $fields array.
            $fields = array_merge($fields, $share_buttons);
        }

        $apply_social_sharing_options = planet4_get_option('apply_social_sharing_options', 'posts_and_forms');

        if ($apply_social_sharing_options === "posts") {
            $fields['p4_share_buttons']['fields'][] = [
                'type' => 'checkbox',
                'name' => 'p4_gf_share_platforms',
                'label' => __('Show share buttons below message', 'planet4-master-theme-backend'),
                'choices' => self::P4_SHARE_BUTTONS,
            ];
        }

        $fields['p4_share_buttons']['fields'][] = [
            'type' => 'text',
            'name' => 'p4_gf_share_text_override',
            'label' => __('Share text', 'planet4-master-theme-backend'),
            'tooltip' => __('This is the text that will be shared when a user clicks a share button (if the platform supports share text)', 'planet4-master-theme-backend'), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        ];

        $fields['p4_share_buttons']['fields'][] = [
            'type' => 'text',
            'name' => 'p4_gf_share_url_override',
            'label' => __('Override share URL', 'planet4-master-theme-backend'),
            'tooltip' => __('By default, share buttons will share the URL of the page that the form was submitted on. Use this field to override with a different URL.', 'planet4-master-theme-backend'), // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        ];

        return $fields;
    }

    /**
     * Find an email address in a form entry and return a SHA-256 hash of it. The returned hash is base64 encoded
     * and any '/' characters are removed.
     *
     * @param array $form The form setting.
     * @param array $entry A form entry.
     *
     * @return string The hashed email address or empty string if no email address is found.
     */
    public function p4_gf_get_email_hash(array $form, array $entry): string
    {
        $email_address = '';

        // Find the first email field in the form and extract the email address
        foreach ($form["fields"] as $i => $field) {
            if (get_class($field) === "GF_Field_Email") {
                $email_address = $entry[$field["id"]];
                break;
            }
        }

        if (empty($email_address)) {
            // Find any email address as a value in the form entry, in any type of form field
            foreach ($entry as $key => $value) {
                if (is_numeric($key) && filter_var(trim($value), FILTER_VALIDATE_EMAIL)) {
                    $email_address = trim($value);
                    break;
                }
            }
        }

        if (!empty($email_address)) {
            // Hash and base64 encode the email using SHA-256
            $hashed_email = base64_encode(hash('sha256', $email_address, true));

            // Remove '/' characters if present in Base64 encoding
            $hashed_email = str_replace('/', '', $hashed_email);

            return $hashed_email;
        }

        return '';
    }

    /**
     *
     * Return custom confirmation message for Gravity Forms.
     *
     * @param string|array $confirmation The default confirmation message.
     * @param mixed        $form         The form properties.
     * @param mixed        $entry        The current entry.
     *
     * @return string|array The custom confirmation message.
     */
    public function p4_gf_custom_confirmation($confirmation, $form, $entry)
    {
        // If the $confirmation object is an array, it means that it's a redirect page so we can directly use it.
        if (is_array($confirmation)) {
            return $confirmation;
        }

        $context = Timber::get_context();
        $post = Timber::query_post(false, Post::class);

        $current_confirmation = $form['confirmation'];

        $email_hash = $this->p4_gf_get_email_hash($form, $entry);

        $event_parameters = [
            "event" => "formSubmission",
            "formID" => $form['id'],
            "formPlugin" => "Gravity Form",
            "gGoal" => ($form['p4_gf_type'] ?? self::DEFAULT_GF_TYPE),
            "formTitle" => $form['title'],
            "gpUserId" => $email_hash,
            "eventTimeout" => 2000,
        ];

        // Filter hook to change the event parameters
        $event_parameters = apply_filters('planet4_datalayer_form_submission', $event_parameters, $form, $entry);

        $script = '<script type="text/javascript">
                        window.dataLayer = window.dataLayer || [];

                        const push_data = ' . json_encode($event_parameters) . '

                        window.dataLayer.push(push_data);

                        const gp_user_id_event = new CustomEvent("gp_user_id_set", {"detail":
                            {"gp_user_id": "' . $email_hash . '" }});
                        document.dispatchEvent(gp_user_id_event);

                        // Disable GFTrackEvent (GFTrackEvent belongs to Gravity Forms Google Analytics Add-On)
                        window.parent.gfgaTagManagerEventSent = true;
                   </script>';
        // Append a datalayer event script to $confirmation html.
        $confirmation .= $script;

        if (rgget('gf_stripe_success')) {
            $purchase_event = $this->p4_gf_stripe_custom_success_event($entry, $form);
            $confirmation .= "<script>// Ecommerce GA4
                window.dataLayer = window.dataLayer || []
                dataLayer.push(" . wp_json_encode($purchase_event) . ");
            </script>";
        }

        $share_platforms = [
            'facebook' => $current_confirmation['facebook'] ?? true,
            'twitter' => $current_confirmation['twitter'] ?? true,
            'whatsapp' => $current_confirmation['whatsapp'] ?? true,
            'native' => $current_confirmation['native'] ?? true,
            'email' => $current_confirmation['email'] ?? true,
            'bluesky' => $current_confirmation['bluesky'] ?? true,
        ];

        $apply_social_sharing_options = planet4_get_option('apply_social_sharing_options', 'posts_and_forms');

        if ($apply_social_sharing_options === "posts_and_forms") {
            $social_share_options = planet4_get_option('social_share_options', []);
            $share_platforms = [
                'facebook' => in_array('facebook', $social_share_options),
                'twitter' => in_array('twitter', $social_share_options),
                'whatsapp' => in_array('whatsapp', $social_share_options),
                'email' => in_array('email', $social_share_options),
                'bluesky' => in_array('bluesky', $social_share_options),
                // We might add a setting for this one in the future, but for now we always enable it in GF.
                'native' => true,
            ];
        }

        $confirmation_fields = [
            'confirmation' => $confirmation,
            'share_platforms' => $share_platforms,
            'post' => $post,
            'social_accounts' => $post->get_social_accounts($context['footer_social_menu'] ?: []),
            'utm_medium' => 'gf-share',
        ];

        if (
            isset($current_confirmation['p4_gf_share_text_override'])
            && $current_confirmation['p4_gf_share_text_override']
        ) {
            $confirmation_fields['share_text'] = $current_confirmation['p4_gf_share_text_override'];
        }

        if (
            isset($current_confirmation['p4_gf_share_url_override'])
            && $current_confirmation['p4_gf_share_url_override']
        ) {
            $confirmation_fields['share_url'] = $current_confirmation['p4_gf_share_url_override'];
        }

        return Timber::compile([ 'gravity_forms_confirmation.twig' ], $confirmation_fields);
    }

    /**
     * Add event for GA4 on successful stripe event
     *
     * @param mixed $entry The successful entry.
     * @param mixed $form The form details.
     *
     * @return array Event array
     */
    public function p4_gf_stripe_custom_success_event($entry, $form): array
    {
        $allowed = ['Paid', 'Active', 'Approved', 'Processing', 'Pending'];

        // GF sometimes returns a Processing entry with data missing
        // while the entry has been successfully retrieved in a previous step.
        // We manually update the entry in that case.
        if (
            (empty($entry['payment_status']) || $entry['payment_status'] === 'Processing')
            && !empty($this->current_entry)
        ) {
            $entry = $this->current_entry;
        }

        if (
            !isset($entry['payment_status'])
            || !in_array($entry['payment_status'], $allowed)
        ) {
            return [];
        }

        $tType = [
            '1' => 'one_time_donation',
            '2' => 'recurring_donation',
        ];

        $tCategory = [
            '1' => 'Single',
            '2' => 'Recurring',
        ];

        $tVariant = [
            'month' => 'Monthly',
            'week' => 'Weekly',
        ];

        $feed = gf_stripe()->get_payment_feed($entry);
        $donation_variant = $feed['meta']['billingCycle_unit'] ?? null;
        $item_variant = 'One-time';

        if ($donation_variant) {
            $item_variant = $tVariant[$donation_variant];
        }

        $event = [
            'event' => 'purchase',
            'ecommerce' => [
                'transaction_id' => $entry['transaction_id'],
                'affiliation' => 'Greenpeace',
                'value' => $entry['payment_amount'],
                'currency' => $entry['currency'],
                'payment_type' => 'Credit Card',
                'items' => [
                    [
                        'item_id' => $entry['transaction_type'] ?? null,
                        'item_name' => $form['title'] ?? null,
                        'item_brand' => 'Greenpeace',
                        'item_category' => $tCategory[$entry['transaction_type']],
                        'item_variant' => $item_variant,
                        'price' => $entry['payment_amount'],
                        'quantity' => 1,
                    ],
                ],
            ],
        ];

        return $event;
    }

    /**
     *
     * Add CSS classes to some Gravity Forms fields: checkboxes and radio buttons.
     *
     * @param string $classes The existing field classes.
     * @param object $field The field.
     *
     * @return string The updated field classes.
     */
    public function p4_gf_custom_field_class(string $classes, object $field): string
    {
        if ('checkbox' === $field->type || 'radio' === $field->type || 'consent' === $field->type) {
            $classes .= ' custom-control';
        }
        return $classes;
    }

    /**
    *
    * Enforces Ajax submission on all forms.
    *
    * @param array $form_args form arguments when adding it to a page/post.
    *
    * @return array The updated form arguments.
    */
    public function p4_gf_enforce_ajax(array $form_args): array
    {
        $form_args['ajax'] = true;

        return $form_args;
    }

    /**
     * Purges the caches of pages that contain a form.
     *
     * @param array $form The form to look for when purging page caches
     * @param bool $is_new Form is new
     *
     */
    public function p4_gf_clear_page_caches(array $form, bool $is_new = false): void
    {
        if ($is_new) {
            return;
        }

        /**
         * Filter hook to change post types that are cleared from cache if they contain a form that has changed
         *
         * @param array $post_types Array of post types to consider for cache clearing after form update
         */
        $post_types = apply_filters('planet4_form_cache_purge_post_types', [ 'page', 'post', 'campaign', 'p4_action' ]);

        // Get IDs of posts that contain the forms
        $posts_to_clear = $this->get_posts_by_gf_id($form['id'], $post_types);

        // Get IDs of synced patterns that contain the forms
        $synced_patterns = $this->get_posts_by_gf_id($form['id'], [ 'wp_block' ]);

        foreach ($synced_patterns as $block_id) {
            // Find posts that contain the synced patterns
            $args = [
                's' => '<!-- wp:block {"ref":' . $block_id . '}',
                "numberposts" => - 1,
                'post_type' => $post_types,
                'post_status' => [ 'publish', 'private' ],
            ];

            $posts = get_posts($args);

            foreach ($posts as $post) {
                $posts_to_clear[] = $post->ID;
            }
        }

        $posts_to_clear = array_unique($posts_to_clear);

        foreach ($posts_to_clear as $post_id) {
            // Updating the post triggers clearing the cache without making any changes to the post itself.
            wp_update_post([
                'ID' => $post_id,
            ], false, true);
        }
    }

    /**
     * Client side dynamic population of form fields
     *
     * @param array|bool $form The different form fields present
     *
     * @return mixed
     */
    public function p4_client_side_gravityforms_prefill($form): array
    {
        if (!is_array($form)) {
            return $form;
        }

        $supported_field_types = ['GF_Field_Hidden'];

        $gf_frontend_populate = [];

        foreach ($form['fields'] as $field) {
            if (!$field->allowsPrepopulate || !in_array(get_class($field), $supported_field_types)) {
                continue;
            }

            // The object doesn't contain the id attribute of the html field in the frontend.
            // Workaround: Render the field and grab the ID from the resulting HTML.
            $dom = new DOMDocument();
            $dom->loadHTML($field->get_field_input($form));

            $dom_field = $dom->getElementsByTagName('input');

            $field_id = $dom_field[0]->getAttribute('id');

            $gf_frontend_populate[] = [
                'parameter' => $field->inputName,
                'fieldId' => $field_id,
                'fieldType' => get_class($field),
            ];
        }

        $gf_frontend_config = [
            "populate" => $gf_frontend_populate,
            "formData" => [
                "formID" => $form['id'],
                "gGoal" => ($form['p4_gf_type'] ?? self::DEFAULT_GF_TYPE),
                "formTitle" => $form['title'],
                "formPlugin" => "Gravity Form",
            ],
        ];

        $theme_dir = get_template_directory_uri();
        $gf_client_side_file = $theme_dir . '/assets/build/gravityforms-client-side.js';

        wp_enqueue_script(
            'p4-gf-client-side',
            $gf_client_side_file,
            array(),
            file_exists($gf_client_side_file) ? filemtime($gf_client_side_file) : false,
            true
        );

        wp_localize_script('p4-gf-client-side', 'p4GfClientSideConfig', $gf_frontend_config);

        return $form;
    }

    /**
     * Find all posts that contain a form.
     *
     * @param int $form_id The ID of the form to look for
     * @param array $post_types Post types that are searched
     *
     */
    private function get_posts_by_gf_id(int $form_id, array $post_types = [ 'post', 'page' ]): array
    {
        $args = [
            's' => '<!-- wp:gravityforms/form ',
            "numberposts" => - 1,
            'post_type' => $post_types,
            'post_status' => [ 'publish', 'private' ],
        ];

        $posts = get_posts($args);

        $post_ids = [];

        foreach ($posts as $post) {
            $blocks = parse_blocks(get_the_content(null, null, $post));

            $gf_blocks = $this->find_nested_blocks('gravityforms/form', $blocks);

            foreach ($gf_blocks as $gf_block) {
                if (intval($gf_block['attrs']['formId']) === $form_id) {
                    $post_ids[] = $post->ID;

                    break;
                }
            }
        }

        return $post_ids;
    }

    /**
     * Recursively search blocks array for block with $block_name and return as flat array.
     *
     * @param string $block_name Block name to find
     * @param array $blocks Blocks to search for nested blocks
     *
     */
    private function find_nested_blocks(string $block_name, array $blocks): array
    {
        $matching_blocks = [];

        foreach ($blocks as $block) {
            if (isset($block['blockName']) && $block['blockName'] === $block_name) {
                $matching_blocks[] = $block;
            }

            if (!is_array($block['innerBlocks'])) {
                continue;
            }

            $inner_blocks = $this->find_nested_blocks($block_name, $block['innerBlocks']);

            if (count($inner_blocks) <= 0) {
                continue;
            }

            $matching_blocks = array_merge($matching_blocks, $inner_blocks);
        }

        return $matching_blocks;
    }

    /**
     * Redirect using Javascript after form submission instead of sending a header.
     * Makes it possible to send tag manager events before redirecting.
     *
     * @param string|array $confirmation The default confirmation message.
     * @param mixed        $form         The form properties.
     * @param mixed        $entry        The current entry.
     *
     * @return string|array The custom confirmation message.
     */
    public function p4_gf_custom_confirmation_redirect($confirmation, $form, $entry)
    {
        GFCommon::log_debug(__METHOD__ . '(): running.');
        if (isset($confirmation['redirect'])) {
            $url = esc_url_raw($confirmation['redirect']);
            GFCommon::log_debug(__METHOD__ . '(): Redirect to URL: ' . $url);

            $html = sprintf(
                // translators: %s = Redirection url variable.
                __(
                    'Thank you! Please <a href="%s">click here</a> if you are not redirected within a few seconds.',
                    'planet4-master-theme'
                ),
                $url,
            );

            // Get the tag manager data layer ID from master theme settings
            $options = get_option('planet4_options');
            $gtm_id = $options['google_tag_manager_identifier'];
            $is_stripe_feed = false;
            if (function_exists('gf_stripe')) {
                $is_stripe_feed = gf_stripe()->has_feed($form['id']);
            }
            $purchase_event = $is_stripe_feed ? $this->p4_gf_stripe_custom_success_event($entry, $form) : [];

            $email_hash = $this->p4_gf_get_email_hash($form, $entry);

            $event_parameters = [
                "event" => "formSubmission",
                "formID" => $form['id'],
                "formPlugin" => "Gravity Form",
                "gGoal" => ($form['p4_gf_type'] ?? self::DEFAULT_GF_TYPE),
                "formTitle" => $form['title'],
                "gpUserId" => $email_hash,
                "eventTimeout" => 2000,
            ];

            // Filter hook to change the event parameters
            $event_parameters = apply_filters('planet4_datalayer_form_submission', $event_parameters, $form, $entry);


            $script = '<script type="text/javascript">
                if (typeof googleTagManagerData !== "undefined" && googleTagManagerData?.google_tag_value) {
                    window.dataLayer = window.dataLayer || [];

                    let push_data = {
                        "eventCallback" : function(id) {
                            // There might be multiple gtm containers, make sure we only redirect for our main container
                            if ( id == "' . $gtm_id . '") {
                                window.top.location.href = "' . $url . '";
                            }
                        }
                    };

                    const event_parameters = ' . json_encode($event_parameters) . '
                    const purchase_event = ' . json_encode($purchase_event) . '

                    push_data = Object.assign(push_data, event_parameters);

                    window.dataLayer.push(push_data);
                    // One time payment returns Paid status, recurring payments returns Active status
                    if ("' . $is_stripe_feed . '" == true) {
                        if (
                        "' . $this->is_payment_successful . '" == "Paid" ||
                            "' . $this->is_payment_successful . '" == "Active"
                        ) {
                            window.dataLayer.push(purchase_event);
                        }
                    }
                    const gp_user_id_event = new CustomEvent("gp_user_id_set", {"detail":
                            {"gp_user_id": "' . $email_hash . '" }});
                    document.dispatchEvent(gp_user_id_event);
                } else {
                    // Redirect latest after two seconds.
                    // This is a failsafe in case the request to tag manager is blocked.
                    setTimeout(function() {
                        window.top.location.href = "' . $url . '";
                    }, 2000);
                }
                </script>';

            $confirmation = '<p>' . $html . '</p>' . $script;
        }

        return $confirmation;
    }

    /**
     * Enable default meta parameters for Gravity forms.
     *
     * @param array $meta Associative array containing all form properties.
     *
    */
    public function p4_gf_enable_default_meta_settings(array $meta): array
    {
        $meta['personalData']['preventIP'] = true;
        $meta['personalData']['retention']['policy'] = 'delete';
        $meta['personalData']['retention']['retain_entries_days'] = 90;
        $meta['personalData']['exportingAndErasing']['enabled'] = true;
        return $meta;
    }

    /**
     * Changes to the lifecycle stage in forms
     * Changing how the lifecycle stage for CRM records is handled in forms.
     * Instead of being a form field, the lifecycle stage will be included in the
     * form settings in a new selectedExternalOptions field.
     * More info: https://developers.hubspot.com/changelog/changes-to-the-lifecycle-stage-in-forms
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingAnyTypeHint
     * @param mixed $hs_form hubspot form.
     *
     * @return mixed $hs_form hubspot form.
    */
    public function p4_gf_hb_form_object_pre_save_feed($hs_form)
    {
        if (! empty($hs_form['selectedExternalOptions'])) {
            $lifecyclestage_id = '';
            foreach ($hs_form['selectedExternalOptions'] as $key => $external_option) {
                if (rgar($external_option, 'propertyName') !== 'lifecyclestage') {
                    continue;
                }

                if (rgar($external_option, 'objectTypeId') === '0-1') {
                    $lifecyclestage_id = rgar($external_option, 'id');
                    continue;
                }

                if (rgar($external_option, 'objectTypeId') === '0-2') {
                    $lifecyclestage_id = '';
                    break;
                }
            }

            if (! empty($lifecyclestage_id)) {
                $hs_form['selectedExternalOptions'][] = array(
                    'referenceType' => 'PIPELINE_STAGE',
                    'objectTypeId' => '0-2',
                    'propertyName' => 'lifecyclestage',
                    'id' => $lifecyclestage_id,
                );
            }
        }

        return $hs_form;
    }
}
