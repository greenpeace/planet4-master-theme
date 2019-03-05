<?php
/**
 * P4 ElasticSearch Class
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_ElasticSearch' ) ) {

	/**
	 * Class P4_ElasticSearch
	 */
	class P4_ElasticSearch extends P4_Search {

		/**
		 * Applies user selected filters to the search if there are any and gets the filtered posts.
		 *
		 * @param array $args Query args.
		 */
		public function set_filters_args( &$args) {
			parent::set_filters_args( $args );

			if ( $this->filters ) {
				foreach ( $this->filters as $type => $filter_type ) {
					foreach ( $filter_type as $filter ) {
						switch ( $type ) {
							case 'ctype':
								switch ( $filter['id'] ) {
									case 1:
										add_filter(
											'ep_formatted_args',
											function ( $formatted_args ) use ( $args ) {
												if ( ! empty( $args['post_mime_type'] ) ) {
													$formatted_args['post_filter']['bool']['must'] = [
														'terms' => [
															'post_mime_type' => $args['post_mime_type'],
														],
													];
												}
												return $formatted_args;
											},
											10,
											1
										);
										break;
									case 2:
										// Workaround for making 'post_parent__not_in' to work with ES.
										add_filter(
											'ep_formatted_args',
											function ( $formatted_args ) use ( $args ) {
												// Make sure it is not an Action page.
												if ( ! empty( $args['post_parent__not_in'] ) ) {
													$formatted_args['post_filter']['bool']['must_not'][] = [
														'terms' => [
															'post_parent' => array_values( (array) $args['post_parent__not_in'] ),
														],
													];
												}
												// Make sure it is a Page.
												$formatted_args['post_filter']['bool']['must'][] = [
													'terms' => [
														'post_type' => array_values( (array) $args['post_type'] ),
													],
												];
												return $formatted_args;
											},
											10,
											1
										);
										break;
									case 3:
										add_filter(
											'ep_formatted_args',
											function ( $formatted_args ) use ( $args ) {
												// Make sure it is a Post.
												if ( ! empty( $args['post_type'] ) ) {
													$formatted_args['post_filter']['bool']['must'][] = [
														'terms' => [
															'post_type' => array_values( (array) $args['post_type'] ),
														],
													];
												}
												return $formatted_args;
											},
											10,
											1
										);
										break;
								}
								break;
						}
					}
				}
			}
		}

		/**
		 * Applies user selected filters to the search if there are any and gets the filtered posts.
		 *
		 * @param array $args The array with the arguments that will be passed to WP_Query.
		 */
		public function set_engine_args( &$args ) {

			$args['ep_integrate'] = true;

			// Get only DOCUMENT_TYPES from the attachments.
			if ( ! $this->search_query && ! $this->filters ) {
				add_filter(
					'ep_formatted_args',
					function ( $formatted_args ) use ( $args ) {
						// TODO - Fix parsing exception in EP API call to Elasticsearch.
						$formatted_args['post_mime_type'] = self::DOCUMENT_TYPES;
						return $formatted_args;
					},
					10,
					1
				);
			}

			add_filter( 'ep_formatted_args', [ $this, 'set_results_weight' ], 20, 1 );

			// Remove from results any Documents that should not be there.
			// TODO - This is a temp fix until we manage to query ES for only the desired documents.
			add_filter(
				'ep_search_results_array',
				function ( $results, $response, $args, $scope ) {
					foreach ( $results['posts'] as $key => $post ) {
						if ( $post['post_mime_type'] && ! in_array( $post['post_mime_type'], self::DOCUMENT_TYPES, true ) ) {
							unset( $results['posts'][ $key ] );
						}
					}
					return $results;
				},
				10,
				4
			);
		}
	}
}
