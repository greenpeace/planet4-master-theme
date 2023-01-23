<?php

namespace P4\MasterTheme\Role;

/**
 * Abstract Class GravityForms
 */
abstract class GravityForms
{
    /**
     * Add capabilities to user roles
     */
    public static function add_capabilities_to_user_roles()
    {
        // We will potentially add more roles.
        $roles = [ 'editor' ];
        $capabilities = [];

        foreach ($roles as $role_name) {
            switch ($role_name) {
                case 'editor':
                    $role = get_role($role_name);

                    $capabilities = [
                        'gform_full_access' => false,
                        'gravityforms_create_form' => true,
                        'gravityforms_delete_form' => true,
                        'gravityforms_edit_forms' => true,
                        'gravityforms_preview_forms' => true,
                        'gravityforms_view_entries' => true,
                        'gravityforms_edit_entries' => true,
                        'gravityforms_delete_entries' => true,
                    ];
                    break;
            }

            foreach ($capabilities as $capability => $has_capability) {
                if ($has_capability) {
                    $role->add_cap($capability);
                } else {
                    $role->remove_cap($capability);
                }
            }
        }
    }
}
