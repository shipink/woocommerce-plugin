<?php

/**
 * Live rates: Shipink turns them on from its panel by writing the callback
 * URL here, and the "Shipink" shipping method asks that URL for rates at
 * checkout.
 *
 * @since      1.6.0
 * @package    Shipink
 * @subpackage Shipink/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shipink_Live_Rates {

	const OPTION = 'shipink_live_rates';

	const REST_NAMESPACE = 'shipink/v1';

	/**
	 * Hosts the callback URL may point at. Anyone holding the store's API key
	 * can write the setting, so it must not become a way to send carts
	 * elsewhere.
	 */
	const ALLOWED_HOSTS = array( 'api.shipink.io', 'api.dev.shipink.io' );

	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'woocommerce_rest_is_request_to_rest_api', array( $this, 'authenticate_our_namespace' ) );
		add_action( 'woocommerce_shipping_init', array( $this, 'load_shipping_method' ) );
		add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
		add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'add_payment_method_to_packages' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'requote_on_payment_change' ) );
	}

	/**
	 * The gateway the buyer has chosen so far ('' before one is picked).
	 */
	public static function chosen_payment_method() {
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return '';
		}
		return (string) WC()->session->get( 'chosen_payment_method', '' );
	}

	/**
	 * Shipink prices cash-on-delivery services only for a COD order, so the
	 * rates depend on the payment method. WooCommerce re-rates a package only
	 * when the package changes: carrying the method in it makes a new choice
	 * re-ask Shipink.
	 */
	public function add_payment_method_to_packages( $packages ) {
		if ( ! self::settings()['enabled'] ) {
			return $packages;
		}
		$method = self::chosen_payment_method();
		foreach ( $packages as $key => $package ) {
			$packages[ $key ]['shipink_payment_method'] = $method;
		}
		return $packages;
	}

	/**
	 * Classic checkout refreshes shipping only on address changes; picking
	 * another payment method must refresh it too. The block checkout is not
	 * covered: it doesn't re-rate on a payment change.
	 */
	public function requote_on_payment_change() {
		if ( ! self::settings()['enabled'] || ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		wp_register_script( 'shipink-live-rates', '', array( 'jquery', 'wc-checkout' ), SHIPINK_VERSION, true );
		wp_enqueue_script( 'shipink-live-rates' );
		wp_add_inline_script(
			'shipink-live-rates',
			"jQuery( function ( $ ) { $( 'form.checkout' ).on( 'change', 'input[name=\"payment_method\"]', function () { $( document.body ).trigger( 'update_checkout' ); } ); } );"
		);
	}

	/**
	 * @return array{enabled: bool, callback_url: string}
	 */
	public static function settings() {
		$settings = get_option( self::OPTION, array() );
		return array(
			'enabled'      => ! empty( $settings['enabled'] ),
			'callback_url' => isset( $settings['callback_url'] ) ? (string) $settings['callback_url'] : '',
		);
	}

	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/live-rates',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'can_manage' ),
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'can_manage' ),
					'args'                => array(
						'enabled'      => array(
							'type'     => 'boolean',
							'required' => true,
						),
						'callback_url' => array(
							'type' => 'string',
						),
					),
				),
			)
		);
	}

	public function can_manage() {
		return current_user_can( 'manage_woocommerce' );
	}

	/**
	 * WooCommerce only checks consumer key/secret on its own REST namespaces;
	 * this lets the same key that syncs orders reach our route.
	 */
	public function authenticate_our_namespace( $is_rest_api_request ) {
		if ( $is_rest_api_request || empty( $_SERVER['REQUEST_URI'] ) ) {
			return $is_rest_api_request;
		}
		$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		return false !== strpos( $request_uri, trailingslashit( rest_get_url_prefix() ) . self::REST_NAMESPACE . '/' );
	}

	public function get_settings() {
		return rest_ensure_response( self::settings() );
	}

	public function update_settings( WP_REST_Request $request ) {
		$enabled      = (bool) $request->get_param( 'enabled' );
		$callback_url = (string) $request->get_param( 'callback_url' );

		if ( $enabled && ! self::is_allowed_callback( $callback_url ) ) {
			return new WP_Error( 'shipink_invalid_callback_url', 'callback_url must be an https Shipink API URL', array( 'status' => 400 ) );
		}

		$settings = array(
			'enabled'      => $enabled,
			// Keep the URL when turning off so a later "on" from an older
			// panel still has somewhere to call.
			'callback_url' => $enabled ? esc_url_raw( $callback_url ) : self::settings()['callback_url'],
		);
		update_option( self::OPTION, $settings, false );
		self::flush_cache();

		return rest_ensure_response( self::settings() );
	}

	private static function is_allowed_callback( $url ) {
		$parts = wp_parse_url( $url );
		return is_array( $parts )
			&& isset( $parts['scheme'], $parts['host'] )
			&& 'https' === $parts['scheme']
			&& in_array( $parts['host'], self::ALLOWED_HOSTS, true );
	}

	/**
	 * Cached rates belong to the old settings; a new token or turning off
	 * must take effect on the next cart update.
	 */
	public static function flush_cache() {
		update_option( 'shipink_live_rates_cache_version', time(), false );
	}

	public function load_shipping_method() {
		require_once plugin_dir_path( __FILE__ ) . 'class-shipink-shipping-method.php';
	}

	public function add_shipping_method( $methods ) {
		$methods['shipink'] = 'Shipink_Shipping_Method';
		return $methods;
	}
}
