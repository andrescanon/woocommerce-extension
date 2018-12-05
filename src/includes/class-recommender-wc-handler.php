<?php
/**
 * Modified log handler
 *
 * Log Level types
 * Emergency: system is unusable
 * Alert: action must be taken immediately
 * Critical: critical conditions
 * Error: error conditions
 * Warning: warning conditions
 * Notice: normal but significant condition
 * Informational: informational messages
 * Debug: debug-level messages
 *
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 */

class Recommender_WC_Log_Handler extends WC_Log_Handler_File
{

    /**
     * An instance of this class
     * @since      0.3.0
     * @access     private
     * @var        Recommender_WC_Log_Handler $instance An instance of this class
     */
    private static $instance = null;

    /**
     * @since      0.3.0
     * @access     private
     * @var        $output_file string output file for logging
     */
    private static $output_file = null;

    /**
     * @since      0.3.0
     * @access     private
     * @var        $output_file_path string Path to output file for logging
     */
    private static $output_file_path = null;

    /**
     * An instance of this class
     * @since      0.3.0
     * @access     protected
     * @var        $version string used for storing plugin version
     */
    protected static $version = null;

    /**
     * Minimum log level this handler will process
     * @since      0.4.0
     * @access     protected
     * @var        $threshold Integer minimum log level to handle.
     */
    protected $threshold = null;

    /**
     * @since      0.4.0
     * @access     protected
     * @param      $level string of the level to check
     * @return     true if should be handled, false otherwise
     */
    protected function should_handle($level ) {
        if ( null === $this->threshold ) {
            return true;
        }
        return $this->threshold <= WC_Log_Levels::get_level_severity($level);
    }

    /**
     * Initialize the class and set its properties.
     *
     * @since      0.3.0
     * @access     private
     * @param      $log_size_limit int limit of the log, default 5 * 1024 * 1024
     * @param      $threshold Integer minimum log level to handle.
     */
    public function __construct($log_size_limit = null, $threshold = 400)
    {
        if ( null === $log_size_limit ) {
            $log_size_limit = 5 * 1024 * 1024;
        }

        $this->log_size_limit = $log_size_limit;
        self::set_output_file('StaccDefault');
        self::$version = PLUGIN_NAME_VERSION;
        $this->threshold = $threshold;

        add_action( 'plugins_loaded', array( $this, 'write_cached_logs' ) );
    }

