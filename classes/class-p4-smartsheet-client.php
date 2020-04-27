<?php
/**
 * Handles calling the SmartSheet API.
 *
 * @package P4MT
 */

/**
 * Handles calling the SmartSheet API.
 */
final class P4_Smartsheet_Client {

	/**
	 * @var string The API key to use for the requests.
	 */
	private $api_key;

	/**
	 * P4_Smartsheet_Client constructor.
	 *
	 * @param string $api_key For authenticating to the SmartSheet API.
	 */
	private function __construct( string $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * Create an instance from an API key.
	 *
	 * @param string $api_key The API key to use for authentication.
	 * @return static The instance.
	 */
	public static function from_api_key( string $api_key ): self {
		return new self( $api_key );
	}

	/**
	 * Fetch a sheet from the API by its id.
	 *
	 * @param string $sheet_id The id of the sheet to fetch.
	 * @return P4_Smartsheet|null The sheet if found, otherwise null.
	 */
	public function get_sheet( string $sheet_id ): ?P4_Smartsheet {
		$url = "https://api.smartsheet.com/2.0/sheets/$sheet_id";

		$response = $this->request( 'GET', $url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		try {
			return P4_Smartsheet::from_api_response( json_decode( $response['body'], true ) );
		} catch ( InvalidArgumentException $exception ) {
			return null;
		}
	}

	/**
	 * Call the SmartSheet API.
	 *
	 * @param string $method The HTTP method.
	 * @param string $url The URL to call.
	 * @return array|WP_Error The response or an error.
	 */
	private function request( string $method, string $url ) {
		return wp_remote_request(
			$url,
			[
				'method'   => $method,
				'blocking' => true,
				'headers'  => [
					'Authorization' => "Bearer {$this->api_key}",
				],
			]
		);
	}
}
