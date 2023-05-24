<?php

/**
 * GuestBook block class.
 *
 * @package P4GBKS
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
}
