<?php

/**
 * Test posts list API cache behavior.
 *
 * @package P4MT
 */

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class PostsListApiTest extends P4TestCase
{
    /**
     * Test posts list values are cached and invalidated when posts change.
     */
    public function test_posts_list_cache_is_populated_and_invalidated(): void
    {
        // Create a sample post.
        $post_id = $this->factory->post->create([
            'post_title' => 'Cached Post',
            'post_type' => 'post',
        ]);

        $this->assertNotZero($post_id);

        $args = [
            'per_page' => '12',
            'page' => '1',
            '_embed' => 'true',
        ];

        $response = P4\MasterTheme\Api\PostsList::get_posts('posts', $args);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('totalPages', $response);
        $this->assertIsArray($response['data']);

        $endpoint_cache_key = 'planet4-posts-list:posts';
        $this->assertNotFalse(wp_cache_get($endpoint_cache_key, 'planet4-posts-list'));

        // Delete the post.
        wp_delete_post($post_id, true);

        // Cache should be invalidated.
        $this->assertFalse(wp_cache_get($endpoint_cache_key, 'planet4-posts-list'));
    }
}
