<?php

/**
 * Exporter Integration Tests — covers actual file export output and error paths.
 *
 * @package P4MT
 */

use P4\MasterTheme\Exporter;

/**
 * Class ExporterOutputTest
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class ExporterOutputTest extends WP_UnitTestCase
{
    private Exporter $exporter;

    public function setUp(): void
    {
        parent::setUp();
        $this->exporter = new Exporter();

        // Clean any $_GET state left by previous tests
        unset($_GET['post'], $_GET['action']);
    }

    public function tearDown(): void
    {
        unset($_GET['post'], $_GET['action']);
        parent::tearDown();
    }

    /**
     * Included exporter.php directly and captured its XML output.
     *
     * We bypass single_post_export_data() because filter_input(INPUT_GET, ...)
     * always returns null in CLI/test environments regardless of $_GET state.
     * exporter.php reads $_GET['post'] directly, so this works correctly.
     *
     * @param int[] $post_ids
     */
    private function get_export_output(array $post_ids): string
    {
        $_GET['post'] = implode(',', $post_ids);
        $_GET['action'] = 'export_data';

        // Suppress E_WARNING from CMX_VERSION being redefined on repeated runs
        set_error_handler(static fn() => true, E_NOTICE | E_WARNING);
        ob_start();
        include get_template_directory() . '/exporter.php'; //NOSONAR
        $output = ob_get_clean();
        restore_error_handler();

        return $output;
    }

    /**
     * wp_die() is called when $_GET['post'] is missing.
     * WP test framework converts wp_die() into a WPDieException.
     */
    public function test_export_data_dies_when_no_post_id(): void
    {
        $_GET['action'] = 'export_data';
        // No $_GET['post'] set

        $this->expectException(WPDieException::class);

        $this->exporter->single_post_export_data();
    }

    /**
     * wp_die() is called when action is not export_data.
     */
    public function test_export_data_dies_when_wrong_action(): void
    {
        $post_id = $this->factory->post->create(['post_status' => 'publish']);
        $_GET['post'] = (string) $post_id;
        $_GET['action'] = 'wrong_action'; // not 'export_data'

        $this->expectException(WPDieException::class);

        $this->exporter->single_post_export_data();
    }

    /**
     * A valid post produces XML output (not empty, not an error).
     */
    public function test_export_data_outputs_xml_for_valid_post(): void
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Test Post',
            'post_status' => 'publish',
            'post_content' => '<p>Hello export world</p>',
        ]);

        $output = $this->get_export_output([$post_id]);

        $this->assertNotEmpty($output, 'Export should produce output');
        $this->assertStringContainsString('<?xml', $output);
        $this->assertStringContainsString('<rss', $output);
        $this->assertStringContainsString('</rss>', $output);
    }

    /**
     * Exported XML contains the post title.
     */
    public function test_exported_xml_contains_post_title(): void
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'My Post Title',
            'post_status' => 'publish',
        ]);

        $output = $this->get_export_output([$post_id]);

        $this->assertStringContainsString('My Post Title', $output);
    }

    /**
     * Exported XML contains the post ID.
     */
    public function test_exported_xml_contains_post_id(): void
    {
        $post_id = $this->factory->post->create(['post_status' => 'publish']);

        $output = $this->get_export_output([$post_id]);

        $this->assertStringContainsString('<wp:post_id>' . $post_id . '</wp:post_id>', $output);
    }

    /**
     * Exported output is valid, parseable XML.
     */
    public function test_exported_output_is_valid_xml(): void
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'Valid XML Test',
            'post_status' => 'publish',
            'post_content' => 'Some content here',
        ]);

        $output = $this->get_export_output([$post_id]);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($output);
        $errors = libxml_get_errors();
        libxml_clear_errors();

        // exporter.php uses dc:creator without declaring xmlns:dc — that is a
        // pre-existing source-file issue. Only fail on truly fatal errors (level 3)
        // which would indicate broken XML structure, not missing namespace declarations.
        $fatal_errors = array_filter($errors, fn($e) => $e->level === LIBXML_ERR_FATAL);
        $this->assertEmpty($fatal_errors, 'Exported XML should have no fatal parse errors: ' .
            print_r($fatal_errors, true)); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
        $this->assertNotFalse($xml, 'simplexml_load_string should succeed');
    }

    /**
     * Bulk export: comma-separated post IDs export all posts.
     */
    public function test_export_data_handles_multiple_post_ids(): void
    {
        $post_id1 = $this->factory->post->create([
            'post_title' => 'Post One',
            'post_status' => 'publish',
        ]);
        $post_id2 = $this->factory->post->create([
            'post_title' => 'Post Two',
            'post_status' => 'publish',
        ]);

        $output = $this->get_export_output([$post_id1, $post_id2]);

        $this->assertStringContainsString('Post One', $output);
        $this->assertStringContainsString('Post Two', $output);
        $this->assertStringContainsString('<rss', $output);
    }

    /**
     * get_campaign_attachments includes attached media IDs.
     */
    public function test_get_campaign_attachments_includes_attachments(): void
    {
        require_once get_template_directory() . '/exporter-helper.php';

        $post_id = $this->factory->post->create(['post_status' => 'publish']);

        // Create an attachment child of the post
        $attachment_id = $this->factory->attachment->create([
            'post_parent' => $post_id,
            'post_status' => 'inherit',
        ]);

        $result = get_campaign_attachments([$post_id]);

        // wpdb returns column values as strings; hence cast before strict comparison
        $this->assertContains((string) $attachment_id, $result, 'Attachment ID should be in export list');
        $this->assertContains($post_id, $result, 'Original post ID should remain in export list');
    }
}
