<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://snorkelapp.com
 * @since      1.0.0
 *
 * @package    Snorkel
 * @subpackage Snorkel/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Snorkel
 * @subpackage Snorkel/admin
 * @author     Benjamin Schultz <bens@olark.com>
 */
use GuzzleHttp\Client;

require_once plugin_dir_path( __FILE__ ) . '/../utils.php';

class Snorkel_Admin {

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version, $url ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->url = $url;
        $this->client = new Client([
            'base_uri' => $url,
            'timeout' => 2.0,
            'http_errors' => FALSE,
        ]);

    }

    /**
     * Register the stylesheets for the admin area.
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

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/snorkel-admin.css', array(), $this->version, 'all' );

    }

    public function validate_settings($raw_settings) {

        $message = NULL;
        $type = NULL;
        $valid_settings = array();

        if (isset($raw_settings['snorkel_api_key'])) {
            $api_key = sanitize_key($raw_settings['snorkel_api_key']);
            if (strlen($api_key) == 32) {
                $valid_settings['snorkel_api_key'] = $api_key;
                $message = 'Snorkel settings have been updated';
                $type = 'updated';
            }
            else {
                $message = 'Snorkel settings were not updated. ' . esc_html($raw_settings['snorkel_api_key'])  . ' is not a valid key';
                $type = 'error';
            }
        }

        add_settings_error($this->plugin_name, $this->plugin_name . '_settings_notice', $message, $type);

        return $valid_settings;
    }

    public function update_settings() {
        register_setting($this->plugin_name, $this->plugin_name, ['sanitize_callback' => array($this, 'validate_settings')]);
    }

    public function add_plugin_admin_menu(){
        add_options_page('Snorkel Settings', 'Snorkel Settings', 'manage_options', $this->plugin_name,
            array( $this, 'display_plugin_setup_page')
        );
    }

    public function display_plugin_setup_page() {
        include_once( 'partials/snorkel-admin-display.php' );
    }

    public function add_action_links( $links ) {
        $settings_link = array(
            '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '    </a>',
        );
        return array_merge( $settings_link, $links );
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

    public function post_product_data($product_id) {
        $product = wc_get_product($product_id);
        try{
            $snorkel_api_key = $this->get_sanitized_snorkel_api_key();
            $resp = $this->client->request('POST', '/woo-events', [
                'json' => [
                    'type'=> 'product',
                    'product' => product_to_json($product),
                ],
                'headers' => ['X-Snorkel-API-Key' => $this->get_sanitized_snorkel_api_key()],
            ]);
        } catch (Exception $e){
            error_log('Error posting product data. ' . $e->getMessage(), 0);
        }
    }

    public function post_all_product_data() {
        $page = 1;
        $PAGE_SIZE = 100;
        $products_to_send = array();
        $more_products = TRUE;
        while( $more_products ){

            $results = wc_get_products(
                array(
                    'limit' => $PAGE_SIZE,
                    'orderby' => 'ID',
                    'order' => 'DESC',
                    'paginate' => TRUE,
                    'page' => $page,
                )
            );

            foreach($results->products as $product){
                $products_to_send[] = product_to_json($product);
            }

            try {
                $resp = $this->client->request('POST', '/woo-events', [
                    'json' => [
                        'type'=> 'products',
                        'products' => $products_to_send,
                        'page' => $page,
                    ],
                    'headers' => ['X-Snorkel-API-Key' => $this->get_sanitized_snorkel_api_key()],
                ]);
            } catch (Exception $e){
                error_log('Error posting all product data. ' . $e->getMessage(), 0);
                return;
            }

            $more_products = $page < $results->max_num_pages;
            $page++;
        }
    }
}
