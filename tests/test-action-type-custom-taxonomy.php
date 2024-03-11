<?php

/**
 * Test action_type custom taxonomy.
 *
 * @package P4MT
 */


/**
 * Class ActionTypeCustomTaxonomyTest
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class ActionTypeCustomTaxonomyTest extends P4TestCase
{
    /**
     * Test that a Action(Custom post type) has always a action-type term assigned to it.
     *
     * @covers P4\MasterTheme\ActionPage::save_taxonomy_action_type
     */
    public function test_action_has_a_actiontype_term_assigned(): void
    {

        // Get editor user.
        $user = get_user_by('login', 'p4_editor');
        wp_set_current_user($user->ID);

        // Create a sample Action(Custom post type) without assigning a action-type 'contest' term to it.
        $post_id = $this->factory->post->create(
            [
                'post_type' => 'p4_action',
                'post_title' => 'The name of the place is Babylon',
                'post_name' => 'test-taxonomy-url',
                'post_content' => 'test content',
                'meta_input' => [
                    '_thumbnail_id' => $this->attachment_id,
                ],
            ]
        );

        $terms = wp_get_object_terms($post_id, 'action-type');

        // Assert that the action has been assigned with a action-type term.
        $this->assertCount(1, $terms);
        $this->assertInstanceOf('WP_Term', $terms[0]);
    }

    /**
     * Test that a Action(Custom post type) has always a single action-type term assigned to it.
     *
     * @covers P4\MasterTheme\ActionPage::save_taxonomy_action_type
     */
    public function test_action_has_a_single_action_type_assigned(): void
    {

        // Get editor user.
        $user = get_user_by('login', 'p4_editor');
        wp_set_current_user($user->ID);

        // Create a sample Action(Custom post type) and assign action-type 'contest' term to it.
        $post_id = $this->factory->post->create(
            [
                'post_type' => 'p4_action',
                'post_title' => 'The name of the place is Babylon.',
                'post_name' => 'test-taxonomy-url',
                'post_content' => 'test content',
                'tax_input' => [
                    'action-type' => [ 'contest', 'event', 'petition' ],
                ],
                'meta_input' => [
                    '_thumbnail_id' => $this->attachment_id,
                ],
            ]
        );

        $terms = wp_get_object_terms($post_id, 'action-type');
        // Assert that the Action has been assigned with a single action-type term.
        $this->assertCount(1, $terms);
        $this->assertEquals('contest', $terms[0]->slug);
    }
}
