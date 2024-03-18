<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://shipink.io
 * @since      1.2.0
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
 * @since      1.2.0
 * @package    Shipink
 * @subpackage Shipink/includes
 * @author     Shipink <info@shipink.com>
 */
class Shipink
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.2.0
     * @access   protected
     * @var      Shipink_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.2.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.2.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.2.0
     */
    public function __construct()
    {
        if (defined('SHIPINK_VERSION')) {
            $this->version = SHIPINK_VERSION;
        } else {
            $this->version = '1.2.0';
        }
        $this->plugin_name = 'shipink';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->load_shipink_admin_menu();
        $this->load_shipink_wc_status();

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
     * @since    1.2.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipink-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-shipink-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-shipink-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-shipink-public.php';

        $this->loader = new Shipink_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Shipink_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.2.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Shipink_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.2.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Shipink_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.2.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Shipink_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.2.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     1.2.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Shipink_Loader    Orchestrates the hooks of the plugin.
     * @since     1.2.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.2.0
     */
    public function get_version()
    {
        return $this->version;
    }

    private function load_shipink_admin_menu()
    {
        if (is_admin() && !is_network_admin()) {
            add_action('admin_menu', array($this, 'create_admin_menu'));
        }
    }

    private function load_shipink_wc_status()
    {
        add_action('init', array($this, 'custom_register_order_shipped_status'));
        add_action('woocommerce_order_actions', array($this, 'wdm_add_order_meta_box_actions'));
        add_filter('wc_order_statuses', array($this, 'add_shipped_to_order_statuses'));
        add_filter('woocommerce_admin_order_actions', array($this, 'add_tracking_action_to_order_list'), 10, 2);
        add_action('woocommerce_order_details_after_order_table', array($this, 'add_tracking_info_to_order_details'), 10, 1);
        add_filter('woocommerce_my_account_my_orders_actions', array($this, 'add_custom_tracking_button_to_orders'), 10, 2);
    }

    public function create_admin_menu()
    {
        $shipink_label = __('Shipink', 'shipink');
        add_submenu_page(
            'woocommerce',
            $shipink_label,
            $shipink_label,
            'manage_options',
            'wc-shipink',
            array($this, 'shipink_admin_page')
        );
    }

    public function shipink_admin_page()
    {
        $file = rtrim(plugin_dir_path(__DIR__), '/') . '/public/views/wc-settings/shipink-page.php';
        $html = $this->render($file);
        echo wp_kses($html, array($this, 'get_allowed_tags'));
    }

    public function get_allowed_tags()
    {
        return array(
            'a' => array(
                'class' => array(),
                'href' => array(),
                'rel' => array(),
                'title' => array(),
                'target' => array()
            ),
            'abbr' => array(
                'title' => array(),
            ),
            'b' => array(),
            'blockquote' => array(
                'cite' => array(),
            ),
            'br' => array(),
            'button' => array(
                'class' => array(),
                'id' => array(),
                'disabled' => array(),
            ),
            'cite' => array(
                'title' => array(),
            ),
            'code' => array(),
            'del' => array(
                'datetime' => array(),
                'title' => array(),
            ),
            'dd' => array(),
            'div' => array(
                'class' => array(),
                'id' => array(),
                'title' => array(),
                'style' => array(),
            ),
            'dl' => array(),
            'dt' => array(),
            'em' => array(),
            'h1' => array(),
            'h2' => array(),
            'h3' => array(),
            'h4' => array(),
            'h5' => array(),
            'h6' => array(),
            'hr' => array(
                'class' => array()
            ),
            'i' => array(
                'class' => array()
            ),
            'img' => array(
                'alt' => array(),
                'class' => array(),
                'height' => array(),
                'src' => array(),
                'width' => array(),
            ),
            'input' => array(
                'id' => array(),
                'class' => array(),
                'name' => array(),
                'value' => array(),
                'type' => array(),
            ),
            'li' => array(
                'class' => array(),
            ),
            'ol' => array(
                'class' => array(),
            ),
            'p' => array(
                'class' => array(),
            ),
            'path' => array(
                'fill' => array(),
                'd' => array(),
                'class' => array(),
                'data-v-19c3f3ae' => array()
            ),
            'q' => array(
                'cite' => array(),
                'title' => array(),
            ),
            'script' => array(
                'type' => array(),
                'id' => array(),
            ),
            'span' => array(
                'class' => array(),
                'title' => array(),
                'style' => array(),
                'data-tip' => array(),
                'data-target' => array(),
            ),
            'strike' => array(),
            'strong' => array(),
            'svg' => array(
                'aria-hidden' => array(),
                'focusable' => array(),
                'data-prefix' => array(),
                'data-icon' => array(),
                'role' => array(),
                'xmlns' => array(),
                'viewbox' => array(),
                'class' => array(),
                'data-v-19c3f3ae' => array(),
            ),
            'table' => array(
                'class' => array()
            ),
            'tbody' => array(
                'class' => array()
            ),
            'thead' => array(
                'class' => array()
            ),
            'tr' => array(
                'class' => array(),
                'data-name' => array(),
            ),
            'td' => array(
                'class' => array(),
                'colspan' => array(),
            ),
            'ul' => array(
                'id' => array(),
                'class' => array(),
            ),
        );
    }

    public function render($file)
    {
        require $file;
    }

    function custom_register_order_shipped_status()
    {
        register_post_status('wc-shipped', array(
            'label' => __('Shipped', 'shipink'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Shipped (%s)', 'Shipped (%s)', 'shipink')
        ));
    }

    function wdm_add_order_meta_box_actions($actions)
    {
        $actions['wc-shipped'] = __('Shipped', 'shipink');
        return $actions;
    }

    function add_shipped_to_order_statuses($order_statuses)
    {
        $new_order_statuses = array();
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-completed' === $key) {
                $new_order_statuses['wc-shipped'] = __('Shipped', 'shipink');
            }
        }
        return $new_order_statuses;
    }

    function add_tracking_action_to_order_list($actions, $order)
    {
        $tracking_url = get_post_meta($order->get_id(), 'shipink_tracking_url', true);
        if (!empty($tracking_url)) {
            $actions['shipink_tracking'] = array(
                'url' => $tracking_url,
                'name' => __('Tracking', 'shipink'),
                'action' => 'link',
            );
        }
        return $actions;
    }

    function add_tracking_info_to_order_details($order)
    {
        $tracking_url = get_post_meta($order->get_id(), 'shipink_tracking_url', true);
        if (!empty($tracking_url)) {
            echo '<p class="order-tracking"><strong>' . __('Tracking', 'shipink') . '</strong> <a href="' . esc_url($tracking_url) . '" target="_blank">' . esc_url($tracking_url) . '</a></p>';
        }
    }

    function add_custom_tracking_button_to_orders($actions, $order)
    {
        $tracking_url = get_post_meta($order->get_id(), 'shipink_tracking_url', true);
        if (!empty($tracking_url)) {
            $actions['shipink_tracking'] = array(
                'url' => $tracking_url,
                'name' => __('TRACKING', 'shipink')
            );
        }
        return $actions;
    }
}
