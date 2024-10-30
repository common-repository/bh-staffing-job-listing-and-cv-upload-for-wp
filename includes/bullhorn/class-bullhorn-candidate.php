<?php
/**
 * Matador / Bullhorn API / Candidate Submission
 *
 * Extends Bullhorn_Connection and submits candidates for jobs.
 *
 * @link        http://matadorjobs.com/
 * @since       3.0.0
 *
 * @package     Matador Jobs Board
 * @author      Jeremy Scott, Paul Bearne
 * @copyright   Copyright (c) 2017, Jeremy Scott, Paul Bearne
 *
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace matador;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class is an extension of Bullhorn_Connection.  Its purpose
 * is to allow for resume and candidate posting
 *
 * Class Bullhorn_Candidate_Processor
 */
class Bullhorn_Candidate extends Bullhorn_Connection {

	/**
	 * Class Constructor
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		parent::__construct();
		try {
			$this->login();
		} catch ( Exception $e ) {

			new Event_Log( $e->getName(), $e->getMessage() );
			Admin_Notices::add( esc_html__( 'Login into Bullhorn failed see log for more info.', 'matador-jobs' ), 'warning', 'bullhorn-login-exception' );
		}
	}

	/**
	 * Find Candidate
	 *
	 * Looks up submitted email address and last name for matching entries
	 * in the candidates database.
	 *
	 * @param string $email
	 * @param string $last_name
	 * @throws Exception
	 * @return integer|boolean
	 *
	 * @since 3.0.0
	 */
	public function find_candidate( $email, $last_name ) {
		if ( ! $email ) {

			return false;
		}

		// API Method
		$method = '/search/Candidate';

		// API Params
		$params = array(
			'count'  => '1',
			'query'  => 'email: "' . Helper::escape_lucene_string( $email )
							. '" AND lastName: "' . Helper::escape_lucene_string( $last_name )
							. '" AND isDeleted:0',
			'fields' => 'id,lastName,email,isDeleted',
		);

		$request = $this->request( $method, $params, 'GET' );

		if (
			! is_wp_error( $request )
			&& is_object( $request )
		    && ! isset( $request->errorMessage ) // @codingStandardsIgnoreLine (SnakeCase)
			&& 0 < $request->count
		) {
			return (int) $request->data[0]->id;
		} else {
			return false;
		}
	}

	/**
	 * Search by Email Address
	 *
	 * Looks up submitted email address for matching entries
	 * in the candidates database.
	 *
	 * @param integer $bhid
	 * @throws Exception
	 * @return \stdClass|boolean
	 *
	 * @since 3.0.0
	 */
	public function get_candidate( $bhid = null ) {

		if ( ! is_integer( $bhid ) ) {
			return false;
		}

		// API Method
		$method = 'entity/Candidate/' . $bhid;

		// API Params
		$params = array(
			'fields' => 'id,name,nickName,firstName,middleName,lastName,address,secondaryAddress,email,email2,email3,mobile,phone,phone2,phone3,description,status,dateLastModified',
		);

		// API Request
		$response = $this->request( $method, $params, 'GET' );

		if ( is_object( $response ) && isset( $response->data ) && isset( $response->data->id ) && $response->data->id === $bhid ) {

			$return = new \stdClass();

			$return->candidate = $response->data;
		} else {
			$return = false;
		}

		return $return;

	}

