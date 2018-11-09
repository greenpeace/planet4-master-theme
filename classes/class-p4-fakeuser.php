<?php

if (! class_exists('P4_FakeUser')) {

    /**
     * Class P4_FakeUser mimics Timber\User and provides a similar interface.
     * 
     * Ref: https://timber.github.io/docs/reference/timber-user/
     */
    class P4_FakeUser
    {
        private $author;

        public function __construct($author_name)
        {
            $this->author = $author_name;
        }

        public function link()
        {
            return '#';
        }

        public function path()
        {
            return '#';
        }

        public function name()
        {
            return $this->author;
        }

        public function __toString()
        {
            return $this->author;
        }
    }
}