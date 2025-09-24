<?php

namespace P4\MasterTheme;

// https://developers.cloudflare.com/turnstile/get-started/
class CloudflareTurnstileHandler
{
    private const API_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
    private const SITE_VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        $features = get_option('planet4_features');
        $use_turnstile = isset($features['cloudflare_turnstile']) ? $features['cloudflare_turnstile'] : null;

        if ("on" !== $use_turnstile) {
            return;
        }

        // if (!defined('TURNSTILE_SECRET_KEY') || !defined ('TURNSTILE_SITE_KEY')) {
        //     return;
        // }

        // add_action('wp_enqueue_scripts', [$this, 'mytheme_enqueue_scripts']);
        // add_filter('preprocess_comment', [$this, 'maybe_validate_token']);
        add_action('comment_form_after_fields', [$this, 'render_widget']);
        add_action('comment_form_logged_in_after', [$this, 'render_widget']);
    }

    public function mytheme_enqueue_scripts() {
        wp_enqueue_script(
            'turnstile',
            self::API_URL,
            array(),
            null,
            true
        );
    }

    public function render_widget() {
        ?>
        <div class="cf-turnstile" data-sitekey="<?php echo "TURNSTILE_SITE_KEY"; ?>"></div>
        <?php
    }

    public function maybe_validate_token()
    {
        if (!isset($_POST['cf-turnstile-response'])) {
            return;
        }

        $secret_key = TURNSTILE_SECRET_KEY;
        $token = $_POST['cf-turnstile-response'] ?? '';
        $remoteip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

        $validation = $this->validateTurnstile($token, $secret_key, $remoteip);

        if (!$validation['success']) {
            $errors = $validation['error-codes'] ?? ['unknown-error'];
            $msg    = 'Turnstile validation failed: ' . implode(', ', $errors);

            if (function_exists('\Sentry\captureMessage')) {
                \Sentry\captureMessage($msg);
            }
        }
    }

    private function validateTurnstile($token, $secret, $remoteip = null)
    {
        $data = [
            'secret' => $secret,
            'response' => $token
        ];

        if ($remoteip) {
            $data['remoteip'] = $remoteip;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents(self::SITE_VERIFY_URL, false, $context);

        if ($response === FALSE) {
            return ['success' => false, 'error-codes' => ['internal-error']];
        }

        return json_decode($response, true);
    }
}
