<?php

/**
 * GuestBook block class.
 *
 * @package P4\MasterTheme;
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class GuestBook
 *
 * @package P4\MasterTheme\Blocks
 */
class GuestBook extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'guestbook';

    /**
     * GuestBook constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
        );

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Required by the `BaseBlock` class.
     *
     * @param array $fields Unused, required by the abstract function.
     *
     * @return array Array.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }
    //phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    public static function get_camelized_block_name(): string
    {
        return 'GuestBook';
    }
}
