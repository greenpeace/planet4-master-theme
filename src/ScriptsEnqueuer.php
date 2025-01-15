<?php

namespace P4\MasterTheme;

/**
 * Class ScriptsEnqueuer.
 *
 * This class is used to enqueue scripts.
 */
class ScriptsEnqueuer
{
    /**
     * ScriptsEnqueuer constructor.
     */
    public function __construct()
    {
        add_action('enqueue_share_butttons_script', [$this, 'share_buttons_script']);
        add_action('enqueue_toggle_comment_submit_script', [$this, 'toggle_comment_submit_script']);
    }

    public function share_buttons_script()
    {
        wp_enqueue_script(
            'share-buttons-script',
            get_template_directory_uri() . '/assets/build/shareButtons.js',
            [],
            null,
            true
        );
    }
    public function toggle_comment_submit_script()
    {
        wp_enqueue_script(
            'toggle_comment_submit_script',
            get_template_directory_uri() . '/assets/build/toggleCommentSubmit.js',
            [],
            null,
            true
        );
    }
}
