<?php

/**
 * Pattern search query parameters
 */

namespace P4\MasterTheme\BlockReportSearch\Pattern\Query;

/**
 * Parameter bag for Query interface
 *
 * @method self with_name(string[] $name)
 * @method self with_post_status(string[] $status)
 * @method self with_post_type(string[] $type)
 * @method self with_order(string[] $order)
 */
class Parameters
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    private $name;

    /**
     * @var string[]
     */
    private array $post_status;

    /**
     * @var string[]
     */
    private array $post_type;

    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    private $order;

    private const DEFAULT_POST_STATUS = [ 'publish', 'private' ];

    /**
     * Generate a query param object from an array.
     *
     * @param array $params The parameters.
     *
     * @return self Parameters
     */
    public static function from_array(array $params): self
    {
        $query = new self();

        foreach ($params as $field => $value) {
            if (null === $value) {
                continue;
            }

            $query = $query->with($field, $value);
        }

        return $query;
    }

    /**
     * Generate query param object from HTTP request.
     *
     * @param array $request The request parameters.
     *
     * @return self Parameters
     */
    public static function from_request(array $request): self
    {
        $name = $request['name'] ?? null;
        if ($name) {
            $name = is_array($name) ? $name : [ $name ];
        }

        return self::from_array(
            [
                'name' => $name,
                'post_status' => $request['post_status'] ?? self::DEFAULT_POST_STATUS,
                'post_type' => $request['post_type'] ?? null,
                'order' => $request['order'] ?? null,
            ]
        );
    }

    /**
     *
     *
     * @param string $name The name.
     * @param array  $args The arguments.
     *
     * @throws \BadMethodCallException Method does not exist.
     */
    public function __call(string $name, array $args): Parameters
    {
        if (strpos($name, 'with_') === 0) {
            $property = substr($name, 5);
            return $this->with($property, $args[0]);
        }

        throw new \BadMethodCallException('Method ' . $name . ' does not exist.');
    }

    /**
     * Sets parameter
     *
     * @param string $name  The name.
     * @param mixed  $value The value.
     *
     * @throws \BadMethodCallException Property not allowed.
     *
     * @return self Immutable parameter object.
     */
    public function with(string $name, $value = null): self
    {
        $allowed = [ 'name', 'post_status', 'post_type', 'order' ];
        if (! in_array($name, $allowed, true)) {
            throw new \BadMethodCallException('Property ' . $name . ' does not exist.');
        }

        $this->$name = $value ?? null;
        return $this;
    }

    /**
     * Pattern name.
     */
    public function name(): ?array
    {
        return $this->name;
    }

    /**
     * List of required post status.
     */
    public function post_status(): ?array
    {
        return $this->post_status ?? self::DEFAULT_POST_STATUS;
    }

    /**
     * List of required post types.
     */
    public function post_type(): ?array
    {
        return $this->post_type ?? null;
    }

    /**
     * Columns names to sort on.
     */
    public function order(): ?array
    {
        return $this->order;
    }
}
