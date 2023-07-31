<?php

namespace P4\MasterTheme;

use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;

class Sendgrid
{
    public const HOST = 'smtp.sendgrid.net';
    public const USERNAME = 'apikey';
    public const PORT_TLS = 587; // 25, 2525

    public static function hooks(): void
    {
        add_action('phpmailer_init', [self::class, 'phpmailer_init'], 10, 1);
        add_action('wp_mail_failed', [self::class, 'phpmailer_error'], 10, 1);
    }

    public static function phpmailer_init(PHPMailer $phpmailer): void
    {
        if (!defined('SENDGRID_API_KEY') && !defined('GF_SENDGRID_KEY')) {
            if (function_exists('\Sentry\captureMessage')) {
                \Sentry\captureMessage('No Sendgrid API key found.');
            }
            return;
        }

        $apiKey = defined('SENDGRID_API_KEY') ? SENDGRID_API_KEY : GF_SENDGRID_KEY;

        $phpmailer->Host = self::HOST;
        $phpmailer->Username = self::USERNAME;
        $phpmailer->Password = $apiKey;
        $phpmailer->From = 'fhernand@greenpeace.org';

        $phpmailer->IsSMTP();
        $phpmailer->Port = self::PORT_TLS;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];

        // $phpmailer->SMTPDebug = true;
    }

    public static function phpmailer_error(WP_Error $error): void
    {
        if (!function_exists('\Sentry\captureMessage')) {
            return;
        }
        \Sentry\captureMessage($error->get_error_message());
    }
}
