<?php

namespace P4\MasterTheme;

use Timber;

/**
 * Class User extends Timber\User.
 *
 * Ref: https://timber.github.io/docs/reference/timber-user/
 */
class User extends Timber\User
{
    /**
     * Is a fake user flag
     *
     */
    public bool $is_fake = false;

    public static function build(\WP_User $wp_user): self
    {
        $user = parent::build($wp_user);
        return $user;
    }

    /**
     * The User profile page url.
     *
     */
    public function link(): string
    {
        if ($this->is_fake) {
            return '#';
        }

        return parent::link();
    }

    /**
     * The relative path of the User profile page.
     *
     */
    public function path(): string
    {
        if ($this->is_fake) {
            return '#';
        }

        return parent::path();
    }

    /**
     * Author display name.
     *
     */
    public function name(): ?string
    {
        if ($this->is_fake) {
            return (string) $this->display_name;
        }

        return parent::name();
    }

    /**
     * Stringifies the User object.
     */
    public function __toString(): string
    {
        if ($this->is_fake) {
            return $this->name();
        }

        return parent::__toString();
    }

    /**
     * Overrides the author's display name with a custom value.
     *
     * If a non-empty string is provided, this method sets the author's
     * display name to the given value and marks the author as "fake".
     *
     * @param string $author_override The author override display name.
     *
     */
    public function set_author_override(string $author_override): void
    {
        $this->display_name = $author_override;
        $this->is_fake = true;
    }
}
