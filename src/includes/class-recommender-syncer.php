<?php
/**
 * The syncing functionality of the plugin.
 *
 * Defines the plugin name, version and callbacks for syncing the catalog and logs
 *
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 * @author     Lauri Leiten <leitenlauri@gmail.com>
 */
class Recommender_Syncer
{
    /**
     * An instance of this class
     *
     * @since      0.4.0
     * @access     private
     * @var        Recommender_Syncer $instance An instance of this class
     */
    private static $instance = null;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.6.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	private $version = null;

    /**
     * Prevents cloning of a class instance
     *
     * @since      0.4.0
     * @access     private
     */
    private function __clone() {}

    /**
     * Returns an instance of this class
     *
     * @since      0.4.0
     */
    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new Recommender_Syncer();
        return self::$instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.6.0
     */
    private function __construct()
    {
	    if (!defined('PLUGIN_NAME_VERSION'))
		    define('PLUGIN_NAME_VERSION', '1.0.0');

	    $this->version = PLUGIN_NAME_VERSION;
    }

    /**
     * Callback for collecting and sending the catalog.
     *
     * @since      0.6.0
     */
    public function sync_products( )
    {
        $api = new Recommender_API();
        $total = 0;
        $size = 50;
        $currency = get_woocommerce_currency();
        while(true) {
            $products = wc_get_products(array(
                'status' => 'publish',
                'stock_status' => 'instock',
                'limit' => $size,
                'offset' => $total,
            ));
            $total+=$size;
            if(empty($products)){
                break;
            }

            $bulk = [];
            foreach ($products as $product){
                $iteminfo = [
                    'item_id' => (string)$product->get_id(),
                    'name' => $product->get_name(),
                    'price' => (float)$product->get_price(),
                    'currency' => $currency,
                ];
                array_push($bulk,$iteminfo);
            }
            $data = [
                'bulk' => $bulk,
                'properties' => []
            ];

            $api->send_post($data, 'catalog');
        }
        return true;
    }

    /**
     * Callback for syncing logs.
     *
     * @since      0.4.0
     */
    public function sync_logs( )
    {
        $api = new Recommender_API();
        $response = false;
        $batchSize = 250;
        $fileName = Recommender_WC_Log_Handler::get_output_file_path();
        $logs = $this->retrieve_logs($fileName);
        $errors = 0;
        $sendSliceSize = 0;
        $logsSize = count($logs) + 1;
        if(count($logs) > 0) {
            for ($i = 0; $i < $logsSize; $i += $batchSize) {
                if ($errors > 0) {
                    Recommender_WC_Log_Handler::logError("Failed to send the logs" . array("lastBatch" => $sendSliceSize . "/" . $logsSize));
                    break;
                }
                $sendSlice = array_slice($logs, $i, $batchSize);
                $sendSliceSize = count($sendSlice);
                if ($sendSliceSize < $batchSize) {
                    $sendSlice[] = array(
                        "channel" => "WOOCOMMERCE_EXTENSION",
                        "level" => "INFO",
                        "msg" => "Finished sending logs " . ($sendSliceSize + 1) . "/" . ($logsSize),
                        "timestamp" => time(),
                        "context" => ["size" => $sendSliceSize + 1],
                        "extension_version" => $this->version
                    );
                }
                $logSlice = [
                    'logs' => $sendSlice,
                ];

                $request = $api->send_post($logSlice, "logs");
                $response = $request;
                if (!$response) {
                    $errors++;
                    Recommender_WC_Log_Handler::logError('No. ' . $errors . $this->$fileName);
                }
            }

            if ($response) {
                // Remove the log file to prevent duplicating logs
                Recommender_WC_Log_Handler::set_sent_and_empty_output_file();
                return true;
            }
        }
        return false;
    }

    /**
     * Method to collect logs
     *
     * @since 0.4.0
     * @param $fileName string Filename of the log file
     * @return array contents of the log file
     */
    private function retrieve_logs($fileName)
    {
        $logs = array();

        try {
            $rawLogs = explode(PHP_EOL, file_get_contents($fileName));
            array_pop($rawLogs);
            foreach($rawLogs as $line){
                array_push($logs, json_decode(trim($line)));
            }
            $logs = array_merge([[
                "channel" => "WOOCOMMERCE_EXTENSION",
                "level" => "INFO",
                "msg" => "Sending " . (count($logs) + 2) . " logs",
                "timestamp" => time(),
                "context" => ["size" => (count($logs) + 2)],
                "extension_version" => $this->version
            ]], $logs);
        } catch (Exception $exception) {
            Recommender_WC_Log_Handler::logError("Recommender_Log_Sender->recommender_retrieve_logs() Exception: ", array(get_class($exception), $exception->getMessage(), $exception->getCode()));
        }

        return $logs;
    }
}