<?php

namespace P4\MasterTheme;

use Timber\Timber;

/**
 * Class CommentFormCustomizer
 */
class CommentFormCustomizer
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_filter('comment_form_submit_field', [$this, 'gdpr_cc_comment_form_add_class'], 150, 1);
        add_filter('comment_form_default_fields', [$this, 'comment_form_replace_inputs']);
    }

    /**
     * Filter and add class to GDPR consent checkbox label after the GDPR fields appended to comment form submit field.
     *
     * @param string $submit_field The HTML content of comment form submit field.
     *
     * @return string HTML content of comment form submit field.
     */
    public function gdpr_cc_comment_form_add_class(string $submit_field): string
    {

        $pattern[0] = '/(for=["\']gdpr-comments-checkbox["\'])/';
        $replacement[0] = '$1 class="custom-control-description"';
        $pattern[1] = '/(id=["\']gdpr-comments-checkbox["\'])/';
        $replacement[1] = '$1 style="width:auto;"';
        $pattern[2] = '/id="gdpr-comments-compliance"/';
        $replacement[2] = 'id="gdpr-comments-compliance" class="custom-control"';

        $submit_field = preg_replace($pattern, $replacement, $submit_field);

        return $submit_field;
    }

    /**
     * Use different templates for the comment form fields (name and email).
     * Also remove the website field since we don't want to use it.
     *
     * @param array $fields The default fields of the comment form.
     *
     * @return array the new fields.
     */
    public function comment_form_replace_inputs(array $fields): array
    {

        $fields['author'] = Timber::compile('comment_form/author_field.twig');
        $fields['email'] = Timber::compile('comment_form/email_field.twig');
        if (isset($fields['url'])) {
            unset($fields['url']);
        }

        return $fields;
    }
}
