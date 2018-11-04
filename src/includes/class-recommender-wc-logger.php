<?php
/**
 * Used for logging
 *
 * This class is used for wc-logger
 * Example for usage:
 * Recommender_WC_Logger::get_Logger()->warning( 'Exception', Recommender_WC_Logger::get_LogFile());
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
 */
class Recommender_WC_Logger
{

	/**
	 * An instance of this class
	 *
	 * @since      0.3.0
	 * @access     private
	 * @var        Recommender_WC_Logger $instance An instance of this class
	 */
	private static $instance = null;

    /**
     * An instance of woocommerce logger
     *
     * @since      0.3.0
     * @access     private
     * @var        WC_Logger $myLogger an instance of woocommerce logger
     */
	private static $Logger = null;

	/**
	 * Log file
	 *
	 * @since      0.3.0
	 * @access     private
	 * @var        string $Log_File Log file
	 */
	private static $Log_File = null;

    /**
     * Prevents cloning of a class instance
     *
     * @since      0.3.0
     * @access     private
     */
    private function __clone() {}

	
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      0.3.0
	 * @access     private
	 */
	private function __construct()
	{
		self::$Log_File = 'defaultLog';
		self::$Logger = wc_get_logger();
	}


    /**
     * @since      0.3.0
     * @return     WC_Logger
     */
    private function getLogger(){
	    return self::$Logger;
    }


    /**
     * @since      0.3.0
     * @return     WC_Logger modified instance
     */
    public static function get_Logger(){
	    return self::get_instance()->getLogger();

    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logEmergency($message){
        self::get_Logger()->emergency($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logAlert($message){
        self::get_Logger()->alert($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logCritical($message){
        self::get_Logger()->critical($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logError($message){
        self::get_Logger()->error($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logWarning($message){
        self::get_Logger()->warning($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logNotice($message){
        self::get_Logger()->notice($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logInformational($message){
        self::get_Logger()->info($message, self::get_LogFile());
    }

    /**
     * @since      0.3.0
     * @param      $message string to write in logs
     */
    public static function logDebug($message){
        self::get_Logger()->debug($message, self::get_LogFile());
    }


    /**
     * @since      0.3.0
     * @return     string output file name of logger
     */
    public static function get_Output_File()
    {
        return self::$Log_File;
    }


    /**
     * @since      0.3.0
     * @return     array output file as source file
     */
    public static function get_LogFile()
    {
        return array( 'source' => self::$Log_File );
    }


    /**
     * @since      0.3.0
     * @return     Recommender_WC_Logger
     */
    public static function get_instance()
	{
		if (self::$instance == null)
			self::$instance = new Recommender_WC_Logger();
		return self::$instance;
	}
}
?>