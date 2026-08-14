<?php

/**
 * Test taxonomy list API cache behavior.
 *
 * @package P4MT
 */

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class TaxonomyListApiTest extends P4TestCase
{
    /**
     * Test taxonomy list values are cached and invalidated when terms change.
     */
    public function test_taxonomy_list_cache_is_populated_and_invalidated(): void
    {
        $term = wp_insert_term('Cached Category', 'category');
        $this->assertNotWPError($term);

        $terms = P4\MasterTheme\Api\TaxonomyList::get_taxonomy_terms('category');

        $this->assertNotEmpty($terms);
        $this->assertSame('Cached Category', $terms[0]['name']);

        $taxonomy_cache_key = 'planet4-taxonomy-list:category';
        $this->assertNotFalse(wp_cache_get($taxonomy_cache_key, 'planet4-taxonomy-list'));

        wp_delete_term((int) $term['term_id'], 'category');

        $this->assertFalse(wp_cache_get($taxonomy_cache_key, 'planet4-taxonomy-list'));
    }
}
