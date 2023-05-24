<?php

/**
 * Base block class.
 *
 * @package P4\MasterTheme
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class BaseBlock
 *
 * @package P4\MasterTheme\Blocks
 */
abstract class BaseBlock
{
    public const NAMESPACE = 'planet4-blocks';

    /**
     * Get all the data that will be needed to render the block correctly.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The data to be passed in the View.
     */
    abstract public function prepare_data(array $fields): array;

    /**
     * @param array $attributes Block attributes.
     *
     * @return mixed
     */
    public function render(array $attributes)
    {

        $data = $this->prepare_data($attributes);

        \Timber::$locations = '/block_templates/blocks';

        $block_output = \Timber::compile(static::BLOCK_NAME . '.twig', $data);

        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
        $empty_message = defined('static::EMPTY_MESSAGE') ? __(static::EMPTY_MESSAGE, 'planet4-blocks') :
            "Block content is empty. Check the block's settings or remove it.";

        // Return empty string if rendered output contains only whitespace or new lines.
        // If it is a rest request from editor/admin area, return a message that block has no content.
        $empty_content = (defined('REST_REQUEST') && REST_REQUEST) ?
            '<div class="EmptyMessage">' . $empty_message . '</div>' : '';

        return ctype_space($block_output) ? $empty_content : $block_output;
    }

    /**
     * Outputs an error message.
     *
     * @param string $message Error message.
     */
    public function render_error_message(string $message): void
    {
        // Ensure only editors see the error, not visitors to the website.
        if (! current_user_can('edit_posts')) {
            return;
        }

        \Timber::render(
            'block_templates/block-error-message.twig',
            [
                'category' => __('Error', 'planet4-blocks'),
                'message' => $message,
            ]
        );
    }

    /**
     * Update the attributes of a block to the latest version.
     * It returns an array with the new version of the block attributes.
     * PHPCS does not allow me to add the return type if there is no return statement, but here we always throw an
     * exception, so adding a return after triggers another CS rule. Disabling the violated rule,
     * Squiz.Commenting.FunctionComment.InvalidNoReturn, is not working in the doc comment.
     *
     * @param array $fields The old version of the block attributes.
     * @throws NotImplemented If no implementation is given by the subclass.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public static function update_data(array $fields): void
    {
        throw new NotImplemented('Method update_data is not implemented for ' . static::class);
    }
    //phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Converts the hy-phe-na-ted block name into CamelCase.
     */
    public static function get_camelized_block_name(): string
    {
        return str_replace('-', '', ucwords(static::BLOCK_NAME, '-'));
    }

    /**
     * Returns the block name with its namespace prefix, e.g.: planet4-blocks/accordion.
     */
    public static function get_full_block_name(): string
    {
        return static::NAMESPACE . '/' . static::BLOCK_NAME;
    }

    /**
     * Renders a `div` with the block's attributes in a `data-` attribute to be handled in the frontend.
     *
     * @param array $attributes The block's attributes.
     *
     * @return string.
     */
    public static function render_frontend(array $attributes): string
    {
        $json = wp_json_encode([ 'attributes' => $attributes ]);

        return '<div data-render="' . self::get_full_block_name() .
            '" data-attributes="' . htmlspecialchars($json) . '"></div>';
    }
}
