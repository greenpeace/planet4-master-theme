<?php

namespace P4\MasterTheme\Migrations\Utils;

/**
 * One to one mappings from Shortcake (Shortcode UI) blocks to Planet 4 blocks.
 *
 * To migrate a new Shortcake block, add an entry to `get_all()`, keyed by the shortcode tag:
 *
 * - `block`      The name of the block replacing the shortcode.
 * - `attributes` Shortcode attribute => block attribute.
 *                Use dots for nested block attributes, e.g. `button.button_text`.
 * - `repeaters`  (optional) Numbered shortcode attributes grouped in a block array attribute:
 *                block array attribute => [
 *                    `max`        Highest number used by the shortcode attributes.
 *                    `attributes` Shortcode attribute, with `%d` for the number => item attribute.
 *                ]
 *                An item is added when at least one of its shortcode attributes has a value.
 * - `defaults`   (optional) Block attributes set on every migrated block.
 * - `formatters` (optional) Block or item attribute => callable formatting the shortcode value,
 *                e.g. `[self::class, 'line_breaks']` for textareas rendered as HTML.
 * - `markup`     (optional) Callable returning the block saved markup from its attributes,
 *                for blocks with a static `save`. Leave it out for dynamic blocks.
 */
class ShortcakeMappings
{
    /**
     * All the mappings, keyed by shortcode tag.
     */
    public static function get_all(): array
    {
        return [
            'shortcake_accordion_list' => [
                'block' => Constants::BLOCK_ACCORDION,
                'attributes' => [
                    'title' => 'title',
                    'paragraph' => 'description',
                ],
                'repeaters' => [
                    'tabs' => [
                        'max' => 20,
                        'attributes' => [
                            'title_accordion_%d' => 'headline',
                            'paragraph_accordion_%d' => 'text',
                        ],
                    ],
                ],
                'formatters' => [
                    'description' => [self::class, 'line_breaks'],
                    'text' => [self::class, 'line_breaks'],
                ],
                'markup' => [ShortcakeBlockMarkup::class, 'accordion'],
            ],
        ];
    }

    /**
     * Mappings for some shortcode tags only.
     *
     * @param string[] $shortcode_tags The shortcode tags.
     */
    public static function get(array $shortcode_tags): array
    {
        return array_intersect_key(self::get_all(), array_flip($shortcode_tags));
    }

    /**
     * Convert shortcode attributes to block attributes.
     *
     * @param array $mapping A mapping from `get_all()`.
     * @param array $shortcode_attrs The shortcode attributes.
     */
    public static function map_attributes(array $mapping, array $shortcode_attrs): array
    {
        $formatters = $mapping['formatters'] ?? [];
        $attrs = $mapping['defaults'] ?? [];

        foreach ($mapping['attributes'] ?? [] as $shortcode_attr => $block_attr) {
            $value = $shortcode_attrs[ strtolower($shortcode_attr) ] ?? '';
            if ('' === $value) {
                continue;
            }
            self::set_path($attrs, $block_attr, self::format($value, $formatters[ $block_attr ] ?? null));
        }

        foreach ($mapping['repeaters'] ?? [] as $block_attr => $repeater) {
            $items = [];
            for ($i = 1; $i <= $repeater['max']; $i++) {
                $item = [];
                foreach ($repeater['attributes'] as $shortcode_attr => $item_attr) {
                    $value = $shortcode_attrs[ strtolower(sprintf($shortcode_attr, $i)) ] ?? '';
                    if ('' === $value) {
                        continue;
                    }
                    self::set_path($item, $item_attr, self::format($value, $formatters[ $item_attr ] ?? null));
                }
                if (empty($item)) {
                    continue;
                }
                $items[] = $item;
            }
            $attrs[ $block_attr ] = $items;
        }

        return $attrs;
    }

    /**
     * Formatter: add `<br />` before line breaks, like `wpautop()` did when the shortcode was rendered.
     * Line breaks already following a `<br>` are left as they are.
     *
     * @param string $value The shortcode attribute value.
     */
    public static function line_breaks(string $value): string
    {
        return preg_replace('/(?<!<br>)(?<!<br\/>)(?<!<br \/>)(\r?\n)/i', '<br />$1', $value);
    }

    /**
     * Apply a formatter to a shortcode value.
     *
     * @param string        $value The shortcode attribute value.
     * @param callable|null $formatter The formatter.
     * @return mixed The formatted value.
     */
    private static function format(string $value, ?callable $formatter)
    {
        return $formatter ? $formatter($value) : $value;
    }

    /**
     * Set a value in an array, using dots for nested keys.
     *
     * @param array  $data The array to update.
     * @param string $path The key, e.g. `button.button_text`.
     * @param mixed  $value The value.
     */
    private static function set_path(array &$data, string $path, $value): void
    {
        $keys = explode('.', $path);
        $last = array_pop($keys);
        $current = &$data;
        foreach ($keys as $key) {
            if (!isset($current[ $key ]) || !is_array($current[ $key ])) {
                $current[ $key ] = [];
            }
            $current = &$current[ $key ];
        }
        $current[ $last ] = $value;
        unset($current);
    }
}
