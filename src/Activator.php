<?php

namespace P4\MasterTheme;

/**
 * Class Activator.
 * The main class that has activation/deactivation hooks for planet4 master-theme.
 */
class Activator
{
    /**
     * Activator constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Hooks the activator functions.
     */
    protected function hooks(): void
    {
        add_action('after_switch_theme', [ self::class, 'run' ]);
    }

    /**
     * Run activation functions.
     */
    public static function run(): void
    {
        Role\Campaigner::register_role_and_add_capabilities();
        Role\Reviewer::register_role();
        Role\GravityForms::add_capabilities_to_user_roles();
        Migrator::migrate();
    }
}
