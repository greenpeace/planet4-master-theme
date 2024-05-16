<?php

namespace P4\MasterTheme;

use PHPMailer\PHPMailer\PHPMailer;
use WP_Error;

class Sendgrid
{
    public const HOST = 'smtp.sendgrid.net';
    public const USERNAME = 'apikey';
    public const SENDER = 'noreply@act.greenpeace.org';
    public const PORT_TLS = 587; // 25, 2525

    public static function hooks(): void
    {
        add_action('phpmailer_init', [self::class, 'phpmailer_init'], 10, 1);
        add_action('wp_mail_failed', [self::class, 'phpmailer_error'], 10, 1);
    }

    public static function phpmailer_init(PHPMailer $phpmailer): void
    {
        if (!defined('SENDGRID_API_KEY') || empty(SENDGRID_API_KEY)) {
            if (function_exists('\Sentry\captureMessage')) {
                \Sentry\captureMessage('No Sendgrid API key found.');
            }
            return;
        }

        if (defined('SENDGRID_NRO_API_KEY') && !empty(SENDGRID_NRO_API_KEY)) {
            $sendgrid_nro_api_key = SENDGRID_NRO_API_KEY;
        }

        $phpmailer->Host = self::HOST;
        $phpmailer->Username = self::USERNAME;
        $phpmailer->Password = $sendgrid_nro_api_key ?? SENDGRID_API_KEY;
        // Filter hook to change the sendgrid From address.
        $phpmailer->From = apply_filters('planet4_sendgrid_sender', self::SENDER);

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
