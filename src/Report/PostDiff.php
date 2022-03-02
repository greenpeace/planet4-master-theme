<?php

declare(strict_types=1);

namespace P4\MasterTheme\Report;

use WP_Post;
use Text_Diff;
use Text_Diff_Op_change;

if ( ! class_exists( 'Text_Diff' ) ) {
	require_once ABSPATH . 'wp-includes/Text/Diff.php';
}

/**
 * @see wp_prepare_revisions_for_js()
 */
class PostDiff {
	private $post;
	private $revision;
	private $compare_from;

	public function __construct( $post, $revision, $compare_from = null ) {
		$this->post = $post;
		$this->revision = $revision;

		$this->compare_from = $compare_from ?: $this->get_origin();

		$this->require_functions();
	}

	private function require_functions(): void {
		if ( ! function_exists( '_wp_post_revision_fields' ) ) {
			require_once ABSPATH . 'wp-admin/includes/revision.php';
		}
		if ( ! function_exists( 'wp_text_diff' ) ) {
			require_once ABSPATH . 'wp-admin/includes/pluggable.php';
		}
	}

	public function get_revisions() {
		return \wp_get_post_revisions(
			$this->post->ID,
			array(
				'order'         => 'ASC',
				'check_enabled' => false,
			)
		);
	}

	public function get_origin() {
		$revisions = $this->get_revisions();
		$found     = array_search(
			$this->revision->ID,
			array_keys( $revisions ),
			true
		);
		if ( $found ) {
			$from = array_keys( array_slice( $revisions, $found - 1, 1, true ) );
			$from = reset( $from );
		} else {
			$from = 0;
		}

		return $from ? get_post( $from ) : null;
	}

	public function get_data(): array {
		$compare_from = $this->compare_from;
		$compare_to   = $this->revision;

		// Add default title if title field is empty.
		if ( $compare_from && empty( $compare_from->post_title ) ) {
			$compare_from->post_title = __( '(no title)' );
		}
		if ( empty( $compare_to->post_title ) ) {
			$compare_to->post_title = __( '(no title)' );
		}

		return [
			'id'     => ($this->compare_from->ID ?? 0) . ':' . $this->revision->ID,
			'fields' => $this->compare(),
		];
	}

	public function compare(): array {
		$compare_from = $this->compare_from;
		$compare_to   = $this->revision;

		if ( ! $compare_from || ! $compare_to ) {
			return [];
		}

		// Add default title if title field is empty.
		if ( $compare_from && empty( $compare_from->post_title ) ) {
			$compare_from->post_title = __( '(no title)' );
		}
		if ( empty( $compare_to->post_title ) ) {
			$compare_to->post_title = __( '(no title)' );
		}

		$diffs  = [];
		$fields = \_wp_post_revision_fields( $this->post );
		foreach ( $fields as $field => $name ) {
			$content_from = $this->get_content_from( $compare_from, $field );
			$content_to   = $this->get_content_to( $compare_to, $field );

			$diffs[ $field ] = $this->text_diff( $content_from, $content_to );
		}

		return array_filter( $diffs, fn ($d) => ! empty( $d ) );
	}

	private function get_content_from( WP_Post $compare_from, string $field ): string {
		return apply_filters(
			"_wp_post_revision_field_{$field}",
			$compare_from->$field,
			$field,
			$compare_from,
			'from'
		);
	}

	private function get_content_to( WP_Post $compare_to, string $field ): string {
		return apply_filters(
			"_wp_post_revision_field_{$field}",
			$compare_to->$field,
			$field,
			$compare_to,
			'to'
		);
	}

	private function text_diff( $content_from, $content_to ): array {
		$lines_from = explode( "\n", trim( $content_from ) );
		$lines_to   = explode( "\n", trim( $content_to ) );
		$text_diff = new Text_Diff( 'auto', [ $lines_from, $lines_to ] );

		$diffs = [];
		foreach ( $text_diff->getDiff() as $diff ) {
			if ( ! ( $diff instanceof Text_Diff_Op_change ) ) {
				continue;
			}

			$diffs[] = $diff;
		}

		return $diffs;
	}
}
