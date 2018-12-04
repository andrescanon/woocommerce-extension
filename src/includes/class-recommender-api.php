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
	private static $api_url = 'http://127.0.0.1:5678/api/v2';

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
		'search' => '/send_search',
        'recs' => '/get_recs',
        'logs' => '/send_logs',
        'catalog' => '/catalog_sync',
        'creds' => '/check_credentials'
	];

	/**
	 * Needed fields for endpoints
	 *
	 * @since      0.3.0
	 * @access     private
	 * @var        ArrayObject $fields JSON field information
	 */
	private static $fields = [
		'add' => ["item_id", "stacc_id", "website", "properties"],
		'purchase' => ["stacc_id", "item_list", "website", "currency", "properties"],
		'view' => ["item_id", "stacc_id", "website", "properties"],
		'search' => ["stacc_id", "query", "filters", "website", "properties"],
		'recs' => ["item_id", "stacc_id", "block_id", "website", "properties"],
        'logs' => ["logs"],
        'catalog' => ["bulk", "properties"],
        'creds' => ["log_sync_url", "product_sync_url"]
	];

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      0.2.0
	 * @access     private
	 */
	private function __construct()
	{
		self::$shop_id = get_option('shop_id');
		self::$key = get_option('api_key');
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
	 * Sends POST request to the API
	 *
	 * @since      0.2.0
	 * @param      ArrayObject $data Data to be sent to the API
	 * @param      string $event_type The type of event data being sent to the API
	 * @param      int $timeout Default value 5000
	 * @return     bool $status true if everything went well; false otherwise
	 */
	public function send_post($data, $event_type, $timeout = 5000)
	{
        if ($event_type != 'recs')
            error_log($event_type);
		try
		{
			// Checks whether the event given in function arguments exists
			if (!array_key_exists($event_type, self::$endpoints))
				throw new Exception("Couldn't find an endpoint matching " . $event_type);

			// Concatenates the API URL and endpoint path
			$url = self::$api_url . self::$endpoints[$event_type];

			foreach (self::$fields[$event_type] as $field)
			{
				if (!isset($data[$field]) || !array_key_exists($field, $data))
				{
					throw new Exception("Data validation failed - " . $field . " is not set");
				}
			}

			// Sends the data to the API
			$data_string = json_encode( $data );
            Recommender_WC_Log_Handler::logDebug($event_type .": " . $data_string);

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
			$response = curl_exec( $ch );
			$result = json_decode ( $response );

            if (!curl_errno($ch)){
                switch ($http_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE)){
                    case 200:
                        if($event_type == 'recs') return $result;
                        return true;
                    default:
                        throw new Exception("Unexpected code: " . $http_code . "; With result: " . $response);
                }
            } else {
                throw new Exception(curl_error($ch));
            }
		}
		catch (Exception $exception)
		{
            Recommender_WC_Log_Handler::logError('POST send failed: ', array(get_class($exception), $exception->getMessage(), $exception->getCode()));
            if($event_type == 'recs') return json_decode(json_encode(array()), FALSE);
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
                        Recommender_WC_Log_Handler::logDebug("Connection to the API possible.");
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
            Recommender_WC_Log_Handler::logError('Connection to the API has failed: ', array(get_class($exception), $exception->getMessage(), $exception->getCode()));
            return false;
        }
    }

}
?>