<?php

/**
 * Timeline block class
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

use P4\MasterTheme\Features\Dev\NewTimelineBlock;

/**
 * Class Timeline
 *
 * @package P4\MasterTheme\Blocks
 * @since 0.1
 */
class Timeline extends BaseBlock
{
    /**
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'timeline';

    /**
     * @const string TIMELINE_JS_VERSION.
     */
    public const TIMELINE_JS_VERSION = '3.8.12';

    /**
     * @const string TIMELINE_LIB.
     */
    public const TIMELINE_LIB = 'https://cdn.knightlab.com/libs/timeline3/';

    /**
     * @const string TIMELINE_JS_SCRIPT.
     */
    public const TIMELINE_JS_SCRIPT = 'timeline-js';

    /**
     * @const string TIMELINE_CSS_SCRIPT.
     */
    public const TIMELINE_CSS_SCRIPT = 'timeline-css';

    /**
     * Timeline constructor.
     */
    public function __construct()
    {
        $this->register_timeline_block();
    }

    /**
     * Register block
     */
    public function register_timeline_block(): void
    {
        // - Register the block for the editor
        // in the PHP side.
        register_block_type(
            self::get_full_block_name(),
            [
                'editor_script' => 'planet4-blocks',
                // todo: Remove when all content is migrated.
                'render_callback' => [ self::class, 'hydrate_frontend' ],
                'attributes' => [
                    'timeline_title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'google_sheets_url' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'language' => [
                        'type' => 'string',
                        'default' => 'en',
                    ],
                    'timenav_position' => [
                        'type' => 'string',
                        'default' => 'bottom',
                    ],
                    'start_at_end' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
            ]
        );

        if (!NewTimelineBlock::is_active()) {
            wp_register_script(
                self::TIMELINE_JS_SCRIPT,
                self::TIMELINE_LIB . self::TIMELINE_JS_VERSION . '/js/timeline-min.js',
                [],
                self::TIMELINE_JS_VERSION,
                true
            );

            wp_register_style(
                self::TIMELINE_CSS_SCRIPT,
                self::TIMELINE_LIB . self::TIMELINE_JS_VERSION . '/css/timeline.css',
                [],
                self::TIMELINE_JS_VERSION
            );
        }

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Frontend script
     */
    public static function enqueue_frontend_script(): void
    {
        $deps = ['planet4-blocks-theme-script'];
        $deps = wp_script_is(self::TIMELINE_JS_SCRIPT, 'enqueued') ?
            array_push($deps, self::TIMELINE_JS_SCRIPT) : $deps;

        wp_enqueue_script(
            static::get_full_block_name() . '-script',
            static::get_url_path() . 'Script.js',
            $deps,
            \P4\MasterTheme\Loader::theme_file_ver(static::get_rel_path() . 'Script.js'),
            true
        );
    }

    /**
     * Frontend style
     */
    public static function enqueue_frontend_style(): void
    {
        wp_enqueue_style(
            static::get_full_block_name() . '-style',
            static::get_url_path() . 'Style.min.css',
            [self::TIMELINE_CSS_SCRIPT],
            \P4\MasterTheme\Loader::theme_file_ver(static::get_rel_path() . 'Style.min.css'),
        );
    }

    /**
     * Required by BaseBlock.
     *
     * @param array $attributes This is the array of fields of this block.
     * @return array The data to be passed in the View.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $attributes): array
    {
        return [];
    }
}
