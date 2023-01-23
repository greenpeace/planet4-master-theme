<?php

namespace P4\MasterTheme\Notifications;

use BracketSpace\Notification\Defaults\Field\CodeEditorField;

/**
 * In order to use mrkdwn, we need to disable html sanitization on the code editor field.
 */
class MrkDwnField extends CodeEditorField
{
    /**
     * Disable sanitization since we're not dealing with HTML. We're sending this to Slack and it's interpreted as
     * mrkdwn, which should provide a safe environment.
     *
     * @param mixed $value The mrkdwn content.
     *
     * @return mixed The mrkdwn content.
     */
    public function sanitize($value)
    {
        return $value;
    }
}
