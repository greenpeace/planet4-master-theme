<?php

/**
 * Cookies block class.
 */

 namespace P4\MasterTheme\Blocks;

 /**
  * Class Cookies
  *
  * @package P4\MasterTheme\Blocks
  */
class Cookies extends BaseBlock
{
    /** @const string BLOCK_NAME */
    public const BLOCK_NAME = 'cookies';

    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [
                // todo: Remove when all content is migrated.
                'render_callback' => [ self::class, 'render_frontend' ],
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'necessary_cookies_name' => [
                        'type' => 'string',
                    ],
                    'necessary_cookies_description' => [
                        'type' => 'string',
                    ],
                    'all_cookies_name' => [
                        'type' => 'string',
                    ],
                    'all_cookies_description' => [
                        'type' => 'string',
                    ],
                    'analytical_cookies_name' => [
                        'type' => 'string',
                    ],
                    'analytical_cookies_description' => [
                        'type' => 'string',
                    ],
                ],
            ]
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
}
