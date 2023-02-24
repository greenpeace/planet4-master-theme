<?php

namespace P4\MasterTheme;

use GFAPI;
use Timber\Timber;

/**
 * Class P4\MasterTheme\GravityFormsExtensions
 * The Gravity form plugin extension, use to add custom functionality like Planet4 form type.
 */
class GravityFormsExtensions
{
    public const DEFAULT_GF_TYPE = 'other';

    public const P4_GF_TYPES = [
        [
            'label' => 'Other',
            'value' => 'other',
        ],
        [
            'label' => 'Petition',
            'value' => 'petition',
        ],
        [
            'label' => 'Email Signup',
            'value' => 'email-signup',
        ],
        [
            'label' => 'Quiz/Poll',
            'value' => 'quiz-poll',
        ],
        [
            'label' => 'Email-to-target',
            'value' => 'email-to-target',
        ],
        [
            'label' => 'Contact',
            'value' => 'contact',
        ],
        [
            'label' => 'Survey',
            'value' => 'survey',
        ],
        [
            'label' => 'Feedback',
            'value' => 'feedback',
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
    ];

    /**
     * The constructor.
     */
    public function __construct()
    {
        $this->hooks();
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
    }

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

        $fields['p4_share_buttons']['fields'][] = [
            'type' => 'checkbox',
            'name' => 'p4_gf_share_platforms',
            'label' => __('Show share buttons below message', 'planet4-master-theme-backend'),
            'choices' => self::P4_SHARE_BUTTONS,
        ];

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
     *
     * Return custom confirmation message for Gravity Forms.
     *
     * @param string|array $confirmation The default confirmation message.
     * @param mixed        $form         The form properties.
     *
     * @return string The custom confirmation message.
     */
    public function p4_gf_custom_confirmation($confirmation, $form): string
    {
        // If the $confirmation object is an array, it means that it's a redirect page so we can directly use it.
        if (is_array($confirmation)) {
            return $confirmation;
        }

        $context = Timber::get_context();
        $post = Timber::query_post(false, Post::class);

        $current_confirmation = $form['confirmation'];

        $confirmation_fields = [
            'confirmation' => $confirmation,
            'share_platforms' => [
                'facebook' => $current_confirmation['facebook'] ?? true,
                'twitter' => $current_confirmation['twitter'] ?? true,
                'whatsapp' => $current_confirmation['whatsapp'] ?? true,
                'native' => $current_confirmation['native'] ?? true,
                'email' => $current_confirmation['email'] ?? true,
            ],
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

        // Get IDs of reusable blocks that contain the forms
        $reusable_blocks = $this->get_posts_by_gf_id($form['id'], [ 'wp_block' ]);

        foreach ($reusable_blocks as $block_id) {
            // Find posts that contain the reusable blocks
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
     * Find all posts that contain a form.
     *
     * @param int $form_id The ID of the form to look for
     * @param array $post_types Post types that are searched
     *
     * @return array
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
     * @return array
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
}