    /**
     * @since      0.3.0
     * @access     public
     * @return     Recommender_WC_Log_Handler instance
     */
    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new Recommender_WC_Log_Handler();
        return self::$instance;
    }

    /**
     * Sets the output file
     *
     * @since      0.3.0
     * @access     public
     * @param      $str string filename of the new output file
     */
    public static function set_output_file($str){
        self::$output_file = $str;
        self::$output_file_path = WP_CONTENT_DIR . '/uploads/wc-logs/' . $str . '.log';
    }

    /**
     * Method used for making a copy of old log file to 'filename' . sent.log
     * Then deletes the old log file
     *
     * @since      0.3.0
     * @access     public
     */
    public static function set_sent_and_empty_output_file(){
        if(copy(self::$output_file_path,
            WP_CONTENT_DIR . '/uploads/wc-logs/' . self::$output_file . '_sent.log')){
            self::logDebug("Making copy of log file to " . self::$output_file . '_sent.log succeeded');

            if(self::get_instance()->remove(self::$output_file)){
                self::logDebug("Old log file successfully deleted");
            }
            else{
                self::logWarning("Old log file was not deleted!");
            }
        }
        else{
            self::logWarning("Making copy of old log file failed!");
        }

    }

    /**
     * @since      0.3.0
     * @access     private
     * @param      $level string Log message level
     * @param      $message string Log message
     * @param      array $context context array
     * @return     bool if logging was successful
     *
     */
    private function addTo( $level, $message, $context) {

        if($this->should_handle($level)){
            $entry = json_encode([
                "channel" => "WOOCOMMERCE_EXTENSION",
                "level" => $level,
                "msg" => $message,
                "timestamp" => time(),
                "context" => $context,
                "extension_version" => self::$version
            ]);

            return $this->add( $entry, self::$output_file);
        }
        return;
    }

    // All 8 types of log level methods:

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logEmergency($message, $context = array()){
        self::get_instance()->addTo('EMERGENCY', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logAlert($message, $context = array()){
        self::get_instance()->addTo('ALERT', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logCritical($message, $context = array()){
        self::get_instance()->addTo('CRITICAL', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logError($message, $context = array()){
        self::get_instance()->addTo('ERROR', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logWarning($message, $context = array()){
        self::get_instance()->addTo('WARNING', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logNotice($message, $context = array()){
        self::get_instance()->addTo('NOTICE', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logInformational($message, $context = array()){
        self::get_instance()->addTo('INFORMATIONAL', $message, $context);
    }

    /**
     * @since      0.3.0
     * @access     public
     * @param      $message string to write in logs
     * @param      array $context
     */
    public static function logDebug($message, $context = array()){
        self::get_instance()->addTo('DEBUG', $message, $context);
    }

    /**
     * @since       0.3.0
     * @access      public
     * @return      string Path for the output file
     */
    public static function get_output_file_path(){
        return self::$output_file_path;
    }

    /**
     * Get a log file name.
     *
     * @since      0.3.0
     * @access     public
     * @param      string $handle Log name.
     * @return     bool|string The log file name or false if cannot be determined.
     */
    public static function get_log_file_name( $handle ) {
        if ( function_exists( 'wp_hash' ) ) {
            return sanitize_file_name( $handle . '.log' );
        } else {
            wc_doing_it_wrong( __METHOD__, __( 'This method should not be called before plugins_loaded.', 'woocommerce' ), '3.3' );
            return false;
        }
    }

    //-----From here everything copied from WC_Log_Handler_File because those methods have to use my overriden get_log_file_name method

    /**
     * Open log file for writing.
     *
     * @param string $handle Log handle.
     * @param string $mode Optional. File mode. Default 'a'.
     * @return bool Success.
     */

    protected function open( $handle, $mode = 'a' ) {
        if ( $this->is_open( $handle ) ) {
            return true;
        }

        $file = self::get_log_file_path( $handle );

        if ( $file ) {
            if ( ! file_exists( $file ) ) {
                $temphandle = @fopen( $file, 'w+' );
                @fclose( $temphandle );

                if ( defined( 'FS_CHMOD_FILE' ) ) {
                    @chmod( $file, FS_CHMOD_FILE );
                }
            }

            if ( $resource = @fopen( $file, $mode ) ) {
                $this->handles[ $handle ] = $resource;
                return true;
            }
        }

        return false;
    }


    /**
     * Remove/delete the chosen file.
     *
     * @param string $handle
     *
     * @return bool
     */
    public function remove( $handle ) {
        $removed = false;
        $file    = self::get_log_file_path( $handle );

        if ( $file ) {
            if ( is_file( $file ) && is_writable( $file ) ) {
                $this->close( $handle ); // Close first to be certain no processes keep it alive after it is unlinked.
                $removed = unlink( $file );
            }
            do_action( 'woocommerce_log_remove', $handle, $removed );
        }

        return $removed;
    }

    /**
     * Check if log file should be rotated.
     *
     * Compares the size of the log file to determine whether it is over the size limit.
     *
     * @param string $handle Log handle
     * @return bool True if if should be rotated.
     */
    protected function should_rotate( $handle ) {
        $file = self::get_log_file_path( $handle );
        if ( $file ) {
            if ( $this->is_open( $handle ) ) {
                $file_stat = fstat( $this->handles[ $handle ] );
                return $file_stat['size'] > $this->log_size_limit;
            } elseif ( file_exists( $file ) ) {
                return filesize( $file ) > $this->log_size_limit;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * Increment a log file suffix.
     *
     * @param string $handle Log handle
     * @param null|int $number Optional. Default null. Log suffix number to be incremented.
     * @return bool True if increment was successful, otherwise false.
     */
    protected function increment_log_infix( $handle, $number = null ) {
        if ( null === $number ) {
            $suffix = '';
            $next_suffix = '.0';
        } else {
            $suffix = '.' . $number;
            $next_suffix = '.' . ($number + 1);
        }

        $rename_from = self::get_log_file_path( "{$handle}{$suffix}" );
        $rename_to = self::get_log_file_path( "{$handle}{$next_suffix}" );

        if ( $this->is_open( $rename_from ) ) {
            $this->close( $rename_from );
        }

        if ( is_writable( $rename_from ) ) {
            return rename( $rename_from, $rename_to );
        } else {
            return false;
        }

    }


    /**
     * Get a log file path.
     *
     * @param string $handle Log name.
     * @return bool|string The log file path or false if path cannot be determined.
     */
    public static function get_log_file_path( $handle ) {
        if ( function_exists( 'wp_hash' ) ) {
            return trailingslashit( WC_LOG_DIR ) . self::get_log_file_name( $handle );
        } else {
            wc_doing_it_wrong( __METHOD__, __( 'This method should not be called before plugins_loaded.', 'woocommerce' ), '3.0' );
            return false;
        }
    }

}
