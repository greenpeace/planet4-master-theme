<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

class Sendgrid extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'sendgrid_api';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Send email with Sendgrid', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Send all emails from this instance using Sendgrid API.',
            'planet4-master-theme-backend'
        );
    }
}
