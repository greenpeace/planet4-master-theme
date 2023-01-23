<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Settings\Features;
use RuntimeException;

/**
 * Class Loader.
 * Loads all necessary classes for Planet4 Master Theme.
 */
final class Loader
{
    /**
     * A static instance of Loader.
     *
     */
    private static Loader $instance;
    /**
     * Indexed array of all the classes/services that are needed.
     *
     * @var array $services
     */
    private array $services;
    /**
     * Indexed array of all the classes/services that are used by Planet4.
     *
     * @var array $default_services
     */
    private array $default_services;

    /**
     * Singleton creational pattern.
     * Makes sure there is only one instance at all times.
     *
     * @param array $services The Controller services to inject.
     *
     */
    public static function get_instance(array $services = []): Loader
    {
        if (! isset(self::$instance)) {
            self::$instance = new self($services);
        }
        return self::$instance;
    }

    /**
     * Loader constructor.
     *
     * @param array $services The dependencies to inject.
     */
    private function __construct(array $services)
    {
        $this->load_services($services);
        $this->add_filters();
        Commands::load();
    }

    /**
     * Inject dependencies.
     *
     * @param array $services The dependencies to inject.
     */
    private function load_services(array $services): void
    {

        $this->default_services = [
            CustomTaxonomy::class,
            PostCampaign::class,
            PostArchive::class,
            Settings::class,
            Features::class,
            PostReportController::class,
            Cookies::class,
            DevReport::class,
            MasterSite::class,
            HttpHeaders::class,
            ActionPage::class,
            PageMeta::class,
            PostMeta::class,
            GravityFormsExtensions::class,
        ];

        if (is_admin()) {
            global $pagenow;

            // Load P4 Control Panel only on Dashboard page.
            $this->default_services[] = ControlPanel::class;
            $this->default_services[] = ImageArchive\UiIntegration::class;
            $this->default_services[] = ImageArchive\Rest::class;

            // Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
            if ('post-new.php' === $pagenow || 'post.php' === $pagenow) {
                $this->default_services[] = MetaboxRegister::class;
                add_action(
                    'cmb2_save_field_p4_campaign_name',
                    [ MetaboxRegister::class, 'save_global_project_id' ],
                    10,
                    3
                );
            }

            // Load `Campaigns` class only when adding/editing a new tag.
            if ('edit-tags.php' === $pagenow || 'term.php' === $pagenow) {
                $this->default_services[] = Campaigns::class;
            }

            $this->default_services[] = Exporter::class;

            // Load `CampaignImporter` class only for WordPress import requests.
			// phpcs:disable
			if ( 'wordpress' === filter_input( INPUT_GET, 'import', FILTER_SANITIZE_STRING ) ) {
				// phpcs:enable
                $this->default_services[] = Importer::class;
            }
        }

        // Run Activator after theme switched to planet4-master-theme or a planet4 child theme.
        if (get_option('theme_switched')) {
            $this->default_services[] = Activator::class;
        }

        if (wp_is_json_request()) {
            $this->default_services[] = MetaboxRegister::class;
        }

        $services = array_merge($services, $this->default_services);
        if (!$services) {
            return;
        }

        foreach ($services as $service) {
            $this->services[ $service ] = new $service();
        }
    }

    /**
     * Gets the loaded services.
     *
     * @return array The loaded services.
     */
    public function get_services(): array
    {
        return $this->services;
    }

    /**
     * Add some filters.
     *
     */
    private function add_filters(): void
    {
        add_filter('pre_delete_post', [ $this, 'do_not_delete_autosave' ], 1, 3);
    }

    /**
     * Due to a bug in WordPress core the "autosave revision" of a post is created and deleted all of the time.
     * This is pretty pointless and makes it impractical to add any post meta to that revision.
     * The logic was probably that some space could be saved it is can be determined that the autosave doesn't differ
     * from the current post content. However that advantage doesn't weigh up to the overhead of deleting the record and
     * inserting it again, each time burning through another id of the posts table.
     *
     * @see https://core.trac.wordpress.org/ticket/49532
     *
     * @param null $delete Whether to go forward with the delete (sic, see original filter where it is null initally, not used here).
     * @param null $post Post object.
     * @param null $force_delete Is true when post is not trashed but deleted permanently (always false for revisions but they are deleted anyway).
     *
     * @return bool|null If the filter returns anything else than null the post is not deleted.
     */
    public function do_not_delete_autosave($delete = null, $post = null, $force_delete = null): ?bool
    {
        if (
            $force_delete
            || ( isset($_GET['action']) && 'delete' === $_GET['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || ( isset($_GET['delete_all']) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            || ! preg_match('/autosave-v\d+$/', $post->post_name)
        ) {
            return $delete;
        }

        return false;
    }

    /**
     * @param string $rel_path Relative path to the file.
     * @return int timestamp of file creation
     */
    public static function theme_file_ver(string $rel_path): int
    {
        $filepath = trailingslashit(get_template_directory()) . $rel_path;

        return self::get_timestamp($filepath);
    }

    /**
     * Get timestamp of a file.
     *
     * @param string $path The path of the file.
     *
     * @throws RuntimeException If the file doesn't exist, or filectime fails in some other way.
     * @return int Timestamp of last file change.
     */
    private static function get_timestamp(string $path): int
    {
        $ctime = filectime($path);

        if (! $ctime) {
            throw new RuntimeException("Tried to get file change time of {$path} but failed to.");
        }

        return $ctime;
    }

    /**
     * Enqueue a style with a version based on the file change time.
     *
     * @param string $relative_path An existing css file.
     * @param null   $handle The handle to enqueue with. Generated from path if empty.
     * @param array  $deps Dependencies of this style.
     */
    public static function enqueue_versioned_style(string $relative_path, $handle = null, array $deps = []): void
    {

        $relative_path = '/' . ltrim($relative_path, '/');

        $version = self::get_timestamp(get_template_directory() . $relative_path);

        wp_enqueue_style(
            // Fall back to unique handle based on the path.
            $handle ?? str_replace('/[^\w]+/g', '', $relative_path),
            get_template_directory_uri() . $relative_path,
            $deps,
            $version
        );
    }

    /**
     * Enqueue a script with a version based on the file change time.
     *
     * @param string $relative_path An existing js file.
     * @param array  $deps Dependencies of the script.
     * @param bool   $in_footer Whether the script should be loaded in the footer.
     */
    public static function enqueue_versioned_script(string $relative_path, array $deps = [], bool $in_footer = false): void
    {
        // Create a supposedly unique handle based on the path.
        $handle = str_replace('/[^\w]/', '', $relative_path);

        $relative_path = '/' . ltrim($relative_path, '/');

        $version = self::get_timestamp(get_template_directory() . $relative_path);

        wp_enqueue_script(
            $handle,
            get_template_directory_uri() . $relative_path,
            $deps,
            $version,
            $in_footer
        );
    }
}
