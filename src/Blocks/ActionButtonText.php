<?php

/**
 * Action Button Text block class.
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

 use WP_Block;

/**
 * Class ActionButtonText
 *
 * @package P4\MasterTheme\Blocks
 */
class ActionButtonText extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'action-button-text';

    /**
     * ActionButtonText constructor.
     */
    public function __construct()
    {
        $this->register_action_button_text_block();
    }

    /**
     * Register ActionButtonText block.
     */
    public function register_action_button_text_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'render_callback' => [ $this, 'render_block' ],
            ]
        );
    }

    /**
     * Required by the `Base_Block` class.
     *
     * @param array $fields Unused, required by the abstract function.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }

    /**
     * @param  array    $attributes Block attributes.
     * @param  string   $content    Block default content.
     * @param  WP_Block $block      Block instance.
     * @return string   Returns the value for the field.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcs:disable Generic.Files.LineLength.MaxExceeded
    */
    public function render_block(array $attributes, string $content, WP_Block $block): string
    {
        $post_id = $block->context['postId'];
        $link = get_permalink($post_id);
        $meta = get_post_meta($post_id);
        $options = get_option('planet4_options');

        $action_btn = $options['take_action_covers_button_text'] ?? __('Take action', 'planet4-blocks');
        $action_acc_btn = __('Take action', 'planet4-blocks');

        $has_button_text = isset($meta['action_button_text']) && $meta['action_button_text'][0];
        $has_button_acc_text = isset($meta['action_button_accessibility_text']) && $meta['action_button_accessibility_text'][0];

        $button_text = $has_button_text ? $meta['action_button_text'][0] : $action_btn;
        $button_acc_text = $has_button_acc_text ? $meta['action_button_accessibility_text'][0] : $action_acc_btn;

        return '<a href="' . $link . '" class="btn btn-primary btn-small" aria-label="' . $button_acc_text . '">' . $button_text . '</a>';
    }
    // @phpcs:enable Generic.Files.LineLength.MaxExceeded
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
