<?php
/**
 * Minimal stand-ins for the WordPress REST and error classes that
 * get_preview() touches. The suite runs without WordPress loaded.
 *
 * @package BetterBookmarks
 */

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound, Squiz.Commenting

if ( ! class_exists( 'WP_Error' ) ) {
	class WP_Error {
		/** @var string */
		public $code;

		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code = $code;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return '';
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	class WP_REST_Request {
		/** @var array */
		private $params;

		public function __construct( array $params = array() ) {
			$this->params = $params;
		}

		public function get_param( $key ) {
			return $this->params[ $key ] ?? null;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	class WP_REST_Response {
		/** @var mixed */
		private $data;

		public function __construct( $data = null, $status = 200 ) {
			$this->data = $data;
		}

		public function get_data() {
			return $this->data;
		}
	}
}
