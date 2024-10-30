<?php
/**
 * Matador / Bullhorn API / Import Jobs
 *
 * Extends Bullhorn_Connection and imports jobs into the WordPress CPT.
 *
 * - Names that begin with get_ retrieve data, mostly from Bullhorn.
 * - Names that begin with save_
 * - Names that begin with the_
 *
 * @link        http://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @subpackage  Bullhorn API
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017, Jeremy Scott
 *
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

namespace matador;

use stdClass;

// Exit if accessed directly or if parent class doesn't exist.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Bullhorn_Import extends Bullhorn_Connection {

	/**
	 * @var array
	 */
	private $organization_url_cache;

	/**
	 * Constructor
	 *
	 * Child class constructor class calls parent
	 * constructor to set up some variables and logs
	 * us into Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @throws Exception
	 */
	public function __construct() {
		parent::__construct();
		try {
			$this->login();
		} catch ( Exception $e ) {

			new Event_Log( $e->getName(), $e->getMessage() );
			Admin_Notices::add( esc_html__( 'Login into Bullhorn failed see log for more info.', 'matador-jobs' ), 'warning', 'bullhorn-login-exception' );
		}

		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_categories' ), 5, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_type' ), 10, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_address' ), 20, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_location' ), 22, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_meta' ), 25, 2 );
		add_action( 'matador_bullhorn_import_save_job', array( $this, 'save_job_jsonld' ), 30, 2 );

		add_filter( 'matador_save_job_meta', array( $this, 'matador_save_job_meta' ), 10, 2 );

	}

	/**
	 * Sync
	 *
	 * This is THE method that does all the import magic. This is the only
	 * method publicly callable.
	 *
	 * @param bool $sync_tax
	 * @throws Exception
	 * @return bool
	 *
	 * @since 3.0.0
	 */
	public function sync( $sync_tax = true ) {

		add_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );
		set_transient( Matador::variable( 'doing_sync', 'transients' ), true, 5 * MINUTE_IN_SECONDS );

		Logger::add( 'info', 'sync_start', __( 'Starting Sync with bullhorn.', 'matador-jobs' ) );

		/**
		 * Action - Matador Bullhorn Before Import
		 *
		 * @since 3.1.0
		 */
		do_action( 'matador_bullhorn_before_import' );

		if ( is_null( $this->url ) ) {
			Logger::add( 'info', 'sync_not_logged_in', __( 'Bullhorn not logged in and cannot import.', 'matador-jobs' ) );

			return false;
		}

		if ( $sync_tax ) {
			$this->save_taxonomy_terms();
		}

		$remote_jobs = $this->get_remote_jobs();
		$local_jobs  = $this->get_local_jobs();

		if ( is_array( $remote_jobs ) ) {
			$this->save_jobs( $remote_jobs, $local_jobs );
		}

		$expired_jobs = $this->get_expired_jobs( $remote_jobs, $local_jobs );

		if ( is_array( $expired_jobs ) ) {
			$this->destroy_jobs( $expired_jobs );
		}

		/**
		 * Action - Matador Bullhorn After Import
		 *
		 * @since 3.1.0
		 */
		do_action( 'matador_bullhorn_after_import' );

		$now = date( 'G:i j-M-Y T' ) . '.';

		Admin_Notices::add( esc_html__( 'Bullhorn Jobs Sync completed successfully at ', 'matador-jobs' ) . $now, 'success', 'bullhorn-sync' );

		Logger::add( 'info', 'sync_finish', __( 'Finished Sync with bullhorn.', 'matador-jobs' ) );

		remove_filter( 'matador_bullhorn_doing_jobs_sync', '__return_true' );
		delete_transient( Matador::variable( 'doing_sync', 'transients' ) );
		return true;
	}

	/**
	 * Get Jobs
	 *
	 * This retrieves all available jobs from Bullhorn.
	 *
	 * @throws Exception
	 * @since 3.0.0
	 */
	private function get_remote_jobs() {

		while ( true ) {

			// Things we need
			$limit  = 20;
			$offset = isset( $offset ) ? $offset : 0;
			$jobs   = isset( $jobs ) ? $jobs : array();

			// API Method
			$request = 'query/JobOrder';

			// API Method Parameters
			$params = array(
				'fields' => $this->the_jobs_fields(),
				'where'  => $this->the_jobs_where(),
				'count'  => $limit,
				'start'  => $offset,
			);

			// API Call
			$response = $this->request( $request, $params );

			// Process API Response
			if ( isset( $response->data ) ) {

				// Merge Results Array with Return Array
				$jobs = array_merge( $jobs, $response->data );

				if ( count( $response->data ) < $limit ) {
					// If the size of the result is less than the results per page
					// we got all the jobs, so end the loop
					break;
				} else {
					// Otherwise, increment the offset by the results per page, and re-run the loop.
					$offset += $limit;
				}
			} elseif ( is_wp_error( $response ) ) {
				throw new Exception( 'error', 'bullhorn-import-request-jobs-timeout', esc_html__( 'Operation timed out', 'matador-jobs' ) );
			} else {
				break;
			}
		} // End while().

		if ( empty( $jobs ) ) {
			return false;
		} else {
			// Translators: Placeholder is for number of found jobs.
			new Event_Log( 'bullhorn-import-found-jobs-count', esc_html( sprintf( __( 'Sync found %1$s jobs.', 'matador-jobs' ), count( $jobs ) ) ) );

			return $jobs;
		}

	}

	/**
	 * Get Existing Jobs
	 *
	 * This retrieves all existing jobs from WordPress with a Bullhorn Job ID meta field
	 * and returns an array of Bullhorn IDs with the value of WordPress IDs.
	 *
	 * @since 3.0.0
	 * @return boolean|array
	 */
	private function get_local_jobs() {

		while ( true ) {

			// Things we need
			$limit    = 100;
			$offset   = isset( $offset ) ? $offset : 0;
			$existing = isset( $existing ) ? $existing : array();

			// WP Query Args.
			$args = array(
				'post_type'      => Matador::variable( 'post_type_key_job_listing' ),
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'meta_key'       => 'bullhorn_job_id',
			);

			// WP Query
			$posts = new \WP_Query( $args );

			if ( $posts->have_posts() && ! is_wp_error( $posts ) ) {

				while ( $posts->have_posts() ) {
					$posts->the_post();

					$bh_id = get_post_meta( get_the_id(), 'bullhorn_job_id', true );
					$wp_id = get_the_id();

					$existing[ $bh_id ] = $wp_id;
				}

				// If the size of the result is less than the limit, break, otherwise increment and re-run
				if ( $posts->post_count < $limit ) {
					break;
				} else {
					$offset += $limit;
				}
			} else {
				break;
			}
		} // End while().

		wp_reset_postdata();

		if ( empty( $existing ) ) {
			Logger::add( 'notice', 'matador-import-existing-none', __( 'No existing jobs were found', 'matador-jobs' ) );
			return false;
		} else {
			// Translators: placeholder is number of jobs found.
			Logger::add( 'notice', 'matador-import-existing-found', sprintf( __( '%s existing jobs were found', 'matador-jobs' ), count( $existing ) ) );
			return $existing;
		}

	}

	/**
	 * Get Expired Jobs
	 *
	 * This takes the list of remote jobs and the list of local jobs and
	 * creates an array of remote jobs that exist in the local storage also.
	 * It then compares that list to the list of all local jobs to create a
	 * list of local jobs that don't exist remotely, and therefore should be
	 * removed. This process saves us from running another WP_Query.
	 *
	 * @since 3.0.0
	 *
	 * @param object $remote_jobs
	 * @param array $local_jobs
	 *
	 * @return array (empty or populated)
	 */
	private function get_expired_jobs( $remote_jobs = null, $local_jobs = null ) {

		$current_jobs = array();
		$expired_jobs = array();

		if ( $remote_jobs && $local_jobs ) {
			foreach ( $remote_jobs as $job ) {
				if ( array_key_exists( $job->id, $local_jobs ) ) {
					$current_jobs[] = $local_jobs[ $job->id ];
				}
			}
			foreach ( $local_jobs as $bhid => $wpid ) {
				if ( ! in_array( $wpid, $current_jobs, true ) ) {
					$expired_jobs[] = $wpid;
				}
			}
		}

		return $expired_jobs;

	}

	/**
	 * Get Categories
	 *
	 * This retrieves all available jobs from Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $job_id optional, if passed requests categories for only single job.
	 *
	 * @throws Exception
	 *
	 * @return array
	 */
	private function get_category_terms( $job_id = null ) {
		$cache_key = 'matador_bullhorn_categories_list' . (  null !== $job_id ) ? '_' . $job_id : '';
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}
		// API Method
		$request = $job_id ? 'entity/JobOrder/' . $job_id . '/categories' : 'options/Category';

		// API Method Parameters
		$params = array(
			'fields' => $job_id ? 'name' : 'label',
		);

		// Only Get Enabled
		if ( ! $job_id ) {
			$params['where'] = 'enabled=true';
		}

		// Submit Request
		$response = $this->request( $request, $params );

		// Format response into array
		$result = array();
		$name   = $job_id ? 'name' : 'label';
		foreach ( $response->data as $category ) {
			$result[] = $category->{$name};
		}
		set_transient( $cache_key, $result, MINUTE_IN_SECONDS * 15 );

		// Return Categories
		return $result;

	}

	/**
	 * Get Countries
	 *
	 * Bullhorn stores country as an ID and not as a name.
	 * So we need to format country data into an array of
	 * IDs and names.
	 *
	 * @throws Exception
	 *
	 * @return array|boolean;
	 */
	public function get_countries() {

		$cache_key = 'matador_bullhorn_countries_list';
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		// API Method
		$request = 'options/Country';

		// API Method Parameters
		$params = array(
			'count'  => '300',
			'fields' => 'value,label',
		);

		// API Call
		$response = $this->request( $request, $params );

		if ( ! is_wp_error( $response ) ) {

			$country_list = array();
			foreach ( $response->data as $country ) {
				$country_list[ $country->value ] = $country->label;
			}
		} else {

			return false;

		}

		set_transient( $cache_key, $country_list, HOUR_IN_SECONDS * 1 );

		return $country_list;

	}

	/**
	 * Save Taxonomy Terms
	 *
	 * @throws Exception
	 */
	public function save_taxonomy_terms() {

		Logger::add( 'info', 'sync_bullhorn_tax_to_wp', __( 'Starting taxonomies sync.', 'matador-jobs' ) );

		do_action( 'matador_bullhorn_import_save_taxonomies', $this );

		$category = Matador::variable( 'category', 'job_taxonomies' );
		$this->save_taxonomy( $this->get_category_terms(), $category['key'] );

		// Translators: Placeholder for datettime.
		// Admin_Notices::add( sprintf( esc_html__( 'Taxonomies Sync completed successfully at %s', 'matador-jobs' ), date( 'G:i j-M-Y T' ) ), 'success', 'bullhorn-sync' );
		Logger::add( 'info', 'sync_bullhorn_tax_to_wp', __( 'Finished Category Sync with bullhorn.', 'matador-jobs' ) );
	}

	/**
	 * Get Hiring Organization URL
	 *
	 * Bullhorn stores HiringOrganization as an ID and to
	 * get data from that, we need to separately query the
	 * HiringOrganization via its ID.
	 *
	 * @param integer $organization_id ID from Bullhorn for Organization
	 *
	 * @throws Exception
	 *
	 * @return boolean|string;
	 */
	private function get_hiring_organization_url( $organization_id = null ) {

		// Requires an Org ID
		if ( empty( $organization_id ) ) {
			return false; //error
		}

		$cache_key = 'matador_import_organization_urls';

		if ( is_null( $this->organization_url_cache ) ) {
			$this->organization_url_cache = get_transient( $cache_key );
		}

		if ( is_array( $this->organization_url_cache ) && array_key_exists( $organization_id, $this->organization_url_cache ) ) {
			return $this->organization_url_cache[ $organization_id ];
		}

		// Translators: placeholder for organization ID
		new Event_Log( 'matador_import_get_hiring_organization_url', esc_html( sprintf( __( 'Requesting hiring URL for job id #%s', 'matador-jobs' ), $organization_id ) ) );

		// API Method
		$request = 'entity/ClientCorporation/' . $organization_id;

		// API Method Parameters
		$params = array(
			'fields' => 'companyURL',
		);

		// API Call
		$response = $this->request( $request, $params );

		// Handle Response
		if ( ! is_wp_error( $response ) ) {
			if ( isset( $response->data->companyURL ) ) {
				$organization_url = ( isset( $response->data->companyURL ) || empty( $response->data->companyURL ) ) ? $response->data->companyURL : null;
			} else {
				$organization_url = '';
			}

			$this->organization_url_cache[ $organization_id ] = $organization_url;
			set_transient( $cache_key, $this->organization_url_cache, HOUR_IN_SECONDS * 24 );

			return $organization_url;
		}

		return null;
	}

	/**
	 * Save Jobs
	 *
	 * Given an array existing jobs and an array of retrieved jobs,
	 * save jobs to database.
	 *
	 * @param array $jobs
	 * @param array $existing
	 *
	 * @return boolean
	 * @since 2.1
	 */
	private function save_jobs( $jobs, $existing ) {

		$cache_key = Matador::variable( 'bullhorn_import_jobs_done', 'transients' );

		if ( ! empty( $jobs ) ) {

			wp_defer_term_counting( true );

			foreach ( $jobs as $job ) {

				$ids_done = get_transient( $cache_key );

				$ids_done = ( false === $ids_done ) ? array() : $ids_done;

				if ( in_array( $job->id, $ids_done, true ) ) {
					// Translators:: placeholder 1 is Job ID
					new Event_Log( 'bullhorn-import-new-job-skip', esc_html( sprintf( __( 'Bullhorn Job #%1$s is was in recent synced list skipping this time.', 'matador-jobs' ), $job->id ) ) );
					continue;
				}

				$job->dateAdded = self::timestamp_to_epoch( $job->dateAdded ); // @codingStandardsIgnoreLine (SnakeCase)
				$job->dateEnd   = self::timestamp_to_epoch( $job->dateEnd ); // @codingStandardsIgnoreLine (SnakeCase)

				$wpid = isset( $existing[ $job->id ] ) ? $existing[ $job->id ] : null;

				if ( $wpid && ! ( apply_filters( 'matador_bullhorn_import_overwrite_job_on_sync', true ) ) ) {
					// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
					new Event_Log( 'bullhorn-import-skip-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is skipped.', 'matador-jobs' ), $job->id, $wpid ) ) );
					continue;
				}

				if ( $wpid ) {
					// Translators: Placeholders are for Bullhorn ID and WordPress Post ID
					new Event_Log( 'bullhorn-import-overwrite-save-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s exists as WP post #%2$s and is updated.', 'matador-jobs' ), $job->id, $wpid ) ) );
				}

				if ( ! $wpid ) {
					// Translators: Placeholders are for Bullhorn ID
					new Event_Log( 'bullhorn-import-new-job', esc_html( sprintf( __( 'Bullhorn Job #%1$s is new and will be added.', 'matador-jobs' ), $job->id, $wpid ) ) );
				}

				$wpid = $this->save_job( $job, $wpid );

				if ( ! is_wp_error( $wpid ) ) {
					do_action( 'matador_bullhorn_import_save_job', $job, $wpid, $this );

					if ( ! isset( $existing[ $job->id ] ) ) {
						do_action( 'matador_bullhorn_import_save_new_job', $job, $wpid, $this );
					}
				} else {
					Logger::add( '5', esc_html__( 'Unable to save job.', 'matador-jobs' ) );

					return false;
				}
				$ids_done[] = $job->id;
				set_transient( $cache_key, $ids_done, MINUTE_IN_SECONDS * 10 );
			}

			wp_defer_term_counting( false );

			delete_transient( $cache_key );

		} // End if().

		return true;
	}

	/**
	 * Insert or Update Job into WordPress
	 *
	 * Given a job object and an optional WP post ID,
	 * insert or add a job post type post to WordPress.
	 *
	 * @param array|object $job
	 * @param integer $wpid
	 *
	 * @return integer WP post ID
	 * @since 3.0.0
	 */
	public function save_job( $job, $wpid = null ) {

		$status = ( null !== $wpid ) ? get_post_status( $wpid ) : apply_filters( 'matador_bullhorn_import_job_status', 'publish' );

		// wp_insert_post args
		$args = array(
			/**
			 * Filter : Matador Import Job Title
			 *
			 * Filter the imported job title. Useful to replace, prepend, or append the title.
			 *
			 * @since 3.4.0
			 * @param string
			 */
			'post_title'   => apply_filters( 'matador_import_job_title', $job->{$this->the_jobs_title_field()} ),
			/**
			 * Filter : Matador Import Job Description
			 *
			 * Filter the imported job description. Useful to replace, prepend, or append the title.
			 *
			 * @since 3.4.0
			 * @param string
			 */
			'post_content' => wp_kses( apply_filters( 'matador_import_job_description', $job->{$this->the_jobs_description_field()} ), $this->the_jobs_description_allowed_tags() ),
			'post_type'    => Matador::variable( 'post_type_key_job_listing' ),
			'post_name'    => $this->the_jobs_slug( $job ),
			'post_date'    => Helper::format_datetime_to_mysql( $job->dateAdded ), // @codingStandardsIgnoreLine (SnakeCase)
			'post_status'  => $status,
		);

		// if this is an existing job, add the ID, else set the status (publish, draft, etc) of the imported job
		if ( $wpid ) {
			$args['ID'] = $wpid;
		}

		return wp_insert_post( $args );
	}

	/**
	 * Save Job Categories
	 *
	 * @param object $job
	 * @param integer $wpid
	 *
	 * @throws Exception
	 *
	 * @return boolean
	 */
	public function save_job_categories( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return false;
		}
		$taxonomies = Matador::variable( 'job_taxonomies' );

		if ( ! isset( $taxonomies['category'] ) ) {

			return false;
		}


		$count      = $job->categories->total;
		$categories = array();

		if ( 0 === $count ) {

			return false;
		}

		if ( ( 0 < $count ) && ( $count <= 5 ) ) {
			foreach ( $job->categories->data as $category ) {
				$categories[] = $category->name;
			}
		} else {
			Logger::add( 'info', 'starting_term_import', esc_html__( 'More than 5 terms doing a full term sync', 'matador-jobs' ) );
			$categories = $this->get_category_terms( $job->id );
		}

		// Need this for the JSON LD builder
		set_transient( 'matador_import_categories_job_' . $wpid, $categories );

		return wp_set_object_terms( $wpid, $categories, $taxonomies['category']['key'] );
	}

	/**
	 * Save Job Types
	 *
	 * @param object $job
	 * @param integer $wpid
	 *
	 * @return boolean
	 */
	public function save_job_type( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return false;
		}

		if ( isset( $job->employmentType ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$taxonomies = Matador::variable( 'job_taxonomies' );

			if ( isset( $taxonomies['type']['key'] ) ) {

				return wp_set_object_terms( $wpid, $job->employmentType, $taxonomies['type']['key'] ); // @codingStandardsIgnoreLine (SnakeCase)
			}
		}

		return true;
	}

	/**
	 * Save Job Meta
	 *
	 *
	 * @param object $job
	 * @param int $wpid
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Added support for saveas = object, flatten an array to a string when saveas = meta
	 */
	public function save_job_meta( $job = null, $wpid = null ) {
		if ( ! $job || ! $wpid ) {
			return;
		}
		$all_fields = $this->the_jobs_fields( 'array' );

		$job_meta_fields = array();

		foreach ( $all_fields as $key => $field ) {

			if ( ! empty( $job->{$key} ) ) {

				$meta_key = array_key_exists( 'name', $field ) ? esc_attr( $field['name'] ) : esc_attr( $key );

				$save_as_meta = false;
				$save_as_object = false;

				if ( array_key_exists( 'saveas', $field ) ) {
					if ( is_array( $field['saveas'] ) ) {
						if ( in_array( 'meta', $field['saveas'], true ) ) {
							$save_as_meta = true;
						}
						if ( in_array( 'object', $field['saveas'], true ) ) {
							$save_as_object = true;
						}
					} elseif ( is_string( $field['saveas'] ) ) {
						if ( 'meta' === strtolower( $field['saveas'] ) ) {
							$save_as_meta = true;
						} elseif ( 'object' === strtolower( $field['saveas'] ) ) {
							$save_as_object = true;
						}
					}
				}

				$meta = $job->{$key};

				if ( $save_as_meta ) {

					if ( is_array( $meta ) ) {
						/**
						 * Filter: Matador Import Meta Item Separator
						 *
						 * When an array or object is sent with job data and the user wishes to include it into post
						 * meta via a string, Matador will flatten the values into a string separated, by default, with
						 * a comma followed by a space. Change the comma and space to another separator with this
						 * filter.
						 *
						 * @since 3.4.0
						 *
						 * @param string         $separator
						 * @param string         $meta_key
						 * @param stdClass|array $value
						 *
						 * @return string
						 */
						$separator = apply_filters( 'matador_import_meta_item_separator', ', ', $meta_key, $meta );
						$meta = implode( $separator, $meta );
					}

					$job_meta_fields[ $meta_key ] = $meta;
				}

				if ( $save_as_object ) {
					if ( is_array( $meta ) ) {
						$job_meta_fields[ '_' . $meta_key ] = $meta;
					} elseif ( is_string( $meta ) ) {
						$job_meta_fields[ '_' . $meta_key ] = preg_split( '/(\s*,\s*)*,+(\s*,\s*)*/', $meta );
					}
				}
			}
		}

		$job_meta_fields['hiringOrganizationURL'] = $this->get_hiring_organization_url( $job->clientCorporation->id ); // @codingStandardsIgnoreLine (SnakeCase)

		foreach ( $job_meta_fields as $key => $val ) {

			update_post_meta( $wpid, $key, apply_filters( 'matador_save_job_meta', $val, $key, $wpid ) );
		}
	}

	/**
	 * @param object $job
	 * @param int $wpid
	 * @throws Exception
	 */
	public function save_job_address( $job = null, $wpid = null ) {
		if ( ! $job || ! $wpid ) {
			return;
		}

		$city       = isset( $job->address->city ) ? $job->address->city : null;
		$state      = isset( $job->address->state ) ? $job->address->state : null;
		$zip        = isset( $job->address->zip ) ? $job->address->zip : null;
		$country_id = isset( $job->address->countryID ) ? $job->address->countryID : null;  // @codingStandardsIgnoreLine (SnakeCase)
		$country    = $country_id ? $this->the_job_country_name( $country_id ) : null;

		// Some Formatting Help
		$comma = ( $city && $state ) ? ', ' : '';
		$space = ( $city || $state ) && $zip ? ' ' : '';
		$dash  = ( ( $city || $state || $zip ) && $country ) ? ' - ' : '';

		if ( $city ) {
			update_post_meta( $wpid, 'bullhorn_city', $city );
		}
		if ( $state ) {
			update_post_meta( $wpid, 'bullhorn_state', $state );
		}
		if ( $country ) {
			update_post_meta( $wpid, 'bullhorn_country', $country );
		}
		if ( $zip ) {
			update_post_meta( $wpid, 'bullhorn_zip', $zip );
		}

		$location_string = sprintf( '%s%s%s%s%s%s%s', $city, $comma, $state, $space, $zip, $dash, $country );
		update_post_meta( $wpid, 'bullhorn_job_location', $location_string );

		$location_data = array(
			'city'    => $city,
			'state'   => $state,
			'zip'     => $zip,
			'country' => $country,
		);
		$general_location = apply_filters( 'matador_import_job_general_location', $city . $comma . $state, $location_data );

		update_post_meta( $wpid, 'job_general_location', $general_location );

		do_action( 'matador_save_job_address', $location_string, $wpid, array(
			'city' => $city,
			'state' => $state,
			'zip' => $zip,
			'country' => $country,
		) );
	}

	public function save_job_location( $job = null, $wpid = null ) {

		if ( ! $job || ! $wpid ) {
			return;
		}

		$allowed = apply_filters( 'matador_import_location_taxonomy_allowed_fields', array( 'city', 'state', 'zip', 'country' ) );

		$field = apply_filters( 'matador_import_location_taxonomy_field', 'city' );
		$field = in_array( $field, $allowed, true ) ? $field : 'city';

		$taxonomies = Matador::variable( 'job_taxonomies' );

		if ( isset( $taxonomies['location']['key'] ) ) {
			$this->save_job_meta_to_tax( $field, $taxonomies['location']['key'], $wpid );
		}

		// You may declare separate taxonomies for the other location fields by
		// creating a taxonomy with the keyname of city, state, zip, or country.
		foreach ( $allowed as $meta ) {
			if ( array_key_exists( $meta, $taxonomies ) ) {
				$this->save_job_meta_to_tax( $meta, $taxonomies[ $meta ]['key'], $wpid );
			}
		}
	}

	public function save_job_meta_to_tax( $field, $taxonomy, $wpid ) {

		$value = get_post_meta( $wpid, 'bullhorn_' . $field, true );

		if ( ! empty( $value ) ) {
			wp_set_object_terms( $wpid, apply_filters( 'matador_import_meta_to_taxonomy_value', $value, $taxonomy ), $taxonomy );
		}
	}

	/**
	 * Format Job As JSON LD
	 *
	 * @since 3.0.0
	 *
	 * @throws Exception
	 *
	 * @param object $job
	 * @param integer $wpid
	 */
	public function save_job_jsonld( $job = null, $wpid = null ) {
		if ( ! $job || ! $wpid ) {
			return;
		}

		$ld                                    = array();
		$ld['@context']                        = 'http://schema.org';
		$ld['@type']                           = 'JobPosting';
		$ld['title']                           = $job->title;
		$ld['description']                     = $job->{$this->the_jobs_description_field()};
		$ld['datePosted']                      = Helper::format_datetime_to_8601( $job->dateAdded ); // @codingStandardsIgnoreLine (SnakeCase)
		$ld['jobLocation']['@type']            = 'Place';
		$ld['jobLocation']['address']['@type'] = 'PostalAddress';
		$ld['hiringOrganization']['@type']     = 'Organization';

		if ( null !== $job->dateEnd && 0 < date_diff( date_create( $job->dateAdded ), date_create( $job->dateEnd ) ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['validThrough'] = Helper::format_datetime_to_8601( $job->dateEnd ); // @codingStandardsIgnoreLine (SnakeCase)
		} else {
			$d = $job->dateAdded; // @codingStandardsIgnoreLine (SnakeCase)
			$d->modify( '+ 1 years' );
			$ld['validThrough'] = Helper::format_datetime_to_8601( $d );
		}

		// Append $ld['jobLocation']
		if ( ! empty( $job->address->city ) ) {
			$ld['jobLocation']['address']['addressLocality'] = $job->address->city;
		}
		if ( ! empty( $job->address->state ) ) {
			$ld['jobLocation']['address']['addressRegion'] = $job->address->state;
		}
		if ( ! empty( $job->address->zip ) ) {
			$ld['jobLocation']['address']['postalCode'] = $job->address->zip;
		}
		if ( $this->the_job_country_name( $job->address->countryID ) ) {
			$ld['jobLocation']['address']['addressCountry'] = $this->the_job_country_name( $job->address->countryID );
		}

		$categories = get_transient( 'matador_import_categories_job_' . $wpid );
//		delete_transient( 'matador_import_categories_job_' . $wpid );
		if ( is_array( $categories ) ) {
			$ld['occupationalCategory'] = implode( ',', $categories );
		}

		if ( 'company' === Matador::setting( 'jsonld_hiring_organization' ) ) {
			if ( isset( $job->clientCorporation->name ) ) {  // @codingStandardsIgnoreLine (SnakeCase)
				$ld['hiringOrganization']['name'] = apply_filters( 'matador_get_hiring_organization_name', $job->clientCorporation->name, $wpid, $job ); // @codingStandardsIgnoreLine (SnakeCase)
			}
			if ( isset( $job->clientCorporation->id ) && self::get_hiring_organization_url( $job->clientCorporation->id ) ) { // @codingStandardsIgnoreLine (SnakeCase)
				$ld['hiringOrganization']['sameAs'] = apply_filters( 'matador_get_hiring_organization_url', self::get_hiring_organization_url( $job->clientCorporation->id ), $wpid, $job ); // @codingStandardsIgnoreLine (SnakeCase)
			}
		}
		if ( empty( $ld['hiringOrganization']['name'] ) ) {
			$ld['hiringOrganization']['name'] = get_bloginfo( 'name' );
		}
		if ( empty( $ld['hiringOrganization']['sameAs'] ) ) {
			$ld['hiringOrganization']['sameAs'] = get_bloginfo( 'url' );
		}


		// Kitchen Sink
		if ( isset( $job->educationDegree ) && ! empty( $job->educationDegree ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['educationRequirements'] = $job->educationDegree; // @codingStandardsIgnoreLine (SnakeCase)
		}
		if ( isset( $job->degreeList ) && ! empty( $job->degreeList ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['educationRequirements'] = $job->degreeList; // @codingStandardsIgnoreLine (SnakeCase)
		}
		if ( isset( $job->employmentType ) && ! empty( $job->employmentType ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['employmentType'] = $job->employmentType; // @codingStandardsIgnoreLine (SnakeCase)
		}
		if ( isset( $job->benefits ) && ! empty( $job->benefits ) ) {
			$ld['jobBenefits'] = $job->benefits;
		}

		if ( Matador::setting( 'jsonld_salary' ) && ( isset( $job->salary ) || isset( $job->payRate ) ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['baseSalary']['@type']          = 'MonetaryAmount';
			$ld['baseSalary']['currency']       = self::get_bullorn_currency_format();
			$ld['baseSalary']['value']['@type'] = 'QuantitativeValue';
			if ( 0 < $job->salary ) {
				$ld['baseSalary']['value']['value'] = $job->salary;
			} elseif ( 0 < $job->payRate ){ // @codingStandardsIgnoreLine (SnakeCase)
				$ld['baseSalary']['value']['value'] = $job->payRate;
			}
			if ( isset( $job->salaryUnit ) && ! empty( $job->salaryUnit ) ) { // @codingStandardsIgnoreLine (SnakeCase)
				$ld['baseSalary']['value']['unitText'] =  $job->salaryUnit; // @codingStandardsIgnoreLine (SnakeCase)
			}
		}



		if ( isset( $job->yearsRequired ) && ! empty( $job->yearsRequired ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['experienceRequirements'] = $job->yearsRequired; // @codingStandardsIgnoreLine (SnakeCase)
		}
		if ( isset( $job->bonusPackage ) && ! empty( $job->bonusPackage ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$ld['incentiveCompensation'] = $job->bonusPackage; // @codingStandardsIgnoreLine (SnakeCase)
		}

		$ld = apply_filters( 'matador_bullhorn_import_save_job_jsonld', $ld, $job, $this );

		update_post_meta( $wpid, 'jsonld', $ld );
	}

	/**
	 * Save Taxonomy Terms
	 *
	 * This will take an array of items from Bullhorn and insert it into
	 * WordPress as taxonomy terms.
	 *
	 * @param array $terms
	 * @param string $taxonomy
	 *
	 * @since 2.1
	 */
	private function save_taxonomy( $terms = null, $taxonomy ) {
		if ( isset( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_insert_term( $term, $taxonomy );
			}
		}
	}

	/**
	 * get_currency format
	 *
	 * This gets the Currency format set in Bullhorn.
	 *
	 *
	 * @since 3.0.6
	 */
	private function get_bullorn_currency_format() {
		$cache_key = 'matador_currency_format';

		$currency_format = get_transient( $cache_key );

		If( false === $currency_format ){
			new Event_Log( 'matador_import_get_currency_format', esc_html( sprintf( __( 'Requesting currency Format', 'matador-jobs' ) ) ) );

			// API Method
			$request = 'settings/currencyFormat';

			// API Method Parameters
			$params = array(
			);

			// API Call
			$response = $this->request( $request, $params );
			// Handle Response
			if ( ! is_wp_error( $response ) ) {
				if ( isset( $response->currencyFormat ) ) {
					$currency_format = $response->currencyFormat;
					set_transient( $cache_key, $currency_format, HOUR_IN_SECONDS * 24 );
				}else{
					$currency_format = '';
				}
			}
		}

		return $currency_format;
	}



	public function matador_save_job_meta( $data, $meta_id ){

		switch ( $meta_id ){

			case 'assignedUsers';
				foreach ( $data->data as $key => $user ){
					$data->data[$key]->email = self::get_email_user_id( $user->id );
				}

				break;
			case 'owner';
					$data->email = self::get_email_user_id( $data->id );
				break;

		}

		return $data;
	}


	private function get_email_user_id( $id ){

		$cache_key = 'matador_user_email';

		$email = get_transient( $cache_key );

		if ( false === $email || ! isset( $email[ $id ] ) ) {
			new Event_Log( 'matador_import_get_email_for_user_id', esc_html( sprintf( __( 'Requesting user data', 'matador-jobs' ) ) ) );

			// API Method
			$request = 'entity/CorporateUser/' . $id;

			// API Method Parameters
			$params = array(
				'fields' => 'email',
			);

			// API Call
			$response = $this->request( $request, $params );
			// Handle Response
			if ( ! is_wp_error( $response ) ) {
				if ( isset( $response->data->email ) ) {
					$email[ $id ] = $response->data->email;
					set_transient( $cache_key, $email, HOUR_IN_SECONDS * 24 );
				}else{
					return '';
				}
			}
		}


		return $email[ $id ];
	}
	/**
	 * Before we start adding in new jobs, we need to delete jobs that are no
	 * longer in Bullhorn.
	 *
	 * @since 3.0.0
	 *
	 * @param array $jobs
	 */
	private function destroy_jobs( $jobs = array() ) {

		if ( ! empty( $jobs ) ) {
			foreach ( $jobs as $job ) {
				// Translators: placeholder is Job ID
				Logger::add( 'info', 'destroy_jobs', sprintf( esc_html__( 'Delete Job(%1$s).', 'matador-jobs' ), $job ) );
				wp_delete_post( $job, true );
			}
		}

	}

	/**
	 * Jobs Request "Fields"
	 *
	 * Prepares the "fields" clause for the Bullhorn Jobs Request.
	 * Uses settings and filters to prepare it nicely.
	 *
	 * @since 3.0.0
	 *
	 * @param string $format format to return
	 *
	 * @return string|array
	 */
	private function the_jobs_fields( $format = 'string' ) {

		$fields = apply_filters( 'matador_bullhorn_import_fields', array() );
		$fields = array_merge( array(
			'id'                                => array(
				'type'   => 'integer',
				'saveas' => array( 'core', 'meta' ),
				'name'   => 'bullhorn_job_id',
			),
			$this->the_jobs_title_field()       => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			$this->the_jobs_description_field() => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			'dateAdded'                         => array(
				'type'   => 'time',
				'saveas' => 'core',
			),
			'status'                            => array(
				'type'   => 'string',
				'saveas' => 'core',
			),
			'address'                           => array(
				'type'   => 'address',
				'saveas' => 'core',
			),
			'categories'                        => array(
				'type'   => 'association',
				'saveas' => 'core',
			),
			'clientCorporation'                 => array(
				'type'   => 'association',
				'saveas' => 'core',
			),
			'dateEnd'                           => array(
				'type'   => 'time',
				'saveas' => 'meta',
			),
			'benefits'                          => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'salary'                            => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'salaryUnit'                        => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'educationDegree'                   => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'employmentType'                    => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'yearsRequired'                     => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'degreeList'                        => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'bonusPackage'                      => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'payRate'                           => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'taxStatus'                         => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'travelRequirements'                => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'willRelocate'                      => array(
				'type'   => 'string',
				'saveas' => 'meta',
			),
			'notes'                             => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'assignedUsers'                     => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
			'owner'                             => array(
				'type'   => 'association',
				'saveas' => 'meta',
			),
		), $fields );

		if ( 'string' === $format ) {
			return implode( ',', array_keys( $fields ) );
		} else {
			return $fields;
		}
	}

	/**
	 * Jobs Request "Where"
	 *
	 * Prepares the "where" clause for the Bullhorn Jobs Request.
	 * Uses the settings and filters to prepare it.
	 *
	 * @since 3.0.0
	 * @return string 'where' clause.
	 */
	private function the_jobs_where() {

		$where = 'isOpen=true AND isDeleted=false AND status<>\'Archive\'';

		switch ( Matador::setting( 'bullhorn_is_public' ) ) {
			case 'all':
				break;
			case 'submitted':
				$where .= ' AND ( isPublic=1 OR isPublic=-1 )';
				break;
			case 'approved':
			case 'published':
			default:
				$where .= ' AND isPublic=1';
				break;
		}

		return apply_filters( 'matador-the-job-where', $where );
	}

	/**
	 * Job Title Field
	 *
	 * Which field will be used for the title.
	 *
	 * @since 3.4.0
	 * @return string title field name
	 */
	private function the_jobs_title_field() {

		/**
		 * Filter Matador Import Job Description
		 *
		 * If there is a filter here, it is overriding one of the two core fields.
		 *
		 * @since 3.4.0
		 * @return string description field name (in external source)
		 */
		return apply_filters( 'matador_import_job_title_field', 'title' );
	}


	/**
	 * Job URL Slug (Post_Name)
	 *
	 * How should the importer determine the job URL slug
	 *
	 * @access private
	 * @since 3.4.0
	 *
	 * @param stdClass $job
	 *
	 * @return string URL formatted for the job slug.
	 */
	private function the_jobs_slug( $job ) {

		$slug = '';

		$setting = Matador::setting( 'post_type_slug_job_listing_each' );

		switch ( $setting ) {
			case 'title_id':
				$slug = $job->{$this->the_jobs_title_field()} . ' ' . $job->id;
				break;
			case 'id_title':
				$slug = $job->id . ' ' . $job->{$this->the_jobs_title_field()};
				break;
			case 'title':
				$slug = $job->{$this->the_jobs_title_field()};
				break;
			default:
				break;
		}

		/**
		 * Filter : Matador Import Job Slug
		 *
		 * Filter the imported job slug. Useful to replace, prepend, or append the slug. Also can be used to add a
		 * custom option to the job slug setting and handle it. Should return a string.
		 *
		 * @since 3.4.0
		 *
		 * @param string $slug
		 * @param stdClass $job
		 * @param string $setting
		 *
		 * @return string
		 */
		$slug = apply_filters( 'matador_import_job_slug', $slug, $job, $setting );

		// We can't return an empty string, so set the job title as the slug if the string is false/empty
		$slug = ! empty( $slug ) ? $slug : $job->{ $this->the_jobs_title_field() };

		// WordPress core function sanitize_title(), which converts a string into URL safe slug.
		return sanitize_title( $slug );
	}

	/**
	 * Job Description Field
	 *
	 * Looks for a setting for Job Description field and verifies its a valid option.
	 *
	 * @since 3.0.0
	 * @return string description field name
	 */
	private function the_jobs_description_field() {

		$setting = Matador::setting( 'bullhorn_description_field' );

		$description = in_array( $setting, array( 'description', 'publicDescription' ), true ) ? $setting : 'description';

		/**
		 * Filter Matador Import Job Description
		 *
		 * If there is a filter here, it is overriding one of the two core fields.
		 *
		 * @since 3.4.0
		 * @return string description field name (in external source)
		 */
		return apply_filters( 'matador_import_job_description_field', $description );
	}

	/**
	 * Job Country Name
	 *
	 * Looks for a setting for Job Description field and verifies its a valid option.
	 *
	 * @since 3.0.0
	 *
	 * @param integer $country_id
	 *
	 * @throws Exception
	 *
	 * @return string country name
	 */
	private function the_job_country_name( $country_id ) {

		$country_list = $this->get_countries();

		if ( array_key_exists( $country_id, $country_list ) ) {
			return $country_list[ $country_id ];
		} else {
			return null;
		}
	}

	/**
	 * Job Description Allowed Fields
	 *
	 * Allowed fields array for the wp_kses() filter on the description imported from Bullhorn.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	private function the_jobs_description_allowed_tags() {
		return apply_filters( 'matador_the_jobs_description_allowed_tags', array(
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
			),
			'br'     => array(),
			'hr'     => array(),
			'em'     => array(),
			'i'      => array(),
			'strong' => array(),
			'b'      => array(),
			'p'      => array(
				'align' => true,
			),
			'img'    => array(
				'alt'      => true,
				'align'    => true,
				'height'   => true,
				'src'      => true,
				'width'    => true,
			),
			'div'    => array(
				'align' => true,
			),
			'table'  => array(
				'border'      => true,
				'cellspacing' => true,
				'cellpadding' => true,
			),
			'thead'  => array(),
			'tbody'  => array(),
			'tr'     => array(),
			'th'     => array(
				'colspan' => true,
				'rowspan' => true,
			),
			'td'     => array(
				'colspan' => true,
				'rowspan' => true,
			),
			'span'   => array(),
			'h1'     => array(
				'align' => true,
			),
			'h2'     => array(
				'align' => true,
			),
			'h3'     => array(
				'align' => true,
			),
			'h4'     => array(
				'align' => true,
			),
			'h5'     => array(
				'align' => true,
			),
			'h6'     => array(
				'align' => true,
			),
			'ul'     => array(),
			'ol'     => array(),
			'li'     => array(),
			'dl'     => array(),
			'dt'     => array(),
			'dd'     => array(),
			'video'  => array(
				'autoplay' => true,
				'controls' => true,
				'height'   => true,
				'loop'     => true,
				'muted'    => true,
				'poster'   => true,
				'preload'  => true,
				'src'      => true,
				'width'    => true,
			),
		) );

	}

	/**
	 * Timestamp to Epoch
	 *
	 * Bullhorn saves the time as Epoch in Milliseconds. This mean we have to do work. Ugh.
	 *
	 * @since 3.0.0
	 *
	 * @param \DateTime|null $timestamp
	 *
	 * @return \DateTime
	 */
	private static function timestamp_to_epoch( $timestamp = null ) {

		if ( null === $timestamp ) {
			return null;
		}

		$microtime = $timestamp / 1000;
		// make sure the have a .00 in the date format
		if ( ! strpos( $microtime, '.' ) ) {
			$microtime = $microtime . '.00';
		}

		return \DateTime::createFromFormat( 'U.u', $microtime );
	}
}
