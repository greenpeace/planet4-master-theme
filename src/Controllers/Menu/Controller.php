<?php

/**
 * Base class
 */

namespace P4\MasterTheme\Controllers\Menu;

use P4\MasterTheme\Views\View;

if (! class_exists('Controller')) {

    /**
     * Class Controller
     *
     * This class will control all the main functions.
     */
    abstract class Controller
    {
        /**
         * View object
         */
        protected View $view;

        /**
         * Creates the controller object.
         * Avoid putting hooks inside the constructor, to make testing easier.
         *
         * @param View $view The view object.
         */
        public function __construct(View $view)
        {
            $this->view = $view;
        }

        /**
         * Hooks the method that Creates the menu item for the current controller.
         */
        public function load(): void
        {
            add_action('admin_menu', [$this, 'create_admin_menu']);
        }

        /**
         * Add the admin menu item.
         */
        abstract public function create_admin_menu(): void;
    }
}
