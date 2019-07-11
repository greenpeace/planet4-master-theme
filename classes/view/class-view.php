<?php
/**
 * View class
 *
 * @package P4GBKS
 * @since 0.1.0
 */

namespace P4GBKS\Views;

use Timber\Timber;

/**
 * Class View
 *
 * @package P4GBKS\Views
 */
class View {

	/** @var string $template_dir The path to the template files. */
	private $template_dir = P4GBKS_INCLUDES_DIR;

	/** @var string $template_override_subdir The path to the template files override subfolder, relative to the child theme. */
	private $template_override_subdir = P4GBKS_TEMPLATE_OVERRIDE_SUBDIR;

	/**
	 * Creates the plugin's View object.
	 */
	public function __construct() {
	}

	/**
	 * Compile and return a template file.
	 *
	 * @param array|string $template_name The file name of the template to render.
	 * @param array        $data The data to pass to the template.
	 * @param string       $relevant_dir The path to a subdirectory where the template is located (relative to $template_dir).
	 *
	 * @return bool|string The returned output
	 */
	public function get_template( $template_name, $data, $relevant_dir = 'blocks/' ) {
		Timber::$locations = $this->get_template_dir( $template_name, $relevant_dir );

		return Timber::compile( [ $relevant_dir . $template_name . '.twig' ], $data );
	}

	/**
	 * Uses the appropriate templating engine to render a template file.
	 *
	 * @param array|string $template_name The file name of the template to render.
	 * @param array        $data The data to pass to the template.
	 * @param string       $relevant_dir The path to a subdirectory where the template is located (relative to $template_dir).
	 */
	private function view_template( $template_name, $data, $relevant_dir = '' ) {
		Timber::$locations = $this->get_template_dir( $template_name, $relevant_dir );
		Timber::render( [ $relevant_dir . $template_name . '.twig' ], $data );
	}

	/**
	 * Overrides the template file if a child theme is active and contains one.
	 *
	 * @param array|string $template_name The file name of the template to render.
	 * @param string       $relevant_dir The path to a subdirectory where the template is located (relative to $template_dir or $template_override_subdir).
	 * @param string       $template_ext The extension of the template (php, twig, ...).
	 *
	 * @return string      The returned output
	 */
	private function get_template_dir( $template_name, $relevant_dir = 'blocks/', $template_ext = 'twig' ) {
		if ( '' === $relevant_dir ) {
			return $this->template_dir;
		}
		if ( is_child_theme() ) {
			$override_dir = get_stylesheet_directory() . $this->template_override_subdir;
			if ( file_exists( $override_dir . $relevant_dir . $template_name . '.' . $template_ext ) ) {
				return $override_dir;
			}
		}

		return $this->template_dir;
	}

	/**
	 * Render the settings page of the plugin.
	 *
	 * @param array $data All the data needed to render the template.
	 */
	public function settings( $data ) {
		$this->view_template( __FUNCTION__, $data );
	}

	/**
	 * Uses the appropriate templating engine to render a template file.
	 *
	 * @param array|string $template_name The file name of the template to render.
	 * @param array        $data The data to pass to the template.
	 * @param string       $template_ext The extension of the template (php, twig, ...).
	 * @param string       $relevant_dir The path to a subdirectory where the template is located (relative to $template_dir).
	 */
	public function block( $template_name, $data, $template_ext = 'twig', $relevant_dir = 'blocks/' ) {

		$template_dir = $this->get_template_dir( $template_name, $relevant_dir, $template_ext );
		if ( 'twig' === $template_ext ) {
			Timber::$locations = $template_dir;
			Timber::render( [ $relevant_dir . $template_name . '.' . $template_ext ], $data );
		} else {
			include_once $template_dir . $relevant_dir . $template_name . '.' . $template_ext;
		}
	}
}
