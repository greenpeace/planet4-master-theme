<?php

/**
 * Slack Carrier.
 */

namespace P4\MasterTheme\Notifications;

use BracketSpace\Notification\Interfaces\Triggerable;
use BracketSpace\Notification\Abstracts;
use Maknz\Slack\Client;
use P4\MasterTheme\Settings;

/**
 * Slack Carrier.
 */
class Slack extends Abstracts\Carrier
{
    /**
     * Carrier icon.
     *
     * @var string SVG.
     */
    public string $icon = '<svg enable-background="new 0 0 2447.6 2452.5" viewBox="0 0 2447.6 2452.5" xmlns="http://www.w3.org/2000/svg"><g clip-rule="evenodd" fill-rule="evenodd"><path d="m897.4 0c-135.3.1-244.8 109.9-244.7 245.2-.1 135.3 109.5 245.1 244.8 245.2h244.8v-245.1c.1-135.3-109.5-245.1-244.9-245.3.1 0 .1 0 0 0m0 654h-652.6c-135.3.1-244.9 109.9-244.8 245.2-.2 135.3 109.4 245.1 244.7 245.3h652.7c135.3-.1 244.9-109.9 244.8-245.2.1-135.4-109.5-245.2-244.8-245.3z" fill="#36c5f0"/><path d="m2447.6 899.2c.1-135.3-109.5-245.1-244.8-245.2-135.3.1-244.9 109.9-244.8 245.2v245.3h244.8c135.3-.1 244.9-109.9 244.8-245.3zm-652.7 0v-654c.1-135.2-109.4-245-244.7-245.2-135.3.1-244.9 109.9-244.8 245.2v654c-.2 135.3 109.4 245.1 244.7 245.3 135.3-.1 244.9-109.9 244.8-245.3z" fill="#2eb67d"/><path d="m1550.1 2452.5c135.3-.1 244.9-109.9 244.8-245.2.1-135.3-109.5-245.1-244.8-245.2h-244.8v245.2c-.1 135.2 109.5 245 244.8 245.2zm0-654.1h652.7c135.3-.1 244.9-109.9 244.8-245.2.2-135.3-109.4-245.1-244.7-245.3h-652.7c-135.3.1-244.9 109.9-244.8 245.2-.1 135.4 109.4 245.2 244.7 245.3z" fill="#ecb22e"/><path d="m0 1553.2c-.1 135.3 109.5 245.1 244.8 245.2 135.3-.1 244.9-109.9 244.8-245.2v-245.2h-244.8c-135.3.1-244.9 109.9-244.8 245.2zm652.7 0v654c-.2 135.3 109.4 245.1 244.7 245.3 135.3-.1 244.9-109.9 244.8-245.2v-653.9c.2-135.3-109.4-245.1-244.7-245.3-135.4 0-244.9 109.8-244.8 245.1 0 0 0 .1 0 0" fill="#e01e5a"/></g></svg>';

    /**
     * Used to register Carrier form fields.
     * Uses $this->add_form_field();.
     *
     * @throws \Exception Not sure which ones.
     */
    public function form_fields(): void
    {
        $body_field = new MrkDwnField(
            [
                'label' => __('Message', 'planet4-master-theme-backend'),
                'description' => '<a target="_blank" class="external-link" href="https://api.slack.com/reference/surfaces/formatting#basics">Learn about message formatting options</a>',
                'name' => 'body',
                'resolvable' => true,
                'settings' => [
                    'mode' => 'markdown',
                    'highlightFormatting' => true,
                    'lineNumbers' => true,
                ],
            ]
        );
        $this->add_form_field($body_field);
    }

    /**
     * Sends the notification.
     *
     * @param Triggerable $trigger trigger object.
     *
     */
    public function send(Triggerable $trigger): void
    {
        $webhook = planet4_get_option(Settings::SLACK_WEBHOOK);
        $client = new Client($webhook, [ 'link_names' => true ]);
        $message = $this->get_message($trigger);
        $client->send($message);
    }

    /**
     * Get the message to send.
     *
     * @param Triggerable $trigger What triggered this notification.
     *
     * @return mixed The message, for now just the body that was entered.
     */
    private function get_message(Triggerable $trigger)
    {
        return $this->data['body'];
    }
}
