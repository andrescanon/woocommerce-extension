<?php
/**
 * The catalog syncing functionality of the plugin.
 *
 * Defines the plugin name, version and callbacks for syncing the catalog
 *
 * @since      0.3.0
 * @package    Recommendations
 * @subpackage Recommendations/includes
 * @author     Stiivo Siider <stiivosiider@gmail.com>
 */
class Recommender_Catalog_Syncer
{
    /**
     * An instance of this class
     *
     * @since      0.3.0
     * @access     private
     * @var        Recommender_Catalog_Syncer $instance An instance of this class
     */
    private static $instance = null;
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
            self::$instance = new Recommender_Catalog_Syncer();
        return self::$instance;
    }
    /**
     * Initialize the class and set its properties.
     *
     * @since      0.3.0
     */
    public function __construct()
    {}
    /**
     * Callback for collecting and sending the catalog.
     *
     * @since      0.3.0
     */
    public function recommender_catalog_sync_callback( )
    {
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

            Recommender_API::get_instance()->catalog_sync($data);
        }
        return true;
    }
}