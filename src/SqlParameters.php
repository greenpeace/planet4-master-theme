<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Exception\SqlInIsEmpty;

/**
 * Holds the parameter values and returns a placeholder string while constructing a SQL statement with input.
 * That way the order of the parameters will correspond to the order of the placeholders automatically, so we can just
 * pass `$this->values` to `$wbdb->prepare`.
 *
 * Because the placeholders are added with a number it shouldn't matter in which order the sql is constructed.
 * I.e. the following should work as intended.
 * ```$sqlEnd = "WHERE field = " . $params->string('foo');
 *    $sqlStart = "SELECT * FROM " . $params->object('myTable');
 *    $wpdb->prepare($sqlStart . $sqlEnd, $params->getValues());
 */
class SqlParameters
{
    /**
     * @var mixed[] The values of the parameters in the order they were added.
     */
    private array $values = [];

    /**
     * Add a parameter for a SQL identifier (mainly table but works for other things too).
     *
     * @param string $name The name of the object.
     *
     * @return string Numbered placeholder.
     */
    public function identifier(string $name): string
    {
        $this->values[] = $name;

        $n = count($this->values);

        return "`%$n\$s`";
    }

    /**
     * Add a parameter for an integer.
     *
     * @param int $value The value the parameter has.
     *
     * @return string Numbered placeholder.
     */
    public function int(int $value): string
    {
        $this->values[] = $value;

        $n = count($this->values);

        return "%$n\$d";
    }

    /**
     * Add a parameter for a string.
     *
     * @param string $value The value the parameter has.
     *
     * @return string Numbered placeholder.
     */
    public function string(string $value): string
    {
        $this->values[] = $value;

        $n = count($this->values);

        return "'%$n\$s'";
    }

    /**
     * Add int parameters for an IN query.
     *
     * @param int[] $values The values for the IN statement.
     *
     * @return string Concatenated numbered placeholders.
     * @throws SqlInIsEmpty If $values is an empty array.
     */
    public function int_list(array $values): string
    {
        if (empty($values)) {
            throw new SqlInIsEmpty(
                'An IN query does not work if there are no values, please check before passing as an argument.'
            );
        }
        $params = [];
        foreach ($values as $value) {
            $params[] = $this->int($value);
        }

        return ' (' . implode(',', $params) . ') ';
    }

    /**
     * Add string parameters for an IN query.
     *
     * @param string[] $values The values for the IN statement.
     *
     * @return string Concatenated numbered placeholders.
     * @throws SqlInIsEmpty If $values is an empty array.
     */
    public function string_list(array $values): string
    {
        if (empty($values)) {
            throw new SqlInIsEmpty(
                'An IN query does not work if there are no values, please check before passing as an argument.'
            );
        }
        $params = [];
        foreach ($values as $value) {
            $params[] = $this->string($value);
        }

        return ' (' . implode(',', $params) . ') ';
    }

    /**
     * Get all values in the order they were added.
     *
     * @return mixed[] All values.
     */
    public function get_values(): array
    {
        return $this->values;
    }
}
