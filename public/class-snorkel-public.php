<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Snorkel
 * @subpackage Snorkel/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Snorkel
 * @subpackage Snorkel/public
 * @author     Benjamin Schultz <bens@olark.com>
 */
require_once plugin_dir_path( __FILE__ ) . '/../vendor/autoload.php';
use GuzzleHttp\Client;

class Snorkel_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version, $url ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->url = $url;
        $this->client = new Client([
            'base_uri' => $this->url,
            'timeout' => 2.0,
            'http_errors' => FALSE,
        ]);
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Snorkel_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Snorkel_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/snorkel-public.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function queues up the snorkel js to load async after the page has finished loading
         */
        wp_enqueue_script( $this->plugin_name . '-widget', plugin_dir_url( __FILE__ ) . 'js/snorkel-public.js' );

        $snorkel_config = get_option($this->plugin_name);
        wp_localize_script( $this->plugin_name . '-widget', $this->plugin_name . '_widget_config', $snorkel_config );


    }

    private function get_sanitized_snorkel_api_key(){
        $api_key = sanitize_key(get_option($this->plugin_name)['snorkel_api_key']);
        if (strlen($api_key) == 32) {
            return $api_key;
        }
        else{
            throw new Exception("Invalid API Key");
        }
    }

    private function get_default_event_headers() {
        return ['X-Snorkel-API-Key' => $this->get_sanitized_snorkel_api_key()];
    }

    public function post_cart_data($cart_item_key) {
        $cart = WC()->cart;
        $cart_items = array();
        foreach($cart->get_cart_contents() as $item=>$value){
            $cart_items[] = array([
                'key' => $value['key'],
                'product_id' => $value['product_id'],
                'quantity'=> $value['quantity'],
                'line_subtotal' => $value['line_subtotal'],
            ]);
        }

        try {

            $resp = $this->client->request('POST', '/woo-events', [
                'json' => [
                    'type'=> 'cart',
                    'cart' => [
                        'id' => $this->get_or_create_cart_id(),
                        'items' => $cart_items,
                        'totals' => $cart->get_totals(),
                    ],
                    'visitor_id' => $this->get_snorkel_visitor_id(),
                ],
                'headers' => $this->get_default_event_headers(),
            ]);
        } catch (Exception $e) {
            error_log('Error posting cart. ' . $e->getMessage(), 0);
        }
    }

    public function post_order_data($order_id) {
        $order = wc_get_order($order_id);
        $order_items = $order->get_items();
        $order_items_json = array();
        foreach($order_items as $item){
            $order_items_json[] = array(
                'type' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'name' => $item->get_name(),
                'product_id' => $item->get_product_id(),
            );
        }
        $order_json = array_merge(
            $order->get_data(),
            [
                'items' => $order_items_json,
                'created_at' => $order->get_date_created()->getTimestamp(),
                'updated_at' => $order->get_date_modified()->getTimestamp(),
            ]
        );

        try {
            $resp = $this->client->request('POST', '/woo-events', [
                'json' => [
                    'type'=> 'order',
                    'order' => $order_json,
                    'visitor_id' => $this->get_snorkel_visitor_id()
                ],
                'headers' => $this->get_default_event_headers(),
            ]);
        } catch (Exception $e) {
            error_log('Error posting order. ' . $e->getMessage(), 0);
        }
    }

    public function post_customer_data($customer_id) {
        $customer = new WC_Customer($customer_id);
        $customer_json = array(
            'id' => $customer_id,
            'email' => $customer->get_first_name(),
            'first_name' => $customer->get_first_name(),
            'last_name' => $customer->get_last_name(),
            'created_at' => $customer->get_date_created()->getTimestamp(),
            'updated_at' => $customer->get_date_modified()->getTimestamp(),
            'billing' => $customer->get_billing(),
            'shipping' => $customer->get_shipping(),
        );
        try {
            $resp = $this->client->request('POST', '/woo-events', [
                'json' => [
                    'type'=> 'customer',
                    'customer' => $customer_json,
                    'visitor_id' => $this->get_snorkel_visitor_id()
                ],
                'headers' => $this->get_default_event_headers(),
            ]);
        } catch (Exception $e) {
            error_log('Error posting customer. ' . $e->getMessage(), 0);
        }
    }
    
    private function validate_and_sanitize_visitor_id($visitor_id) {
        $visitor_id = sanitize_key($visitor_id);
        if (strlen($visitor_id) == 36) {
            return $visitor_id;
        }
        else {
            throw new Exception('Invalid snorkel visitor_id ' . $visitor_id);
        }
    }

    private function get_snorkel_visitor_id() {
        return $this->validate_and_sanitize_visitor_id($_COOKIE['snorkel_visitor_id'] ?? $_SESSION['snorkel_visitor_id']);
    }

    private function create_uuid() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function get_or_create_cart_id() {
        $cart_id = WC()->session->get('snorkel_cart_id');
        if(is_null($cart_id)){
            $cart_id = $this->create_uuid();
            WC()->session->set('snorkel_cart_id', $cart_id);
        }
        return $cart_id;
    }
}
