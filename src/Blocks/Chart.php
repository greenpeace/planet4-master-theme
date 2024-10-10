<?php

/**
 * Chart block class.
 */

 namespace P4\MasterTheme\Blocks;

 /**
  * Class Chart
  *
  * @package P4\MasterTheme\Blocks
  */
class Chart extends BaseBlock
{
    /** @const string BLOCK_NAME */
    public const BLOCK_NAME = 'chart';

    /**
     * Chart constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'editor_script' => 'planet4-blocks',
                'attributes' => [
                    'dataUrl' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'dataType' => [
                        'type' => 'string',
                        'enum' => ['csv', 'json'],
                        'default' => '',
                    ],
                    'chartType' => [
                        'type' => 'string',
                        'enum' => ['bar', 'line', 'area', 'pie', 'donut'],
                        'default' => '',
                    ],
                    'width' => [
                        'type' => 'number',
                        'default' => 400,
                    ],
                    'height' => [
                        'type' => 'number',
                        'default' => 400,
                    ],
                    'axis' => [
                        'type' => 'object',
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
