<?php

if( !defined( 'ABSPATH' ) ) die();

/**
 * Singleton reference
 */
global $searchwp;


/**
 * Class SearchWPSearch performs search queries on the index
 */
class SearchWPSearch
{
	/**
	 * @var string Search engine name
	 * @since 1.0
	 */
	private $engine;

	/**
	 * @var array Terms to search for
	 * @since 1.0
	 */
	private $terms;

	/**
	 * @var mixed|void Stored SearchWP settings
	 * @since 1.0
	 */
	private $settings;

	/**
	 * @var int The page of results to work with
	 * @since 1.0
	 */
	private $page;

	/**
	 * @var int The number of posts per page
	 * @since 1.0
	 */
	private $postsPer;

	/**
	 * @var string The order in which results should be returned
	 * @since 1.0
	 */
	private $order = 'DESC';

	public $foundPosts  = 0;
	public $maxNumPages = 0;
	public $postIDs     = array();
	public $posts;

	/**
	 * @var string|array post status(es) to include when indexing
	 *
	 * @since 1.6.10
	 */
	private $post_statuses = 'publish';


	/**
	 * Constructor
	 *
	 * @param array $args
	 * @since 1.0
	 */
	function __construct( $args = array() )
	{
		do_action( 'searchwp_log', 'SearchWPSearch __construct()' );

		$defaults = array(
			'terms'             => '',
			'engine'            => 'default',
			'page'              => 1,
			'posts_per_page'    => intval( get_option( 'posts_per_page' ) ),
			'order'             => $this->order,
			'load_posts'        => true,
		);

		// process our arguments
		$args = wp_parse_args( $args, $defaults );
		$searchwp = SearchWP::instance();

		do_action( 'searchwp_log', '$args = ' . var_export( $args, true ) );

		// if we have a valid engine, perform the query
		if( $searchwp->isValidEngine( $args['engine'] ) )
		{
			// this filter is also applied in the SearchWP class search methods
			// TODO: should this be applied in both places? which?
			$sanitizeTerms = apply_filters( 'searchwp_sanitize_terms', true, $args['engine'] );
			if ( ! is_bool( $sanitizeTerms ) ) {
				$sanitizeTerms = true;
			}

			if( $sanitizeTerms ) {
				$terms = $searchwp->sanitizeTerms( $args['terms'] );
			} else {
				do_action( 'searchwp_log', 'Opted out of internal sanitization' );
			}

			$engine = $args['engine'];

			// allow dev to customize post statuses are included
			$this->post_statuses = (array) apply_filters( 'searchwp_post_statuses', $this->post_statuses, $engine );
			foreach( $this->post_statuses as $post_status_key => $post_status_value ) {
				$this->post_statuses[$post_status_key] = sanitize_key( $post_status_value );
			}

			do_action( 'searchwp_log', '$terms = ' . var_export( $terms, true ) );

			if( strtoupper( apply_filters( 'searchwp_search_query_order', $args['order'] ) ) != 'DESC' && strtoupper( $args['order'] ) != 'ASC' ) {
				$args['order'] = 'DESC';
			}

			// filter the terms just before querying
			$terms = apply_filters( 'searchwp_pre_search_terms', $terms, $engine );

			do_action( 'searchwp_log', 'searchwp_pre_search_terms $terms = ' . var_export( $terms, true ) );

			$this->terms        = $terms;
			$this->engine       = $engine;
			$this->settings     = get_option( SEARCHWP_PREFIX . 'settings' );
			$this->page         = absint( $args['page'] );
			$this->postsPer     = absint( $args['posts_per_page'] );
			$this->order        = $args['order'];
			$this->load_posts   = is_bool( $args['load_posts'] ) ? $args['load_posts'] : true;

			// perform our query
			$this->posts = $this->query();
		}

	}


	/**
	 * Perform a query on the index
	 *
	 * @return array Posts returned by the query
	 * @since 1.0
	 */
	function query()
	{
		do_action( 'searchwp_log', 'query()' );

		do_action( 'searchwp_before_query_index', array(
			'terms'     => $this->terms,
			'engine'    => $this->engine,
			'settings'  => $this->settings,
			'page'      => $this->page,
			'postsPer'  => $this->postsPer
		) );

		$this->queryForPostIDs();

		$swpargs = array(
			'terms'     => $this->terms,
			'engine'    => $this->engine,
			'settings'  => $this->settings,
			'page'      => $this->page,
			'postsPer'  => $this->postsPer
		);

		do_action( 'searchwp_after_query_index', $swpargs );

		// facilitate filtration of returned results
		$this->postIDs = apply_filters( 'searchwp_query_results', $this->postIDs, $swpargs );

		if( empty( $this->postIDs ) ) {
			return array();
		}

		// our post IDs will have already been filtered based on the engine settings, so we want to query for
		// anything that matches our post IDs
		$args = array(
			'posts_per_page'    => count( $this->postIDs ),
			'post_type'         => 'any',
			'post_status'       => 'any',	// we've already filtered our post statuses in the original query
			'post__in'          => $this->postIDs,
			'orderby'           => 'post__in'
		);

		// we want ints all the time
		$this->postIDs = array_map( 'absint', $this->postIDs );

		if ( $this->load_posts && true === apply_filters( 'searchwp_load_posts', true, $swpargs ) ) {
			$posts = apply_filters( 'searchwp_found_post_objects', get_posts( $args ), $swpargs );
		} else {
			$posts = $this->postIDs;
		}

		return $posts;
	}


