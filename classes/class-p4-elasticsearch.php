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
		 *
		 * @throws UnexpectedValueException When filter type is not recognized.
		 */
		public function set_filters_args( &$args ) {
			parent::set_filters_args( $args );

			if ( $this->filters ) {
				foreach ( $this->filters as $type => $filter_type ) {
					foreach ( $filter_type as $filter ) {
						switch ( $type ) {
							case 'cat':
							case 'tag':
							case 'ptype':
								break;
							case 'ctype':
								switch ( $filter['id'] ) {
									case 0:
										break;
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
									case 4:
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
									case 5:
										break;
									default:
										throw new UnexpectedValueException( 'Unexpected content type!' );
								}
								break;
							default:
								throw new UnexpectedValueException( 'Unexpected filter!' );
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
		public function set_engines_args( &$args ) {
			$args['ep_integrate'] = true;
			simple_value_filter( 'epwr_scale', planet4_get_option( 'epwr_scale', '28d' ) );
			simple_value_filter( 'epwr_decay', planet4_get_option( 'epwr_decay', 0.5 ) );
			simple_value_filter( 'epwr_offset', planet4_get_option( 'epwr_offset', '365d' ) );

			add_filter( 'ep_formatted_args', [ $this, 'set_full_text_search' ], 19, 1 );
			add_filter( 'ep_formatted_args', [ $this, 'set_results_weight' ], 20, 1 );

			if ( ! wp_doing_ajax() ) {
				add_filter( 'ep_formatted_args', [ $this, 'add_aggregations' ], 999, 1 );
			}

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

		/**
		 * Apply full-text search.
		 *
		 * @param mixed $formatted_args Assoc array with the args that ES expects.
		 *
		 * @return mixed
		 */
		public function set_full_text_search( $formatted_args ) {
			if ( isset( $formatted_args['query']['function_score']['query']['bool'] ) ) {
				// Create/change the bool query from should to must.
				$formatted_args['query']['function_score']['query']['bool']['must'] = $formatted_args['query']['function_score']['query']['bool']['should'];
				// Add the operator AND to the new bool query.
				$formatted_args['query']['function_score']['query']['bool']['must'][0]['multi_match']['operator'] = 'AND';
				// Erase the old should query.
				unset( $formatted_args['query']['function_score']['query']['bool']['should'] );
				// Erase the phrase matching (to make sure we get results that include both 'courageous' AND 'act' instead of only those with 'courageous act'.
				unset( $formatted_args['query']['function_score']['query']['bool']['must'][0]['multi_match']['type'] );
			}
			return $formatted_args;
		}

		/**
		 * Apply custom weight to search results.
		 *
		 * @param mixed $formatted_args Assoc array with the args that ES expects.
		 *
		 * @return mixed
		 */
		public function set_results_weight( $formatted_args ) {

			// Move the existing query.
			$existing_query = $formatted_args['query'];
			unset( $formatted_args['query'] );
			$formatted_args['query']['function_score']['query'] = $existing_query;

			$options = get_option( 'planet4_options' );

			/**
			 * Use any combination of filters here, any matched filter will adjust
			 * the weighted results according to the scoring settings set below.
			 */
			$formatted_args['query']['function_score']['functions'] = $existing_query['function_score']['functions'] ?? [] + [
				[
					'filter' => [
						'match' => [
							'post_type' => 'page',
						],
					],
					'weight' => self::DEFAULT_PAGE_WEIGHT,
				],
				[
					'filter' => [
						'term' => [
							'post_parent' => esc_sql( $options['act_page'] ),
						],
					],
					'weight' => self::DEFAULT_ACTION_WEIGHT,
				],
			];

			// Specify how the computed scores are combined.
			$formatted_args['query']['function_score']['score_mode'] = 'sum';

			return $formatted_args;
		}

		/**
		 * Add some ES aggregations so we can get terms numbers for the complete result set.
		 *
		 * We need to add the `with_post_filter` to this otherwise it will show numbers for the result set without that
		 * filter. Actually we should not be using `with_post_filter` in the first place, as the only point of that is
		 * that it allows you to perform aggregations on the unfiltered set, which we don't want.
		 * However due to the complexity of the current setup (we use ElasticPress which creates the ES query based on
		 * the WP query, and we apply modifications to both queries) this is not easy to change.
		 *
		 * @param  array $formatted_args The query that is going to ES.
		 * @return array The same query, but with added aggregations.
		 */
		public function add_aggregations( $formatted_args ) {
			$formatted_args['aggs'] = [
				'with_post_filter' => [
					'filter' => $formatted_args['post_filter'],
					'aggs'   => [
						'post_type'    => [
							'terms' => [
								'field' => 'post_type.raw',
							],
						],
						'post_parent'  => [
							'terms' => [
								'field' => 'post_parent',
							],
						],
						'categories'   => [
							'terms' => [
								'field' => 'terms.category.term_id',
							],
						],
						'tags'         => [
							'terms' => [
								'field' => 'terms.post_tag.term_id',
							],
						],
						'p4-page-type' => [
							'terms' => [
								'field' => 'terms.p4-page-type.term_id',
							],
						],
					],
				],
			];

			return $formatted_args;
		}
	}
}
