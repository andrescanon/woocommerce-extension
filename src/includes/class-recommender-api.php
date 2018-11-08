<?php
/**
 * Communicates with STACC API
 *
 * This class defines all code necessary to communicate with STACC's API
 *
 * @since      0.2.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 */
class Recommender_API
{

	/**
	 * An instance of this class
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        Recommender_API $instance An instance of this class
	 */
	private static $instance = null;

	/**
	 * Shop ID
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        string $shop_id Shop ID
	 */
	private static $shop_id = null;

	/**
	 * API key
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        string $key API key
	 */
	private static $key = null;

	/**
	 * API URL
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        string $key API URL
	 */
	private static $api_url = 'TODO';

	/**
	 * API endpoints
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        ArrayObject $key API endpoints
	 */
	private static $endpoints = [
		'add' => '/send_add_to_cart',
		'purchase' => '/send_purchase',
		'view' => '/send_view',
		'search' => '/send_search'
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      0.2.0
	 * @access     private
	 */
	private function __construct()
	{
		//TODO validation, error-handling
		self::$shop_id = get_option('shop_id');
		self::$key = get_option('api_key');

        //if problem:
        //Recommender_WC_Log_Handler::logError('Validation Error');
	}

	/**
	 * Prevents cloning of a class instance
	 *
	 * @since      0.2.0
	 * @access     private
	 */
	private function __clone() {}

	/**
	 * Returns an instance of this class
	 *
	 * @since      0.2.0
	 */
	public static function get_instance()
	{
		if (self::$instance == null)
			self::$instance = new Recommender_API();
		return self::$instance;
	}

	/**
	 * Sends events to the API
	 *
	 * @since      0.2.0
	 * @param      ArrayObject $data Data to be sent to the API
	 * @param      string $event_type The type of event data being sent to the API
	 * @param      int $timeout Default value 5000
	 * @return     bool $status true if everything went well; false otherwise
	 */
	public function send_event($data, $event_type, $timeout = 5000)
	{
        //WP internal logging for incoming events. TODO: Once we have tested sending events to the API properly, delete this.
        // Needs in wp-config.php
        // define('WP_DEBUG', true);
        // define('WP_DEBUG_LOG', true);
        // The log will be in wp-content
        error_log($event_type);
        error_log(print_r($data,true));
		try
		{
			// Gets user id and only proceeds if the user is authenticated
			$user_id = get_current_user_id();

			if ($user_id == 0)
				throw new Exception("user isn't logged in");

			// Checks whether the event given in function arguments exists
			if (!array_key_exists($event_type, self::$endpoints))
				throw new Exception("Couldn't find an endpoint matching " . $event_type);

			// Concatenates the API URL and endpoint path
			$url = self::$api_url . self::$endpoints[$event_type];

			//TODO data validation

			// Sends the data to the API
			$data_string = json_encode( $data );

			$ch = curl_init( $url );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen( $data_string )
				)
			);
			curl_setopt( $ch, CURLOPT_USERPWD, self::$shop_id . ":" . self::$key );
			curl_setopt( $ch, CURLOPT_FRESH_CONNECT, 1 );
			curl_setopt( $ch, CURLOPT_TIMEOUT_MS, $timeout );
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			$result = json_decode ( curl_exec( $ch ) );

			if ($result != null)
				throw new Exception($result['error']);

			return true;
		}
		catch (Exception $exception)
		{
            Recommender_WC_Log_Handler::logError('Event send failed: ' . $exception);
			return false;
		}
	}

    /**
     * Function to check connection to the API
     *
     * @since 0.2.0
     * @return bool Can the Recommender connect to the API
     */
	public function has_connection(){
	    try {
            $url = self::$api_url . '/info';

            $ch = curl_init( $url );
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Accept: application/json"
            ));
            curl_setopt( $ch, CURLOPT_TIMEOUT_MS, 1000 );
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_exec( $ch );

            if (!curl_errno($ch)){
                switch ($http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE)){
                    case 200:
                        return true;
                    default:
                        throw new Exception("Unexpected code: " . $http_code);
                }
            } else {
                throw new Exception(curl_error($ch));
            }
        }
        catch (Exception $exception)
        {
            Recommender_WC_Log_Handler::logCritical('Connection to the API has failed: ' . $exception);
            return false;
        }
    }

    /**
     * Sends events to the API
     *
     * @since      0.3.0
     * @param      ArrayObject $data data to be sent to the API
     * @param      int $timeout Default value 5000
     * @return     bool $status true if everything went well; false otherwise
     */
    public function catalog_sync($data, $timeout = 5000)
    {
        error_log("Start catalog sync");
        error_log(print_r($data,true));
        try
        {
            // Gets user id and only proceeds if the user is authenticated
            $user_id = get_current_user_id();
            if ($user_id == 0)
                throw new Exception("user isn't logged in");
            // Concatenates the API URL and endpoint path
            $url = self::$api_url . "/catalog_sync";
            //TODO data validation
            // Sends the data to the API
            $data_string = json_encode( $data );
            error_log(print_r($data_string,true));
            $ch = curl_init( $url );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen( $data_string )
                )
            );
            curl_setopt( $ch, CURLOPT_USERPWD, self::$shop_id . ":" . self::$key );
            curl_setopt( $ch, CURLOPT_FRESH_CONNECT, 1 );
            curl_setopt( $ch, CURLOPT_TIMEOUT_MS, $timeout );
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $data_string );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = json_decode ( curl_exec( $ch ) );
            if ($result != null)
                throw new Exception($result['error']);
            return true;
        }
        catch (Exception $exception)
        {
            //TODO logging
            return false;
        }
    }

}
?>