	/**
	 * Save Candidate
	 *
	 * @param \stdClass $candidate
	 * @throws Exception
	 * @return \stdClass|boolean
	 * @since 3.0
	 */
	public function save_candidate( $candidate = null ) {

		if ( ! $candidate->candidate || ! is_object( $candidate->candidate ) ) {
			Logger::add( 'error', 'matador-error-bad-candidate data', esc_html__( 'We passed bad data to the save candidate function the data was: ', 'matador-jobs' ) . ' ' . print_r( $candidate, true ) );

			return false;
		}

		// API Method
		if ( isset( $candidate->candidate->id ) ) {
			$method = 'entity/Candidate/' . $candidate->candidate->id;
			// API Request
			$response = $this->request( $method, array(), 'POST', $candidate->candidate );
		} else {
			$method = 'entity/Candidate';
			// API Request
			$response = $this->request( $method, array(), 'PUT', $candidate->candidate );
		}

		if ( is_object( $response ) && isset( $response->changedEntityId ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			$candidate->candidate->id = $response->changedEntityId; // @codingStandardsIgnoreLine (SnakeCase)

			return $candidate;
		} else {

			if ( isset( $candidate->candidate->id ) ) {
				Logger::add( 'error', 'matador-error-updating-candidate', esc_html__( 'We got an error when updating a remote candidate the error was: ', 'matador-jobs' ) . ' ' . print_r( $response, true ) );
			} else {
				Logger::add( 'error', 'matador-error-creating-candidate', esc_html__( 'We got an error when creating a remote candidate the error was: ', 'matador-jobs' ) . ' ' . print_r( $response, true ) );
			}

			return false;
		}

	}

	/**
	 * Save Candidate Education
	 *
	 * @param \stdClass|array $candidate
	 * @throws Exception
	 * @return boolean
	 * @since 3.0.0
	 */
	public function save_candidate_education( $candidate = null ) {

		if ( ! $candidate ) {
			return false;
		}

		// API Method
		$method = 'entity/CandidateEducation';

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		if ( isset( $candidate->candidateEducation ) && is_array( $candidate->candidateEducation ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			$return = array();

			foreach ( $candidate->candidateEducation as $education ) { // @codingStandardsIgnoreLine (SnakeCase)

				$education->candidate     = new \stdClass();
				$education->candidate->id = $candidate->candidate->id;

				// API Call
				$return[] = $this->request( $method, $params, $http, $education );

			}
		}

		return isset( $return ) ? true : false;
	}

	/**
	 * Save Candidate Work History
	 *
	 * @param \stdClass|array $candidate
	 * @throws Exception
	 * @return boolean
	 * @since 3.0.0
	 */
	public function save_candidate_work_history( $candidate = null ) {

		if ( empty( $candidate ) ) {
			return false;
		}

		// API Method
		$method = 'entity/CandidateWorkHistory';

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		if ( isset( $candidate->candidateWorkHistory ) && is_array( $candidate->candidateWorkHistory ) ) { // @codingStandardsIgnoreLine (SnakeCase)

			// Return Array
			$return = array();

			foreach ( $candidate->candidateWorkHistory as $job ) { // @codingStandardsIgnoreLine (SnakeCase)

				$job->candidate     = new \stdClass();
				$job->candidate->id = $candidate->candidate->id;

				// API Call
				$return[] = $this->request( $method, $params, $http, $job );

			}
		}

		return ! empty( $return ) ? true : false;
	}

	/**
	 * Save Candidate Skills
	 *
	 * @param \stdClass $candidate
	 * @throws Exception
	 * @return boolean
	 * @since 3.0.0
	 */
	public function save_candidate_skills( $candidate = null ) {

		if ( ! $candidate || empty( $candidate->skillList ) ) { // @codingStandardsIgnoreLine (SnakeCase)
			return false;
		}

		$bullhorn_skills      = $this->get_skills_list();
		$candidate_skills     = $candidate->skillList; // @codingStandardsIgnoreLine (SnakeCase)
		$candidate_skills_ids = array();

		if ( ! empty( $bullhorn_skills ) ) {
			foreach ( $candidate_skills as $skill ) {
				$key = array_search( strtolower( $skill ), $bullhorn_skills, true );
				if ( $key ) {
					$candidate_skills_ids[] = $key;
				}
			}
			$candidate_skills_ids = array_unique( $candidate_skills_ids );
		}

		// API Method
		$method = 'entity/Candidate/' . $candidate->candidate->id . '/primarySkills/' . implode( ',', $candidate_skills_ids );

		// API Params
		$params = array();

		// HTTP Action
		$http = 'PUT';

		// Return Array
		$return = $this->request( $method, $params, $http );

		// Send a Boolean response
		return ! empty( $return ) ? true : false;
	}

	/**
	 * Attach Note to a candidate
	 *
	 * @param \stdClass $candidate
	 * @param string $note
	 * @throws Exception
	 * @return bool
	 *
	 * @since 3.0.0
	 */
	public function save_candidate_note( $candidate = null, $note = null ) {

		if ( ! $candidate && ! $note ) {
			return false;
		}

		$body = array(
			'personReference' => array( 'id' => $candidate->candidate->id ),
			'comments'        => $note,
		);

		// API Method
		$method = 'entity/Note';

		// API Params
		$params = array();

		// Request
		$response = $this->request( $method, $params, 'PUT', $body );

		return $response ? true : false;
	}

	/**
	 * Attach Resume to a candidate.
	 *
	 * @param \stdClass $candidate
	 * @param string $file path/to/file
	 * @throws Exception
	 *
	 * @return mixed
	 */
	public function save_candidate_file( $candidate = null, $file = null ) {

		if ( ! $candidate || ! $file ) {
			return false;
		}

		// API Method
		$method = '/file/Candidate/' . $candidate->candidate->id . '/raw';

		// API Params
		$params = array(
			'externalID' => 'Portfolio', // PER BULLHORN
			'fileType'   => 'SAMPLE', // PER BULLHORN
		);

		// API Request
		$request = $this->request_with_payload( $method, $params, 'PUT', $file );

		if ( ! $request ) {

			return false;
		}

		return $request ? true : false;
	}

	/**
	 * Attach Found Candidate to Job
	 *
	 * Looks up submitted email address for matching entries
	 * in the candidates database.
	 *
	 * @return array|bool
	 * @param \stdClass $candidate
	 * @param integer $job_id
	 * @throws Exception
	 *
	 * @since 3.0.0
	 */
	public function submit_candidate_to_job( $candidate = null, $job_id = null ) {

		if ( ! is_object( $candidate ) && ! is_int( $job_id ) ) {
			return false;
		}

		$mark_application_as = Matador::setting( 'bullhorn_mark_application_as' );
		if ( ! empty( $mark_application_as ) ) {
			switch ( $mark_application_as ) {
				case 'submitted':
					$mark = 'Submitted';
					break;
				case 'lead':
				default:
					$mark = 'New Lead';
					break;
			}
		} else {
			$mark = 'New Lead';
		}

		// API Method
		$method = 'entity/JobSubmission';

		// Request Body
		$body = array(
			'candidate'       => array( 'id' => $candidate->candidate->id ),
			'jobOrder'        => array( 'id' => $job_id ),
			/**
			 * Matador Data Source Description
			 *
			 * Adjusts the text description for the source of the job submission. Default is "{Site Name} Website", ie:
			 * "ACME Staffing Website". Use the $entity argument to narrow the modification to certain entities.
			 *
			 * @since 3.1.1
			 * @since 3.4.0 added $data parameter
			 *
			 * @var string
			 * @var string   $context Limit scope of filter in filtering function
			 * @var \stdClass $data.   The associated data with the $context. Should not be used without $context first.
			 */
			'source'          => apply_filters( 'matador_data_source_description', ( get_bloginfo( 'name' ) . ' Website' ), 'submission', $candidate->candidate ),
			'status'          => $mark,
			'dateWebResponse' => (int) ( microtime( true ) * 1000 ),
		);

		$response = $this->request( $method, array(), 'PUT', $body );

		return $response ? true : false;

	}

	/**
	 * Parse Resume
	 *
	 * Takes an application data array, checks for the file info.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 added $context parameter
	 *
	 * @access public
	 *
	 * @param string $file    Path to file for resume.
	 * @param string $content Text-based resume content.
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function parse_resume( $file = null, $content = null ) {

		if ( ! $file && ! $content ) {
			throw new Exception( 'warning', 'bullhorn-parse-resume-no-file', esc_html__( 'Parse resume cannot be called without a file.', 'matador-jobs' ) );
		}

		// API Method
		if ( ! $file && $content ) {
			$method = '/resume/parseToCandidateViaJson';
		} else {
			$method = 'resume/parseToCandidate';
		}

		// API Params
		$params = array(
			'populateDescription' => apply_filters( 'matador_bullhorn_candidate_parse_resume_description_format', 'html' ),
		);

		// while ( true ) is ambiguous, but the loop is broken upon a return, which occurs by the fifth cycle.
		while ( true ) {

			$count = isset( $count ) ? ++ $count : 1;

			if ( ! $file && $content ) {
				$body = array(
					'resume' => $content,
				);

				$request_args = array(
					'headers' => array( 'Content-Type' => 'application/json' ),
				);

				if ( strip_tags( $content ) !== $content ) {
					$params['format'] = 'html';
				} else {
					$params['format'] = 'text';
				}

				$return = $this->request( $method, $params, 'POST', $body, $request_args );

			} else {
				$return = $this->request_with_payload( $method, $params, 'POST', $file );
			}

			if ( is_array( $return ) && array_key_exists( 'error', $return ) ) {
				return $return;
			}

			if ( ! is_wp_error( $return ) && ! isset( $return->errorMessage ) ) { // @codingStandardsIgnoreLine (SnakeCase)
				return $return;
			}

			if ( $count >= 5 ) {
				return array( 'error' => 'attempted-five-and-failed' );
			}
		}

		return false;
	}

	/**
	 * Get Skills List
	 *
	 * Takes an application data array, checks for the file info.
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 3.0.0
	 */
	public function get_skills_list() {

		$transient = Matador::variable( 'bullhorn_skills_cache', 'transients' );
		$skills    = get_transient( $transient );

		if ( ! $skills ) {

			// API Method
			$method = 'options/Skill';

			// HTTP Action
			$http = 'GET';

			// Return Array
			$request = $this->request( $method, array(), $http );

			if ( isset( $request->data ) ) {

				$skills = array();

				foreach ( $request->data as $skill ) {
					$skills[ $skill->value ] = strtolower( trim( $skill->label ) );
				}

				$skill_list = array_unique( $skills );

				set_transient( $transient, $skill_list, HOUR_IN_SECONDS * 6 );

			}
		}

		return $skills;

	}

	/**
	 * @param \stdClass $candidate
	 *
	 * @return mixed
	 */
	public function delete_candidate( $candidate ) {

		return $candidate;
	}

}
