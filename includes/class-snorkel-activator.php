<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Snorkel
 * @subpackage Snorkel/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Snorkel
 * @subpackage Snorkel/includes
 * @author     Your Name <email@example.com>
 */
require_once plugin_dir_path( __FILE__ ) . '/../vendor/autoload.php';
require_once plugin_dir_path( __FILE__ ) . '/../utils.php';

use GuzzleHttp\Client;

class Snorkel_Activator {

	/**
	 * Uncover valuable insights in visitor interactions, see patterns in customer behaviour and make informed decisions to increase your ecommerce sales.
	 *
	 * Discover why visitors don't buy. Then change it. Uncover valuable insights in visitor interactions, see patterns in customer behaviour and make informed decisions to increase your ecommerce sales.
	 *
	 * @since    1.0.0
	 */
  public static function activate($url) {
      if (!defined('WC_VERSION')) {
        trigger_error('Snorkel cannot be activated without WooCommerce', E_USER_ERROR);
      }
  }
}
