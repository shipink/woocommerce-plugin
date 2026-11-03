<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://shipink.io
 * @since      1.5.1
 *
 * @package    Shipink
 * @subpackage Shipink/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Shipink
 * @subpackage Shipink/public
 * @author     Shipink <info@shipink.com>
 */
class Shipink_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.5.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipink_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipink_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/shipink-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.5.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Shipink_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Shipink_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/shipink-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Registers [shipink_returns].
	 *
	 * @since    1.7.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'shipink_returns', array( $this, 'returns_shortcode' ) );
	}

	/**
	 * [shipink_returns heading="" accent="#111827"]: the block buyers request a
	 * return with. shipink-returns.js builds the whole block inside this root.
	 * Define SHIPINK_RETURNS_BASE in wp-config.php to point it at another
	 * Shipink environment (e.g. https://dev.ship.ink) for testing.
	 *
	 * @since    1.7.0
	 * @param    array|string    $atts    Shortcode attributes.
	 * @return   string
	 */
	public function returns_shortcode( $atts ) {
		$atts   = shortcode_atts( array( 'heading' => '', 'accent' => '#111827' ), $atts, 'shipink_returns' );
		$accent = sanitize_hex_color( $atts['accent'] );
		$base   = defined( 'SHIPINK_RETURNS_BASE' ) ? SHIPINK_RETURNS_BASE : 'https://ship.ink';

		wp_enqueue_style( 'shipink-returns', plugin_dir_url( __FILE__ ) . 'css/shipink-returns.css', array(), $this->version );
		wp_enqueue_script( 'shipink-returns', plugin_dir_url( __FILE__ ) . 'js/shipink-returns.js', array(), $this->version, true );

		return sprintf(
			'<div class="shipink-returns" style="--shipink-accent: %s;" data-endpoint="%s" data-shop="%s" data-locale="%s" data-heading="%s"></div>',
			esc_attr( $accent ? $accent : '#111827' ),
			esc_url( untrailingslashit( $base ) . '/api/returns/woocommerce' ),
			esc_attr( strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ),
			esc_attr( determine_locale() ),
			esc_attr( $atts['heading'] )
		);
	}

}
