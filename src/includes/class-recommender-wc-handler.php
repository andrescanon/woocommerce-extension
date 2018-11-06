<?php
/**
 * Modified log handler
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Hannes Saariste <hannes.saariste@gmail.com>
 */

class Recommender_WC_Log_Handler extends WC_Log_Handler_File
{

    /**
     * An instance of this class
     *
     * @since      0.3.0
     * @access     private
     * @var        Recommender_WC_Log_Handler $instance An instance of this class
     */
    private static $instance = null;

    private static $output_File = null;


    public static function set_Output_File($str){
        self::$output_File = $str;
    }

    public static function set_Sent_And_Empty_Output_File(){
        copy(plugin_dir_path(dirname(WP_CONTENT_DIR)). 'wordpress/wp-content/uploads/wc-logs/' . self::$output_File . '.log',
            plugin_dir_path(dirname(WP_CONTENT_DIR)). 'wordpress/wp-content/uploads/wc-logs/' . self::$output_File . 'sent.log');

        //TODO unlink isnt working right now, checked file is writeable and exists, ???
        unlink(plugin_dir_path(dirname(WP_CONTENT_DIR)). 'wordpress/wp-content/uploads/wc-logs/' . self::$output_File . '.log');
    }

    public function __construct( $log_size_limit = null ) {

        if ( null === $log_size_limit ) {
            $log_size_limit = 5 * 1024 * 1024;
        }

        $this->log_size_limit = $log_size_limit;
        self::$output_File = 'StaccDefault';

        add_action( 'plugins_loaded', array( $this, 'write_cached_logs' ) );
    }

    /**
     * @since      0.3.0
     * @return     Recommender_WC_Log_Handler
     */

    public static function get_instance()
    {
        if (self::$instance == null)
            self::$instance = new Recommender_WC_Log_Handler();
        return self::$instance;
    }


    private function addTo( $level, $message) {

        $entry = json_encode([
            //"channel" => $channel,
            "level" => $level,
            "msg" => $message,
            "timestamp" => date_i18n( 'm-d-Y @ H:i:s' ),
            //TODO getting version, not working like that:
            //"extension_version" => Recommender::get_version()
        ]);

        return $this->add( $entry, self::$output_File);
    }

    // All 8 types of log level methods:

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logEmergency($message){
        self::get_instance()->addTo('Emergency', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logAlert($message){
        self::get_instance()->addTo('Alert', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logCritical($message){
        self::get_instance()->addTo('Critical', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logError($message){
        self::get_instance()->addTo('Error', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logWarning($message){
        self::get_instance()->addTo('Warning', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logNotice($message){
        self::get_instance()->addTo('Notice', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logInformational($message){
        self::get_instance()->addTo('Informational', $message);
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logDebug($message){
        self::get_instance()->addTo('Debug', $message);
    }


    /**
     * Get a log file name.
     *
     * @since 3.3
     * @param string $handle Log name.
     * @return bool|string The log file name or false if cannot be determined.
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