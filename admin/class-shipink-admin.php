<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://shipink.io
 * @since      1.5.1
 *
 * @package    Shipink
 * @subpackage Shipink/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Shipink
 * @subpackage Shipink/admin
 * @author     Shipink <info@shipink.com>
 */
class Shipink_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.5.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Hook suffix ending of the Shipink admin page.
	 *
	 * The full suffix is "{parent}_page_wc-shipink", where {parent} is derived
	 * from the WooCommerce menu title, so only the ending is matched.
	 *
	 * @since    1.5.4
	 * @var      string
	 */
	const SCREEN_SUFFIX = '_page_wc-shipink';

	/**
	 * Whether the given admin page is the Shipink admin page.
	 *
	 * @since    1.5.4
	 * @param    string    $hook_suffix    The current admin page.
	 * @return   bool
	 */
	private function is_shipink_screen( $hook_suffix ) {
		return is_string( $hook_suffix )
			&& substr( $hook_suffix, - strlen( self::SCREEN_SUFFIX ) ) === self::SCREEN_SUFFIX;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.5.1
	 * @param    string    $hook_suffix    The current admin page.
	 */
	public function enqueue_styles( $hook_suffix ) {

		if ( ! $this->is_shipink_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shipink-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.5.1
	 * @param    string    $hook_suffix    The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( ! $this->is_shipink_screen( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shipink-admin.js', array( 'jquery' ), $this->version, false );

	}

}
