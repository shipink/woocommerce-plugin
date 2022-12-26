<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://shipink.io
 * @since      1.0.0
 *
 * @package    Shipink
 * @subpackage Shipink/includes
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
 * @package    Shipink
 * @subpackage Shipink/includes
 * @author     Shipink <info@shipink.com>
 */
class Shipink {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Shipink_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	public function __construct() {
		if ( defined( 'SHIPINK_VERSION' ) ) {
			$this->version = SHIPINK_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'shipink';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->load_shipink_admin_menu();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Shipink_Loader. Orchestrates the hooks of the plugin.
	 * - Shipink_i18n. Defines internationalization functionality.
	 * - Shipink_Admin. Defines all hooks for the admin area.
	 * - Shipink_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shipink-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-shipink-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-shipink-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-shipink-public.php';

		$this->loader = new Shipink_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Shipink_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Shipink_i18n();

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

		$plugin_admin = new Shipink_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Shipink_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * @return    Shipink_Loader    Orchestrates the hooks of the plugin.
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

	private function load_shipink_admin_menu() {
		if ( is_admin() && ! is_network_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		}
	}

	public function create_admin_menu() {
		$shipink_label = __( 'Shipink', 'shipink' );
		add_submenu_page(
			'woocommerce',
			$shipink_label,
			$shipink_label,
			'manage_options',
			'wc-shipink',
			array( $this, 'shipink_admin_page')
		);
	}
	
	public function shipink_admin_page() {
		$file = rtrim( plugin_dir_path( __DIR__ ), '/' ).'/public/views/wc-settings/shipink-page.php';
		$html  = $this->render($file);
		echo wp_kses($html,array( $this, 'get_allowed_tags'));
	}
	
	public  function get_allowed_tags() {
		return array(
			'a'          => array(
				'class'  => array(),
				'href'   => array(),
				'rel'    => array(),
				'title'  => array(),
				'target' => array()
			),
			'abbr'       => array(
				'title' => array(),
			),
			'b'          => array(),
			'blockquote' => array(
				'cite' => array(),
			),
			'br'         => array(),
			'button'     => array(
				'class'    => array(),
				'id'       => array(),
				'disabled' => array(),
			),
			'cite'       => array(
				'title' => array(),
			),
			'code'       => array(),
			'del'        => array(
				'datetime' => array(),
				'title'    => array(),
			),
			'dd'         => array(),
			'div'        => array(
				'class' => array(),
				'id'    => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl'         => array(),
			'dt'         => array(),
			'em'         => array(),
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'hr'         => array(
				'class' => array()
			),
			'i'          => array(
				'class' => array()
			),
			'img'        => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'input'      => array(
				'id'    => array(),
				'class'  => array(),
				'name' => array(),
				'value'    => array(),
				'type'  => array(),
			),
			'li'         => array(
				'class' => array(),
			),
			'ol'         => array(
				'class' => array(),
			),
			'p'          => array(
				'class' => array(),
			),
			'path'       => array(
				'fill'            => array(),
				'd'               => array(),
				'class'           => array(),
				'data-v-19c3f3ae' => array()
			),
			'q'          => array(
				'cite'  => array(),
				'title' => array(),
			),
			'script'     => array(
				'type' => array(),
				'id'   => array(),
			),
			'span'       => array(
				'class'       => array(),
				'title'       => array(),
				'style'       => array(),
				'data-tip'    => array(),
				'data-target' => array(),
			),
			'strike'     => array(),
			'strong'     => array(),
			'svg'        => array(
				'aria-hidden'     => array(),
				'focusable'       => array(),
				'data-prefix'     => array(),
				'data-icon'       => array(),
				'role'            => array(),
				'xmlns'           => array(),
				'viewbox'         => array(),
				'class'           => array(),
				'data-v-19c3f3ae' => array(),
			),
			'table'      => array(
				'class' => array()
			),
			'tbody'      => array(
				'class' => array()
			),
			'thead'      => array(
				'class' => array()
			),
			'tr'         => array(
				'class'     => array(),
				'data-name' => array(),
			),
			'td'         => array(
				'class'   => array(),
				'colspan' => array(),
			),
			'ul'         => array(
				'id'    => array(),
				'class' => array(),
			),
		);
	}

	public function render($file) {
		require $file;
	}

}
