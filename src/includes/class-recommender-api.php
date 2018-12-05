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
class Recommender_API extends WP_Async_Request
{
	/**
	 * Shop ID
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        string $shop_id Shop ID
	 */
	private $shop_id = null;

	/**
	 * API key
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        string $key API key
	 */
	private $key = null;

	/**
	 * API endpoints
	 *
	 * @since      0.2.0
	 * @access     private
	 * @var        ArrayObject $endpoints API endpoints
	 */
	private $endpoints = [
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
	private $fields = [
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
     * API URL
     *
     * @since      0.5.0
     * @access     private
     * @var        string $query_url API URL
     */
    private $api_url = 'http://127.0.0.1:5678/api/v2';

    /**
     * Query URL
     *
     * @since      0.5.0
     * @access     private
     * @var        string $query_url Query URL
     */
    protected $query_url = null;

    /**
     * Query arguments
     *
     * @since      0.5.0
     * @access     private
     * @var        string $query_args Args
     */
    protected $query_args = null;

    /**
     * POST arguments
     *
     * @since      0.5.0
     * @access     private
     * @var        string $query_url POST args
     */
    protected $post_args = null;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      0.5.0
	 * @access     private
	 */
	public function __construct()
	{
	    $this->shop_id = get_option('shop_id');
		$this->key = get_option('api_key');
		parent::__construct();
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
	public function send_post($data, $event_type, $timeout = 5)
	{
        if ($event_type != 'recs')
            error_log($event_type);
        
	    if($event_type != 'creds' && get_option( 'cred_check_failed', $default = true )){
	        return false;
        }

		try
		{
            // Checks whether the event given in function arguments exists
            if (!array_key_exists($event_type, $this->endpoints))
                throw new Exception("Couldn't find an endpoint matching " . $event_type);

            // Concatenates the API URL and endpoint path
            $url = $this->api_url . $this->endpoints[$event_type];

            foreach ($this->fields[$event_type] as $field)
            {
                if (!isset($data[$field]) || !array_key_exists($field, $data))
                {
                    throw new Exception("Data validation failed - " . $field . " is not set");
                }
            }

            // Checks if the POST request response is needed
            $blocking = false;
            if ($event_type == "recs" || $event_type == "creds" || $event_type == "logs" || $event_type == "catalog")
                $blocking = true;

            $data_json = json_encode($data);

            // Sends the data to the API
            Recommender_WC_Log_Handler::logDebug($event_type .": " . $data_json);

            $this->query_url = $url;
            $this->query_args = array();
            $this->post_args = array(
                'timeout'   => $timeout,
                'blocking'  => $blocking,
                'headers'   => array(
                    'Content-Type'   => 'application/json',
                    'Content-Length' => strlen( $data_json ),
                    'Authorization'  => 'Basic ' . base64_encode($this->shop_id . ":" . $this->key)
                ),
                'body'      => $data_json
            );
            $response = $this->dispatch();

            // TODO log something when it is blocking?
            // currently there is no way to know if the request succeeded
            if (!$blocking)
                return true;

            if (!is_wp_error($response)){
                switch ($http_code = $response['response']['code']){
                    case 200:
                        if($event_type == 'recs') return $response['body'];
                        return true;
                    default:
                        throw new Exception("Event type ".$event_type." with data_json: ".$data_json." gave an unexpected code: " . $http_code . " with result: " . $response);
                }
            } else {
                throw new Exception($response);
            }
		}
		catch (Exception $exception)
		{
            Recommender_WC_Log_Handler::logError('POST send failed: ', array(get_class($exception), $exception->getMessage(), $exception->getCode()));
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
            $url = $this->api_url . '/info';
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

    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    protected function handle()
    {
        return;
    }
}
?>