<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://shipink.io
 * @since      1.5.1
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
 * @since      1.5.1
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
     * @since    1.5.1
     * @access   protected
     * @var      Shipink_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.5.1
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.5.1
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
     * @since    1.5.1
     */
    public function __construct()
    {
        if (defined('SHIPINK_VERSION')) {
            $this->version = SHIPINK_VERSION;
        } else {
            $this->version = '1.5.1';
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
     * @since    1.5.1
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
     * @since    1.5.1
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Shipink_i18n();

        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.5.1
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
     * @since    1.5.1
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
     * @since    1.5.1
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
     * @since     1.5.1
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Shipink_Loader    Orchestrates the hooks of the plugin.
     * @since     1.5.1
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.5.1
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
        add_action('init', array($this, 'custom_register_order_missing_status'));
        add_filter('woocommerce_order_actions', array($this, 'wdm_add_order_meta_box_actions'));
        add_action('woocommerce_order_action_mark_shipped', array($this, 'handle_mark_shipped'));
        add_action('woocommerce_order_action_mark_partially_shipped', array($this, 'handle_mark_partially_shipped'));
        add_filter('wc_order_statuses', array($this, 'add_missing_to_order_statuses'));
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
        if (!current_user_can('manage_options')) {
            return;
        }
        $is_connected = $this->is_shipink_connected();
        require rtrim(plugin_dir_path(__DIR__), '/') . '/public/views/wc-settings/shipink-page.php';
    }

    /**
     * Check if Shipink is connected.
     *
     * @return bool True if connected, false otherwise.
     */
    private function is_shipink_connected()
    {
        if (get_option('shipink_connected')) {
            return true;
        }

        if (!empty(get_option('woocommerce_shipink_settings'))) {
            return true;
        }

        global $wpdb;
        // WooCommerce exposes no API for reading its API keys, and this runs once
        // per view of our own settings screen, so a direct uncached query is fine.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $api_key_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}woocommerce_api_keys
                 WHERE description LIKE %s
                 AND permissions IN ('read_write', 'write')",
                '%shipink%'
            )
        );

        return $api_key_count > 0;
    }

    function custom_register_order_missing_status()
    {
        register_post_status('wc-shipped', array(
            'label' => __('Shipped', 'shipink'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Shipped (%s)', 'Shipped (%s)', 'shipink')
        ));

        register_post_status('wc-partially-shipped', array(
            'label' => __('Partially Shipped', 'shipink'),
            'public' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Partially Shipped (%s)', 'Partially Shipped (%s)', 'shipink')
        ));
    }

    function wdm_add_order_meta_box_actions($actions)
    {
        $actions['mark_shipped'] = __('Mark as Shipped', 'shipink');
        $actions['mark_partially_shipped'] = __('Mark as Partially Shipped', 'shipink');
        return $actions;
    }

    function handle_mark_shipped($order)
    {
        $order->update_status('shipped', __('Order marked as shipped via Shipink.', 'shipink'));
    }

    function handle_mark_partially_shipped($order)
    {
        $order->update_status('partially-shipped', __('Order marked as partially shipped via Shipink.', 'shipink'));
    }

    function add_missing_to_order_statuses($order_statuses)
    {
        $new_order_statuses = array();
        foreach ($order_statuses as $key => $status) {
            $new_order_statuses[$key] = $status;
            if ('wc-completed' === $key) {
                $new_order_statuses['wc-shipped'] = __('Shipped', 'shipink');
                $new_order_statuses['wc-partially-shipped'] = __('Partially Shipped', 'shipink');
            }
        }
        return $new_order_statuses;
    }

    function add_tracking_action_to_order_list($actions, $order)
    {
        $tracking_url = $order->get_meta('shipink_tracking_url');
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
        $tracking_url = $order->get_meta('shipink_tracking_url');
        if (!empty($tracking_url)) {
            echo '<p class="order-tracking"><strong>' . esc_html__('Tracking', 'shipink') . '</strong> <a href="' . esc_url($tracking_url) . '" target="_blank">' . esc_html($tracking_url) . '</a></p>';
        }
    }

    function add_custom_tracking_button_to_orders($actions, $order)
    {
        $tracking_url = $order->get_meta('shipink_tracking_url');
        if (!empty($tracking_url)) {
            $actions['shipink_tracking'] = array(
                'url' => $tracking_url,
                'name' => __('TRACKING', 'shipink')
            );
        }
        return $actions;
    }
}