	/**
	 * Dynamically generate SQL query based on engine settings and retrieve a weighted, ordered list of posts
	 *
	 * @return bool|array Post IDs found in the index
	 * @since 1.0
	 */
	function queryForPostIDs()
	{
		global $wpdb;

		do_action( 'searchwp_log', 'queryForPostIDs()' );

		// check to make sure there are settings for the current engine
		if( !isset( $this->settings['engines'][$this->engine] ) && is_array( $this->settings['engines'][$this->engine] ) ) {
			return false;
		}

		// check to make sure we actually have terms to search
		// TODO: refactor this when this method is refactored for 2.0
		if( empty( $this->terms ) ) {
			// short circuit
			$this->foundPosts = 0;
			$this->maxNumPages = 0;
			$this->postIDs = array();
			return false;
		}

		// instantiate our stemmer for later
		$stemmer = new SearchWPStemmer();

		// pull out our engine-specific settings
		$engineSettings = $this->settings['engines'][$this->engine];

		// allow filtration of settings at runtime
		$engineSettings = apply_filters( "searchwp_engine_settings_{$this->engine}", $engineSettings, $this->terms );

		// check to make sure that all post types in the settings are still in fact registered and active
		$searchwp = SearchWP::instance();
		if( is_array( $searchwp->postTypes ) )
			foreach( $engineSettings as $postType => $postTypeSettings )
				if( !in_array( $postType, $searchwp->postTypes ) )
					unset( $engineSettings[$postType] );

		// check to make sure that at least one post type is enabled for this engine
		$okToSearch = false;
		if( is_array( $engineSettings ) )
			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
					$okToSearch = true;

		if( !$okToSearch )
			return false;

		// let the fun begin
		$prefix = $wpdb->prefix;

		// we're going to exclude entered IDs for the query as a whole
		// need to get these IDs early because if an attributed post ID is excluded we need to omit it from
		// the query entirely
		$excludeIDs = apply_filters( 'searchwp_prevent_indexing', array() ); // catch anything that shouldn't have been indexed anyway
		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			// store our exclude clause
			$postTypeExcludeIDs = ( isset( $postTypeWeights['options']['exclude'] ) && !empty( $postTypeWeights['options']['exclude'] ) ) ? explode( ',', $postTypeWeights['options']['exclude'] ) : array();

			if( !empty( $postTypeExcludeIDs ) )
				foreach( $postTypeExcludeIDs as $postTypeExcludeID )
					$excludeIDs[] = intval( $postTypeExcludeID );

		}

		do_action( 'searchwp_log', '$excludeIDs = ' . var_export( $excludeIDs, true ) );

