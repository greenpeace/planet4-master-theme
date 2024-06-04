<?php

/**
 * Block search query parameters
 *
 * @package P4BKS\Search\Block
 */

namespace P4\MasterTheme\BlockReportSearch\Block\Query;

/**
 * Parameter bag for Query interface
 */
class Parameters
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var string
     */
    private $content;

    /**
     * @var string[]
     */
    private $post_status;

    /**
     * @var string[]
     */
    private $post_type;

    /**
     * @var string[]
     */
    private $order;

    /**
     * @var string[]
     */
    const DEFAULT_POST_STATUS = [ 'publish', 'private' ];

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
        $text_search = ! empty($request['s']) ? $request['s'] : null;

        if (! empty($request['name'])) {
            $request['namespace'] = null;
        }

        return self::from_array(
            [
                'namespace' => $request['namespace'] ?? null,
                'name' => $request['name'] ?? null,
                'content' => $text_search,
                'attributes' => $request['attributes'] ?? [ $text_search ],
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
     * @throws \BadMethodCallException Method does not exists.
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
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
        $allowed = [ 'namespace', 'name', 'attributes', 'content', 'post_status', 'post_type', 'order' ];
        if (! in_array($name, $allowed, true)) {
            throw new \BadMethodCallException('Property ' . $name . ' does not exist.');
        }

        $this->$name = $value ?? null;
        return $this;
    }

    /**
     * Block namespace.
     */
    public function namespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Full block name.
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Block attributes.
     */
    public function attributes(): ?array
    {
        return $this->attributes;
    }

    /**
     * Block options content.
     */
    public function content(): ?string
    {
        return $this->content;
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
