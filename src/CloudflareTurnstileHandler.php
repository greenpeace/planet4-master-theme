<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Settings\CloudflareTurnstile;

/**
 * Handles Cloudflare Turnstile integration for WordPress comment forms.
 *
 * @link https://developers.cloudflare.com/turnstile/
 *
 */
class CloudflareTurnstileHandler
{
    private const BASE_URL = 'https://challenges.cloudflare.com/turnstile/v0/';
    private const API_URL = self::BASE_URL . 'api.js';
    private const SITE_VERIFY_URL = self::BASE_URL . 'siteverify';

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!CloudflareTurnstile::get_option() || !defined('TURNSTILE_SITE_KEY') || !defined('TURNSTILE_SECRET_KEY')) {
            return;
        }

        // Prevent Akismet from firing before Turnstile.
        remove_filter('preprocess_comment', 'akismet_auto_check_comment', 1);

        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_filter('preprocess_comment', [$this, 'validate_token']);
    }

    /**
     * Enqueues the Cloudflare Turnstile client-side script.
     * Enqueues the script to render the Cloudflare Turnstile.
     * Passes the site key to the render script.
     *
     * @link https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#1-add-the-turnstile-script
     */
    public function enqueue_scripts(): void
    {
        wp_enqueue_script(
            'turnstile',
            self::API_URL,
            array(),
            null,
            true
        );
        wp_enqueue_script(
            'turnstile-render-script',
            get_template_directory_uri() . '/assets/build/turnstileRender.js',
            ['turnstile'],
            Loader::theme_file_ver('/assets/build/turnstileRender.js'),
            true
        );
        wp_localize_script(
            'turnstile-render-script',
            'TurnstileConfig',
            ['sitekey' => TURNSTILE_SITE_KEY]
        );
    }

    /**
     * Validates the submitted Turnstile token during comment submission.
     * If validation fails, logs the error via Sentry if available.
     *
     * @param array $commentdata    Data about the comment sent by a user.
     * @link https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#basic-validation-examples
     * @link https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#error-codes
     */
    public function validate_token(array $commentdata): mixed
    {
        if (empty($_POST['cf-turnstile-response'])) {
            wp_die(__('Error: Please complete the captcha.', 'planet4-blocks'));
        }

        $secret_key = TURNSTILE_SECRET_KEY;
        $token = $_POST['cf-turnstile-response'] ?? '';
        $remoteip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

        $validation = $this->validate_turnstile($token, $secret_key, $remoteip);

        if ($validation['success']) {
            // If Turnstile passes, let Akismet analyze comment for spam.
            if (function_exists('akismet_auto_check_comment')) {
                $commentdata = akismet_auto_check_comment($commentdata);
            }
            return $commentdata;
        }

        $errors = $validation['error-codes'] ?? ['unknown-error'];
        $msg = 'Turnstile validation failed: ' . implode(', ', $errors);

        if (function_exists('\Sentry\captureMessage')) {
            \Sentry\captureMessage($msg);
        }

        wp_die(__('Error: Captcha verification failed. Please try again.', 'planet4-blocks'));
        return false;
    }

    /**
     * Validates a Turnstile token with Cloudflare's siteverify API.
     *
     * @param string      $token    The token returned from the client-side widget.
     * @param string      $secret   The secret key for the site.
     * @param string|null $remoteip The user's IP address (optional).
     * @link https://developers.cloudflare.com/turnstile/get-started/server-side-validation/#basic-validation-examples
     * @return array<string,mixed> The decoded JSON response from the API.
     */
    private function validate_turnstile(string $token, string $secret, ?string $remoteip = null): array
    {
        $body = [
            'secret' => $secret,
            'response' => $token,
        ];

        if ($remoteip) {
            $body['remoteip'] = $remoteip;
        }

        $response = wp_remote_post(self::SITE_VERIFY_URL, [
            'body' => $body,
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'error-codes' => ['internal-error']];
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);

        return is_array($json) ? $json : ['success' => false, 'error-codes' => ['invalid-json']];
    }
}
