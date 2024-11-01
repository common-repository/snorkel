<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://snorkelapp.com
 * @since      1.0.0
 *
 * @package    Snorkel
 * @subpackage Snorkel/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Snorkel
 * @subpackage Snorkel/includes
 * @author     Your Name <email@example.com>
 */
class Snorkel {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Snorkel_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct($url) {
		if ( defined( 'SNORKEL_VERSION' ) ) {
			$this->version = SNORKEL_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'snorkel';

		$this->load_dependencies();
		$this->set_locale();
        $this->url = $url;
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Snorkel_Loader. Orchestrates the hooks of the plugin.
	 * - Snorkel_i18n. Defines internationalization functionality.
	 * - Snorkel_Admin. Defines all hooks for the admin area.
	 * - Snorkel_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-snorkel-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-snorkel-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-snorkel-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-snorkel-public.php';

 		$this->loader = new Snorkel_Loader($this->url);
 
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Snorkel_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Snorkel_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Snorkel_Admin( $this->get_plugin_name(), $this->get_version(), $this->url );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

        // Add Settings link to the plugin
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
        $this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );
        $this->loader->add_action('admin_init', $plugin_admin, 'update_settings');

        $this->loader->add_action('update_option_snorkel', $plugin_admin, 'post_all_product_data');
        $this->loader->add_action('woocommerce_update_product', $plugin_admin, 'post_product_data', 10, 2);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Snorkel_Public( $this->get_plugin_name(), $this->get_version(), $this->url );
        
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        # cart data
        $this->loader->add_action( 'woocommerce_add_to_cart', $plugin_public, 'post_cart_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_update_cart', $plugin_public, 'post_cart_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_remove_cart_item', $plugin_public, 'post_cart_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_restore_cart_item', $plugin_public, 'post_cart_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_cart_emptied', $plugin_public, 'post_cart_data', 10, 2 );

        # order data
        $this->loader->add_action( 'woocommerce_new_order', $plugin_public, 'post_order_data', 10, 2);
        $this->loader->add_action( 'woocommerce_update_order', $plugin_public, 'post_order_data', 10, 2);
        $this->loader->add_action( 'woocommerce_order_partially_refunded', $plugin_public, 'post_order_data', 10, 2);
        $this->loader->add_action( 'woocommerce_order_refunded', $plugin_public, 'post_order_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_order_status_changed', $plugin_public, 'post_order_data', 10, 2 );
        $this->loader->add_action( 'woocommerce_payment_complete', $plugin_public, 'post_order_data', 10, 2 );

        #customer data
        $this->loader->add_action( 'woocommerce_new_customer', $plugin_public, 'post_customer_data', 10, 2);
        $this->loader->add_action( 'woocommerce_update_customer', $plugin_public, 'post_customer_data', 10, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Snorkel_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
