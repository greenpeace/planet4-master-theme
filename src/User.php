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

    /**
     * User constructor.
     *
     * @param object|int|bool $uid The User id.
     * @param string          $author_override The author override display name.
     */
    public function __construct($uid = false, string $author_override = '')
    {
        if (! $author_override) {
            parent::__construct($uid);
        } else {
            $this->display_name = $author_override;
            $this->is_fake = true;
        }
    }

    /**
     * The User profile page url.
     *
     */
    public function link(): string
    {
        if ($this->is_fake) {
            return '#';
        } else {
            return parent::link();
        }
    }

    /**
     * The relative path of the User profile page.
     *
     */
    public function path(): string
    {
        if ($this->is_fake) {
            return '#';
        } else {
            return parent::path();
        }
    }

    /**
     * Author display name.
     *
     */
    public function name(): ?string
    {
        if ($this->is_fake) {
            return (string) $this->display_name;
        } else {
            return parent::name();
        }
    }

    /**
     * Stringifies the User object.
     */
    public function __toString(): string
    {
        if ($this->is_fake) {
            return $this->name();
        } else {
            return parent::__toString();
        }
    }
}