		// pull any excluded IDs based on taxonomy term
		add_filter( 'searchwp_force_wp_query', '__return_true' ); // we're going to be firing a WP_Query and want it to finish
		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			$taxonomies = get_object_taxonomies( $postType );
			if( is_array( $taxonomies ) && count( $taxonomies ) )
				foreach( $taxonomies as $taxonomy )
				{
					$taxonomy = get_taxonomy( $taxonomy );
					if( isset( $postTypeWeights['options']['exclude_' . $taxonomy->name] ) )
					{
						$excludedTerms = explode( ',', $postTypeWeights['options']['exclude_' . $taxonomy->name] );

						if( !is_array( $excludedTerms ) )
							$excludedTerms = array( intval( $excludedTerms ) );

						if( !empty( $excludedTerms ) )
							foreach( $excludedTerms as $excludedKey => $excludedValue )
								$excludedTerms[$excludedKey] = intval( $excludedValue );

						// determine which post(s) have this term
						$args = array(
							'posts_per_page'    => -1,
							'fields'            => 'ids',
							'post_type'         => $postType,
							'suppress_filters'  => true,
							'tax_query'         => array(
								array(
									'taxonomy'  => $taxonomy->name,
									'field'     => 'id',
									'terms'     => $excludedTerms
								)
							)
						);

						$excludedByTerm = new WP_Query( $args );

						if( !empty( $excludedByTerm ) )
							$excludeIDs = array_merge( $excludeIDs, $excludedByTerm->posts );
					}
				}
		}
		remove_filter( 'searchwp_force_wp_query', '__return_true' );

		do_action( 'searchwp_log', 'After taxonomy exclusion $excludeIDs = ' . var_export( $excludeIDs, true ) );

		// perform our AND logic before getting started
		// e.g. we're going to limit to posts that have all of the search terms
		$relevantTermPrefix = $prefix . SEARCHWP_DBPREFIX;
		$parity = count( $this->terms );

		// AND logic only applies if there's more than one term (and the dev doesn't disable it)
		$doAnd = ( count( $this->terms ) > 1 && apply_filters( 'searchwp_and_logic', true ) ) ? true : false;

		do_action( 'searchwp_log', '$doAnd = ' . var_export( $doAnd, true ) );

		// allow devs to filter which fields should be included for AND checks
		$andFieldsDefaults = array( 'title', 'content', 'slug', 'excerpt', 'comment', 'tax', 'meta' );
		$andFields = apply_filters( 'searchwp_and_fields', $andFieldsDefaults );

		// validate AND fields
		if( is_array( $andFields ) && !empty( $andFields ) )
		{
			$andFields = array_map( 'strtolower', $andFields );
			foreach( $andFields as $andFieldKey => $andField )
			{
				if( !in_array( $andField, $andFieldsDefaults ) )
				{
					// invalid field, kill it
					unset( $andFields[$andFieldKey] );
				}
			}
		}
		else
		{
			// returned not an array, so reset it (which will basically invalidate AND searching)
			$andFields = array();
		}

		do_action( 'searchwp_log', '$andFields = ' . var_export( $andFields, true ) );

		$relevantPostIds = array();
		if( $doAnd && is_array( $andFields ) && !empty( $andFields ) )
		{
			// find posts where all terms appear in the title
			$andTerms = array();
			$applicableAndResults = true;

			// grab posts with each term in the title
			foreach( $this->terms as $andTerm )
			{
				$coalesceFields = array();

				// we're going to utilize $andFields to build our query based on what the dev wants to count for AND queries
				foreach( $andFields as $andField )
				{
					switch( $andField )
					{
						case 'tax':
							$andFieldTable = 'tax';
							$andFieldColumn = 'count';
							break;
						case 'meta':
							$andFieldTable = 'cf';
							$andFieldColumn = 'count';
							break;
						default:
							$andFieldTable = 'index';
							$andFieldColumn = $andField;
							break;
					}

					$coalesceFields[] = "COALESCE({$relevantTermPrefix}{$andFieldTable}.{$andFieldColumn},0)";
				}
				$andFieldsCoalesce = implode( ' + ', $coalesceFields );

				// in order to save having to scrub through every enabled post type
				// we're just going to assume a stem here and limit the result pool as quickly as possible
				// since the main query will take into consideration the additional limitation of the stem
				$andTerm = $wpdb->prepare( '%s', $stemmer->stem( $andTerm ) );
				$relavantTermWhere = " {$relevantTermPrefix}terms.stem = " . strtolower( $andTerm );

				$andTermSQL = "
						SELECT {$relevantTermPrefix}index.post_id,
							{$andFieldsCoalesce} as termcount
						FROM {$relevantTermPrefix}index FORCE INDEX (termindex)
						LEFT JOIN {$relevantTermPrefix}terms
						ON {$relevantTermPrefix}index.term = {$relevantTermPrefix}terms.id
						LEFT JOIN {$relevantTermPrefix}cf
						ON {$relevantTermPrefix}index.post_id = {$relevantTermPrefix}cf.post_id
						LEFT JOIN {$relevantTermPrefix}tax
						ON {$relevantTermPrefix}index.post_id = {$relevantTermPrefix}tax.post_id
						WHERE {$relavantTermWhere}
						GROUP BY {$relevantTermPrefix}index.post_id
						HAVING termcount > 0
						";

				$postsWithTermPresent = $wpdb->get_col( $andTermSQL );

				do_action( 'searchwp_log', '$postsWithTermPresent = ' . var_export( $postsWithTermPresent, true ) );

				if( !empty( $postsWithTermPresent ) ) {
					$andTerms[] = $postsWithTermPresent;
				} else {
					// since no posts were found with this term in the title, our AND logic fails
					$applicableAndResults = false;
					break;
				}
			}

			// find the common post IDs across the board
			if( $applicableAndResults ) {
				$relevantPostIds = call_user_func_array( 'array_intersect', $andTerms );
			}

		}

		// we want ints, always
		$relevantPostIds = array_map( 'absint', $relevantPostIds );

		// we need to check for exclusions at this point (weights of < zero)
		$andTerms = array();
		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
			{
				foreach( $postTypeWeights['weights'] as $type => $weight )
				{
					foreach( $this->terms as $andTerm )
					{
						$applicableExclusion = false;

						// determine whether we want a term match or stem match
						$andTerm = $wpdb->prepare( '%s', $andTerm );
						if( !isset( $postTypeWeights['options']['stem'] ) || empty( $postTypeWeights['options']['stem'] ) )
							$relavantTermWhere = " {$relevantTermPrefix}terms.term = " . strtolower( $wpdb->prepare( '%s', $andTerm ) );
						else
							$relavantTermWhere = " {$relevantTermPrefix}terms.stem = " . strtolower( $wpdb->prepare( '%s', $stemmer->stem( $andTerm ) ) );

						$andInternalSQL = "
									SELECT {$relevantTermPrefix}index.post_id
									FROM {$relevantTermPrefix}index FORCE INDEX (termindex)
									LEFT JOIN {$relevantTermPrefix}terms
									ON {$relevantTermPrefix}index.term = {$relevantTermPrefix}terms.id
									LEFT JOIN {$relevantTermPrefix}cf
									ON {$relevantTermPrefix}index.post_id = {$relevantTermPrefix}cf.post_id
									LEFT JOIN {$relevantTermPrefix}tax
									ON {$relevantTermPrefix}index.post_id = {$relevantTermPrefix}tax.post_id
									WHERE {$relavantTermWhere} ";

						if( !empty( $relevantPostIds ) )
						{
							$relevantIDsSQL = implode( ",", $relevantPostIds );
							$andInternalSQL .= " AND {$relevantTermPrefix}index.post_id IN ({$relevantIDsSQL}) ";
						}

						$andInternalSQL .= " AND ( ";

						// $weight will sometimes be an array (taxonomies and custom fields)
						if( !is_array( $weight ) && intval( $weight ) < 0 )
						{
							$applicableExclusion = true;
							switch( $type ) {
								case 'title':
									$andInternalSQL .= " {$relevantTermPrefix}index.title > 0  OR ";
									break;
								case 'content':
									$andInternalSQL .= " {$relevantTermPrefix}index.content > 0  OR ";
									break;
								case 'slug':
									$andInternalSQL .= " {$relevantTermPrefix}index.slug > 0  OR ";
									break;
								case 'excerpt':
									$andInternalSQL .= " {$relevantTermPrefix}index.excerpt > 0  OR ";
									break;
								case 'comment':
									$andInternalSQL .= " {$relevantTermPrefix}index.comment > 0  OR ";
									break;
							}
						}
						else
						{
							// it's either a taxonomy or custom field, so we need to handle it a bit differently
							if( $type == 'tax' )
							{
								foreach( $weight as $postTypeTax => $postTypeTaxWeight )
								{
									if( intval( $postTypeTaxWeight ) < 0 )
									{
										$applicableExclusion = true;
										$andInternalSQL .= " ( {$relevantTermPrefix}tax.taxonomy = '{$postTypeTax}' AND {$relevantTermPrefix}tax.count > 0 )  OR ";
									}
								}
							}
							elseif( $type == 'cf' )
							{
								foreach( $weight as $postTypeCustomField )
								{
									foreach( $postTypeCustomField as $postTypeCustomFieldKey => $postTypeCustomFieldWeight )
									{
										if( intval( $postTypeCustomFieldWeight ) < 0 )
										{
											$applicableExclusion = true;
											$andInternalSQL .= " ( {$relevantTermPrefix}cf.metakey = '{$postTypeCustomFieldKey}' AND {$relevantTermPrefix}cf.count > 0 )  OR ";
										}
									}
								}
							}
						}

						// trim off the extra OR
						$andInternalSQL = substr( $andInternalSQL, 0, strlen( $andInternalSQL ) - 4 ) . " ) GROUP BY {$relevantTermPrefix}index.post_id";

						// if this exclusion is applicable, grab post IDs that trigger the exclusion
						if( $applicableExclusion )
						{
							$postsWithTerm = $wpdb->get_col( $andInternalSQL );

							// add these post IDs to the heap (we're going to make it unique later)
							$andTerms = array_merge( $andTerms, array_map( 'absint', $postsWithTerm ) );
						}
					}

				}
			}
		}

		// $andTerms is a conglomerate pile of post IDs violating the exclusion rule
		$andTerms = array_unique( $andTerms );

		// merge the weight-based exlusions on to the main excludes
		$excludeIDs = array_merge( $excludeIDs, $andTerms );

		// make sure everything is an int
		if( !empty( $excludeIDs ) )
		{
			$excludeIDs = array_map( 'absint', $excludeIDs );
		}

		$excludeIDs = apply_filters( 'searchwp_exclude', $excludeIDs, $this->engine, $this->terms );
		$excludeSQL = ( !empty( $excludeIDs ) ) ? " AND {$prefix}posts.ID NOT IN (" . implode( ',', $excludeIDs ) . ") " : '';

		// if there's an insane number of posts returned, we're dealing with a site with a lot of similar content
		// so we need to trim out the initial results by relevance before proceeding else we'll have a wicked slow query
		$maxNumAndResults = absint( apply_filters( 'searchwp_max_and_results', 300 ) );
		if( $parity > 1 && apply_filters( 'searchwp_refine_and_results', true ) && count( $relevantPostIds) > $maxNumAndResults )
		{
			// find posts where all terms appear in the title
			$andTerms = array();
			$applicableAndResults = true;

			$intermediateIncludeSQL = ( !empty( $relevantPostIds ) ) ? " AND {$prefix}swp_index.post_id IN (" . implode( ',', $relevantPostIds ) . ") " : '';
			$intermediateExcludeSQL = ( !empty( $excludeIDs ) ) ? " AND {$prefix}swp_index.post_id NOT IN (" . implode( ',', $excludeIDs ) . ") " : '';

			// grab posts with each term in the title
			foreach( $this->terms as $andTerm )
			{
				// determine whether we want a term match or stem match
				$andTerm = $wpdb->prepare( '%s', $andTerm );
				if( !isset( $postTypeWeights['options']['stem'] ) || empty( $postTypeWeights['options']['stem'] ) )
					$relavantTermWhere = " {$relevantTermPrefix}terms.term = " . strtolower( $wpdb->prepare( '%s', $andTerm ) );
				else
					$relavantTermWhere = " {$relevantTermPrefix}terms.stem = " . strtolower( $wpdb->prepare( '%s', $stemmer->stem( $andTerm ) ) );

				$postsWithTermInTitle = $wpdb->get_col(
					"
						SELECT post_id
						FROM {$relevantTermPrefix}index FORCE INDEX (termindex)
						LEFT JOIN {$relevantTermPrefix}terms
						ON {$relevantTermPrefix}index.term = {$relevantTermPrefix}terms.id
						WHERE {$relavantTermWhere}
						{$intermediateExcludeSQL}
						{$intermediateIncludeSQL}
						AND {$relevantTermPrefix}index.title > 0
						"
				);

				if( !empty( $postsWithTermInTitle ) ) {
					$andTerms[] = $postsWithTermInTitle;
				} else {
					// since no posts were found with this term in the title, our AND logic fails
					$applicableAndResults = false;
					break;
				}
			}

			// find the common post IDs across the board
			if( $applicableAndResults ) {
				$relevantPostIds = call_user_func_array( 'array_intersect', $andTerms );
			}

		}

		// make sure we've got an array of unique integers
		$relevantPostIds = array_map( 'absint', array_unique( $relevantPostIds ) );

		add_filter( 'searchwp_force_wp_query', '__return_true' );
		$includeIDs = apply_filters( 'searchwp_include', $relevantPostIds, $this->engine, $this->terms );
		remove_filter( 'searchwp_force_wp_query', '__return_true' );

		// allow devs to force AND logic all the time, no matter what (if there was more than one search term)
		$forceAnd = ( count( $this->terms ) > 1 && apply_filters( 'searchwp_and_logic_only', false ) ) ? true : false;

		// if it was totally empty and AND logic is forced, we'll hit a SQL error, so populate it with an impossible ID
		if( empty( $includeIDs ) && $forceAnd )
			$includeIDs = array( 0 );

		$includeSQL = ( ( is_array( $includeIDs ) && !empty( $includeIDs ) ) || $forceAnd ) ? " AND {$prefix}posts.ID IN (" . implode( ',', $includeIDs ) . ") " : '';

		/**
		 * OPEN THE QUERY
		 */
		$sql = "SELECT SQL_CALC_FOUND_ROWS {$prefix}posts.ID AS post_id, \n";

		// sum our final weights per post type
		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
			{
				$termCounter = 1;
				if( empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}weight`,0) + ";
						$termCounter++;
					}
				}
				else
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}attr`,0) + ";
						$termCounter++;
					}
				}
				$sql = substr( $sql, 0, strlen( $sql ) - 2 );	// trim off the extra +
				$sql .= " AS `final{$postType}weight`, ";
			}
		}


		// build our final, overall weight
		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
			{
				$termCounter = 1;
				if( empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}weight`,0) + ";
						$termCounter++;
					}
				}
				else
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}attr`,0) + ";
						$termCounter++;
					}
				}
			}
		}

		$sql = substr( $sql, 0, strlen( $sql ) - 2 );	// trim off the extra +
		$sql .= " AS finalweight FROM {$prefix}posts ";

		// allow for pre-algorithm join
		$sql = ' ' . (string) apply_filters( 'searchwp_query_main_join', $sql, $this->engine ) . ' ';

		/**
		 * BEGIN LOOP THROUGH EACH SUBMITTED TERM
		 */
		$termCounter = 1;
		foreach( $this->terms as $term )
		{
			$sql .= "LEFT JOIN ( ";

			// our final query cap
			$sql .= "SELECT {$prefix}posts.ID AS post_id ";

			// implement our post type weight column
			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && empty( $postTypeWeights['options']['attribute_to'] ) )
					$sql .= ", COALESCE(`{$postType}weight`,0) AS `{$postType}weight` ";

			// implement our post type attributed weight column
			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && !empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					$attributedTo = intval( $postTypeWeights['options']['attribute_to'] );
					// make sure we're not excluding the attributed post id
					if( !in_array( $attributedTo, $excludeIDs ) )
						$sql .= ", COALESCE(`{$postType}attr`,0) as `{$postType}attr` ";
				}

			$sql .= " , ";

			// concatenate our total weight with post type weight
			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && empty( $postTypeWeights['options']['attribute_to'] ) )
					$sql .= " COALESCE(`{$postType}weight`,0) +";

			// concatenate our total weight with our attributed weight
			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && !empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					$attributedTo = intval( $postTypeWeights['options']['attribute_to'] );
					// make sure we're not excluding the attributed post id
					if( !in_array( $attributedTo, $excludeIDs ) )
						$sql .= " COALESCE(`{$postType}attr`,0) +";
				}


			$sql = substr( $sql, 0, strlen( $sql ) - 2 );	// trim off the extra +

			$sql .= " AS weight ";
			$sql .= " FROM {$prefix}posts ";

			// build our post type queries
			foreach( $engineSettings as $postType => $postTypeWeights )
			{
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
				{
					// TODO: store our post format clause and integrate
					// TODO: store our post status clause and integrate

					// if it's an attachment we need to force 'inherit'
					$post_statuses = $postType == 'attachment' ? array( 'inherit' ) : $this->post_statuses;
					$statusSQL = "AND {$prefix}posts.post_status IN ( '" . implode( "', '", $post_statuses ) . "' ) ";

					// determine whether we need to limit to a mime type
					if( isset( $postTypeWeights['options']['mimes'] ) && !empty( $postTypeWeights['options']['mimes'] ) )
					{
						$mimes          = explode( ',', $postTypeWeights['options']['mimes'] );
						$targetedMimes  = array();

						// TODO: Better system for this
						$mimeref = array(
							'image' => array(
								'image/jpeg',
								'image/gif',
								'image/png',
								'image/bmp',
								'image/tiff',
								'image/x-icon',
							),
							'video' => array(
								'video/x-ms-asf',
								'video/x-ms-wmv',
								'video/x-ms-wmx',
								'video/x-ms-wm',
								'video/avi',
								'video/divx',
								'video/x-flv',
								'video/quicktime',
								'video/mpeg',
								'video/mp4',
								'video/ogg',
								'video/webm',
								'video/x-matroska',
							),
							'text' => array(
								'text/plain',
								'text/csv',
								'text/tab-separated-values',
								'text/calendar',
								'text/richtext',
								'text/css',
								'text/html',
							),
							'audio' => array(
								'audio/mpeg',
								'audio/x-realaudio',
								'audio/wav',
								'audio/ogg',
								'audio/midi',
								'audio/x-ms-wma',
								'audio/x-ms-wax',
								'audio/x-matroska',
							),
							'application' => array(
								'application/rtf',
								'application/javascript',
								'application/pdf',
								'application/x-shockwave-flash',
								'application/java',
								'application/x-tar',
								'application/zip',
								'application/x-gzip',
								'application/rar',
								'application/x-7z-compressed',
								'application/x-msdownload',
							),
							'msoffice' => array(
								'application/msword',
								'application/vnd.ms-powerpoint',
								'application/vnd.ms-write',
								'application/vnd.ms-excel',
								'application/vnd.ms-access',
								'application/vnd.ms-project',
								'application/vnd.openxmlformats-officedocument.wordprocessingml. document',
								'application/vnd.ms-word.document.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.wordprocessingml. template',
								'application/vnd.ms-word.template.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
								'application/vnd.ms-excel.sheet.macroEnabled.12',
								'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
								'application/vnd.ms-excel.template.macroEnabled.12',
								'application/vnd.ms-excel.addin.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.presentationml. presentation',
								'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.presentationml. slideshow',
								'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.presentationml.template',
								'application/vnd.ms-powerpoint.template.macroEnabled.12',
								'application/vnd.ms-powerpoint.addin.macroEnabled.12',
								'application/vnd.openxmlformats-officedocument.presentationml.slide',
								'application/vnd.ms-powerpoint.slide.macroEnabled.12',
								'application/onenote',
							),
							'openoffice' => array(
								'application/vnd.oasis.opendocument.text',
								'application/vnd.oasis.opendocument.presentation',
								'application/vnd.oasis.opendocument.spreadsheet',
								'application/vnd.oasis.opendocument.graphics',
								'application/vnd.oasis.opendocument.chart',
								'application/vnd.oasis.opendocument.database',
								'application/vnd.oasis.opendocument.formula',
							),
							'wordperfect' => array(
								'application/wordperfect',
							),
							'iwork' => array(
								'application/vnd.apple.keynote',
								'application/vnd.apple.numbers',
								'application/vnd.apple.pages',
							),
						);

						foreach( $mimes as $mimeKey )
						{
							switch( intval( $mimeKey ) )
							{
								case 1: // PDFs
									$targetedMimes = array_merge( $targetedMimes, array( 'application/pdf' ) );
									break;
								case 2: // Plain Text
									$targetedMimes = array_merge( $targetedMimes, $mimeref['text'] );
									break;
								case 3: // Images
									$targetedMimes = array_merge( $targetedMimes, $mimeref['images'] );
									break;
								case 4: // Video
									$targetedMimes = array_merge( $targetedMimes, $mimeref['video'] );
									break;
								case 5: // Audio
									$targetedMimes = array_merge( $targetedMimes, $mimeref['audio'] );
									break;
								default: // All Documents
									$targetedMimes = array_merge( $targetedMimes,
										$mimeref['text'],
										$mimeref['application'],
										$mimeref['msoffice'],
										$mimeref['openoffice'],
										$mimeref['wordperfect'],
										$mimeref['iwork']
									);
									break;
							}

							// remove dupes
							$targetedMimes = array_unique( $targetedMimes );
						}

						// we have an array of keys that match MIME types (not subtypes) that we can limit to by appending this condition
						$statusSQL .= " AND {$prefix}posts.post_type = 'attachment' AND {$prefix}posts.post_mime_type IN ( '" . implode( "', '", $targetedMimes ) . "' ) ";
					}

					// determine whether we're stemming or not
					if( !isset( $postTypeWeights['options']['stem'] ) || empty( $postTypeWeights['options']['stem'] ) )
						$termWhere = " {$prefix}swp_terms.term = " . strtolower( $wpdb->prepare( '%s', $term ) );
					else
						$termWhere = " {$prefix}swp_terms.stem = " . strtolower( $wpdb->prepare( '%s', $stemmer->stem( $term ) ) );

					// we need to use absint because if a weight was set to -1 for exclusion, it was already forcefully excluded
					$titleWeight 	  = isset( $postTypeWeights['weights']['title'] )   ? absint( $postTypeWeights['weights']['title'] )    : 0;
					$slugWeight 	  = isset( $postTypeWeights['weights']['slug'] )    ? absint( $postTypeWeights['weights']['slug'] )     : 0;
					$contentWeight 	= isset( $postTypeWeights['weights']['content'] ) ? absint( $postTypeWeights['weights']['content'] )  : 0;
					$commentWeight 	= isset( $postTypeWeights['weights']['comment'] ) ? absint( $postTypeWeights['weights']['comment'] )  : 0;
					$excerptWeight 	= isset( $postTypeWeights['weights']['excerpt'] ) ? absint( $postTypeWeights['weights']['excerpt'] )  : 0;

					$coalesceCustomFields = '0 +';
					if( isset( $postTypeWeights['weights']['cf'] ) && is_array( $postTypeWeights['weights']['cf'] ) && !empty( $postTypeWeights['weights']['cf'] ) )
					{
						$totalCustomFields = count( $postTypeWeights['weights']['cf'] );
						for( $i = 0; $i < $totalCustomFields; $i++ )
						{
							$coalesceCustomFields .= " COALESCE(cfweight" . $i . ",0) + ";
						}
					}
					$coalesceCustomFields = substr( $coalesceCustomFields, 0, strlen( $coalesceCustomFields) - 2 );

					$coalesceTaxonomies = '0 +';
					if( isset( $postTypeWeights['weights']['tax'] ) && is_array( $postTypeWeights['weights']['tax'] ) && !empty( $postTypeWeights['weights']['tax'] ) )
					{
						$totalTaxonomies = count( $postTypeWeights['weights']['tax'] );
						for( $i = 0; $i < $totalTaxonomies; $i++ )
						{
							$coalesceTaxonomies .= " COALESCE(taxweight" . $i . ",0) + ";
						}
					}
					$coalesceTaxonomies = substr( $coalesceTaxonomies, 0, strlen( $coalesceTaxonomies) - 2 );

					// allow additional tables to be joined
					$joinSQL = apply_filters( 'searchwp_query_join', '', $postType, $this->engine );
					if( !is_string( $joinSQL ) )
						$joinSQL = '';

					// allow additional conditions
					$conditionsSQL = apply_filters( 'searchwp_query_conditions', '', $postType, $this->engine );
					if( !is_string( $conditionsSQL ) )
						$conditionsSQL = '';

					// if we're dealing with attributed weight we need to make sure that the attribution target was not excluded
					$excludedByAttribution = false;
					$attributedTo = null;
					if( isset( $postTypeWeights['options']['attribute_to'] ) && !empty( $postTypeWeights['options']['attribute_to'] ) )
					{
						$postColumn = 'ID';
						$attributedTo = intval( $postTypeWeights['options']['attribute_to'] );
						if( in_array( $attributedTo, $excludeIDs ) )
							$excludedByAttribution = true;
					}
					else
					{
						// if it's an attachment and we want to attribute to the parent, we need to set that here
						$postColumn = isset( $postTypeWeights['options']['parent'] ) ? 'post_parent' : 'ID';
					}

					// open up the post type subquery
					if( !$excludedByAttribution )
					{
						$sql .= "
							LEFT JOIN (
								SELECT {$prefix}posts.{$postColumn} AS post_id,
									( {$prefix}swp_index.title * {$titleWeight} ) +
									( {$prefix}swp_index.slug * {$slugWeight} ) +
									( {$prefix}swp_index.content * {$contentWeight} ) +
									( {$prefix}swp_index.comment * {$commentWeight} ) +
									( {$prefix}swp_index.excerpt * {$excerptWeight} ) +
									{$coalesceCustomFields} +
									{$coalesceTaxonomies}";

						// the identifier is different if we're attributing
						$sql .= isset( $attributedTo ) ? " AS `{$postType}attr` " : " AS `{$postType}weight` " ;

						$sql .= "
								FROM {$prefix}swp_terms FORCE INDEX (termindex)
								LEFT JOIN {$prefix}swp_index ON {$prefix}swp_terms.id = {$prefix}swp_index.term
								LEFT JOIN {$prefix}posts ON {$prefix}swp_index.post_id = {$prefix}posts.ID
								{$joinSQL}
							";

						// handle custom field weights
						if( isset( $postTypeWeights['weights']['cf'] ) && is_array( $postTypeWeights['weights']['cf'] ) && !empty( $postTypeWeights['weights']['cf'] ) )
						{
							$i = 0;
							foreach( $postTypeWeights['weights']['cf'] as $postTypeCfRecord => $postTypeCf )
							{
								$cfWeight = absint( $postTypeCf['weight'] );
								$cfName = $postTypeCf['metakey'];

								$cfClause = '';
								if( $cfName != 'searchwpcfdefault' )
									$cfClause = " AND " . $prefix . "swp_cf.metakey = '" . $cfName . "' ";

								$sql .= "
										LEFT JOIN (
											SELECT {$prefix}swp_cf.post_id, SUM({$prefix}swp_cf.count * {$cfWeight}) AS cfweight{$i}
											FROM {$prefix}swp_terms FORCE INDEX (termindex)
											LEFT JOIN {$prefix}swp_cf ON {$prefix}swp_terms.id = {$prefix}swp_cf.term
											LEFT JOIN {$prefix}posts ON {$prefix}swp_cf.post_id = {$prefix}posts.ID
											{$joinSQL}
											WHERE {$termWhere}
											{$statusSQL}
											AND {$prefix}posts.post_type = '{$postType}'
											{$excludeSQL}
											{$includeSQL}
											{$cfClause}
											{$conditionsSQL}
											GROUP BY {$prefix}swp_cf.post_id
										) cfweights{$i} USING(post_id)
									";
								$i++;
							}
						}

						// handle taxonomy weights
						if( isset( $postTypeWeights['weights']['tax'] ) && is_array( $postTypeWeights['weights']['tax'] ) && !empty( $postTypeWeights['weights']['tax'] ) )
						{
							$i = 0;
							foreach( $postTypeWeights['weights']['tax'] as $postTypeTaxName => $postTypeTaxWeight )
							{
								$postTypeTaxWeight = absint( $postTypeTaxWeight );
								$sql .= "
										LEFT JOIN (
											SELECT {$prefix}swp_tax.post_id, SUM({$prefix}swp_tax.count * {$postTypeTaxWeight}) AS taxweight{$i}
											FROM {$prefix}swp_terms FORCE INDEX (termindex)
											LEFT JOIN {$prefix}swp_tax ON {$prefix}swp_terms.id = {$prefix}swp_tax.term
											LEFT JOIN {$prefix}posts ON {$prefix}swp_tax.post_id = {$prefix}posts.ID
											{$joinSQL}
											WHERE {$termWhere}
											{$statusSQL}
											AND {$prefix}posts.post_type = '{$postType}'
											{$excludeSQL}
											{$includeSQL}
											AND {$prefix}swp_tax.taxonomy = '{$postTypeTaxName}'
											{$conditionsSQL}
											GROUP BY {$prefix}swp_tax.post_id
										) taxweights{$i} USING(post_id)
									";
								$i++;
							}
						}

						// cap off each enabled post type subquery
						$sql .= "
									WHERE {$termWhere}
									{$statusSQL}
									AND {$prefix}posts.post_type = '{$postType}'
									{$excludeSQL}
									{$includeSQL}
									{$conditionsSQL}
									GROUP BY {$prefix}posts.ID";

						if( isset( $postTypeWeights['options']['attribute_to'] ) && !empty( $postTypeWeights['options']['attribute_to'] ) )
						{
							// $attributedTo was defined in the initial conditional
							$attributedTo = absint( $postTypeWeights['options']['attribute_to'] );
							$sql .= ") `attributed{$postType}` ON $attributedTo = {$prefix}posts.ID";
						}
						else
						{
							$sql .= ") AS `{$postType}weights` ON `{$postType}weights`.post_id = {$prefix}posts.ID";
						}
					}

				}
			}

			// make sure we're only getting posts with actual weight
			$sql .= " WHERE   ";

			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && empty( $postTypeWeights['options']['attribute_to'] ) )
					$sql .= " COALESCE(`{$postType}weight`,0) +";

			foreach( $engineSettings as $postType => $postTypeWeights )
				if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true && !empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					$attributedTo = intval( $postTypeWeights['options']['attribute_to'] );
					// make sure we're not excluding the attributed post id
					if( !in_array( $attributedTo, $excludeIDs ) )
						$sql .= " COALESCE(`{$postType}attr`,0) +";
				}

			$sql = substr( $sql, 0, strlen( $sql ) - 2 );
			$sql .= " > " . absint( apply_filters( 'searchwp_weight_threshold', 0 ) ) . " ";

			$sql .= $this->postStatusLimiterSQL( $engineSettings );

			$sql .= "
					GROUP BY post_id
				";

			$sql .= " ) AS term{$termCounter} ON term{$termCounter}.post_id = {$prefix}posts.ID ";

			$termCounter++;
		}

		/**
		 * END LOOP THROUGH EACH SUBMITTED TERM
		 */


		// make sure we're only getting posts with actual weight
		$sql .= " WHERE   ";

		foreach( $engineSettings as $postType => $postTypeWeights )
		{
			if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
			{
				$termCounter = 1;
				if( empty( $postTypeWeights['options']['attribute_to'] ) )
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}weight`,0) + ";
						$termCounter++;
					}
				}
				else
				{
					foreach( $this->terms as $term )
					{
						$sql .= "COALESCE(term{$termCounter}.`{$postType}attr`,0) + ";
						$termCounter++;
					}
				}
			}
		}

		$sql = substr( $sql, 0, strlen( $sql ) - 2 );	// trim off the extra +
		$sql .= " > " . absint( apply_filters( 'searchwp_weight_threshold', 0 ) ) . " ";

		$sql .= $this->postStatusLimiterSQL( $engineSettings );

		$start = intval( ( $this->page - 1 ) * $this->postsPer );
		$total = intval( $this->postsPer );
		$order = $this->order;

		// accommodate a custom offset
		$start = absint( apply_filters( 'searchwp_query_limit_start', $start, $this->page, $this->engine ) );
		$total = absint( apply_filters( 'searchwp_query_limit_total', $total, $this->page, $this->engine ) );

		$extraWhere = apply_filters( 'searchwp_where', '', $this->engine );
		$sql .= " " . $extraWhere . " ";

		// allow developers to order by date
		$orderByDate = apply_filters( 'searchwp_return_orderby_date', false, $this->engine );
		$finalOrderBySQL = $orderByDate ? " ORDER BY post_date {$order}, finalweight {$order} " : " ORDER BY finalweight {$order}, post_date DESC ";

		// allow developers to return completely random results that meet the minumum weight
		if( apply_filters( 'searchwp_return_orderby_random', false, $this->engine ) ) {
			$finalOrderBySQL = " ORDER BY RAND() ";
		}

		$sql .= "
			GROUP BY {$prefix}posts.ID
			{$finalOrderBySQL}
		";

		if( $this->postsPer > 0 )
			$sql .= "LIMIT {$start}, {$total}";

		$sql = str_replace( "\n", " ", $sql );
		$sql = str_replace( "\t", " ", $sql );

		// allow BIG_SELECTS
		$bigSelects = apply_filters( 'searchwp_big_selects', false );
		if( $bigSelects ) {
			$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
		}

		$postIDs = $wpdb->get_col( $sql );

		do_action( 'searchwp_log', 'Search results: ' . var_export( $postIDs, true ) );

		// retrieve how many total posts were found without the limit
		$this->foundPosts = $wpdb->get_var(
			apply_filters_ref_array(
				'found_posts_query',
				array( 'SELECT FOUND_ROWS()', &$wpdb )
			)
		);

		// store an accurate max_num_pages for $wp_query
		$this->maxNumPages = ceil( $this->foundPosts / $this->postsPer );

		// store our post IDs
		$this->postIDs = $postIDs;

		return true;
	}


	private function postStatusLimiterSQL( $engineSettings )
	{
		global $wpdb;

		$prefix = $wpdb->prefix;
		$sql    = '';

		// add more limiting
		$finalPostTypes = array();
		$finalPostTypesIncludesAttachments = false;
		foreach( $engineSettings as $postType => $postTypeWeights )
			if( isset( $postTypeWeights['enabled'] ) && $postTypeWeights['enabled'] == true )
			{
				if( $postType == 'attachment' )
					$finalPostTypesIncludesAttachments = true;
				else
					$finalPostTypes[] = $postType;
			}

		$sql .= " AND ( ";
		$sql .= " ( {$prefix}posts.post_status  IN ( '" . implode( "', '", $this->post_statuses ) . "' )  AND {$prefix}posts.post_type IN ('" . implode( "','", $finalPostTypes ) . "') ) ";

		if( $finalPostTypesIncludesAttachments )
			$sql .= " OR {$prefix}posts.post_type = 'attachment' ";

		$sql .= " ) ";

		return $sql;
	}


	/**
	 * Returns the maximum number of pages of results
	 *
	 * @return int
	 * @since 1.0.5
	 */
	function getMaxNumPages()
	{
		return $this->maxNumPages;
	}


	/**
	 * Returns the number of found posts
	 *
	 * @return int
	 * @since 1.0.5
	 */
	function getFoundPosts()
	{
		return $this->foundPosts;
	}


	/**
	 * Returns the number of the current page of results
	 *
	 * @return int
	 * @since 1.0.5
	 */
	function getPage()
	{
		return $this->page;
	}

}
