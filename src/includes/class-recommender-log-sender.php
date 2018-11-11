<?php
/**
 * The log sending functionality of the plugin.
 *
 * Defines the plugin name, version and callbacks for sending logs
 *
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 */
class Recommender_Log_Sender
{
    /**
     * An instance of this class
     *
     * @since      0.3.0
     * @access     private
     * @var        Recommender_Log_Sender $instance An instance of this class
     */
    private static $instance = null;

    /**
     * An instance of this class
     * @since      0.3.0
     * @access     protected
     * @var        $version string used for storing plugin version
     */
    protected static $version = null;

    /**
     * Prevents cloning of a class instance
     *
     * @since      0.3.0
     * @access     private
     */
    private function __clone() {}

    /**
     * Returns an instance of this class
     *
     * @since      0.3.0
     */
    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new Recommender_Log_Sender('0.1.0');
        return self::$instance;
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.3.0
     */
    public function __construct($version)
    {
        self::$version = $version;
        self::$instance = $this;
    }

    /**
     * Callback for sending logs.
     *
     * @since      0.3.0
     */
    public function recommender_send_logs( )
    {
        $response = false;
        $batchSize = 250;
        $fileName = Recommender_WC_Log_Handler::get_output_file_path();
        $logs = $this->recommender_retrieve_logs($fileName);
        $errors = 0;
        $sendSliceSize = 0;
        $logsSize = count($logs) + 1;
        if(count($logs) > 0) {
            for ($i = 0; $i < $logsSize; $i += $batchSize) {
                if ($errors > 0) {
                    Recommender_WC_Logger::logError("Failed to send the logs" . array("lastBatch" => $sendSliceSize . "/" . $logsSize));
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
                        "extension_version" => self::$version
                    );
                }
                $logSlice = [
                    'logs' => $sendSlice,
                ];

                $request = Recommender_API::get_instance()->send_post($logSlice, "logs");
                $response = $request;
                if (!$response) {
                    $errors++;
                    Recommender_WC_Logger::logError("No. " . $errors . array($this->$fileName));
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
     * @since 0.3.0
     * @param $fileName string Filename of the log file
     * @return array contents of the log file
     */
    private function recommender_retrieve_logs($fileName)
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
                "extension_version" => self::$version
            ]], $logs);
        } catch (Exception $exception) {
            Recommender_WC_Log_Handler::logCritical("Recommender_Log_Sender->recommender_retrieve_logs() Exception: ", array(get_class($exception), $exception->getMessage(), $exception->getCode()));
        }

        return $logs;
    }
}