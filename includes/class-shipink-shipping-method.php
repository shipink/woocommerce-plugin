<?php

/**
 * "Shipink" shipping method: the merchant's Shipink carrier rates at
 * checkout. Added to shipping zones like any other method; offers nothing
 * until live rates are turned on from the Shipink panel.
 *
 * @since      1.6.0
 * @package    Shipink
 * @subpackage Shipink/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shipink_Shipping_Method extends WC_Shipping_Method {

	/** Seconds to wait for Shipink before the checkout moves on without us. */
	const TIMEOUT = 5;

	/** How long a cart's rates are reused (Shopify caches its carrier rates as long). */
	const CACHE_TTL = 15 * MINUTE_IN_SECONDS;

	/** A cart with no rates is asked again sooner. */
	const EMPTY_CACHE_TTL = MINUTE_IN_SECONDS;

	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'shipink';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Shipink', 'shipink' );
		$this->method_description = __( 'Shows your Shipink carrier rates at checkout. Turn on live rates for this store in the Shipink panel. Keep another method in the zone as a fallback.', 'shipink' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );

		$this->init();
	}

	private function init() {
		$this->instance_form_fields = array(
			'tax_status' => array(
				'title'   => __( 'Tax status', 'woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woocommerce' ),
					'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
				),
			),
		);
		$this->title      = $this->method_title;
		$this->tax_status = $this->get_option( 'tax_status', 'taxable' );

		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function calculate_shipping( $package = array() ) {
		$settings = Shipink_Live_Rates::settings();
		if ( ! $settings['enabled'] || '' === $settings['callback_url'] ) {
			return;
		}

		$payload = $this->rate_request( $package );
		$rates   = $this->fetch_rates( $settings['callback_url'], $payload );

		foreach ( $rates as $rate ) {
			if ( empty( $rate['id'] ) || ! isset( $rate['cost'] ) || ( isset( $rate['currency'] ) && $rate['currency'] !== $payload['currency'] ) ) {
				continue;
			}

			$meta = array( 'shipink_carrier_service_id' => (string) $rate['id'] );
			if ( ! empty( $rate['description'] ) ) {
				$meta[ __( 'Delivery', 'shipink' ) ] = (string) $rate['description'];
			}

			$this->add_rate(
				array(
					'id'        => $this->get_rate_id( sanitize_key( $rate['id'] ) ),
					'label'     => $this->rate_label( $rate ),
					// Net of tax: WooCommerce adds the store's shipping tax.
					'cost'      => (float) $rate['cost'],
					'package'   => $package,
					'meta_data' => $meta,
				)
			);
		}
	}

	/**
	 * "Aras Standart (Tahmini teslimat: 2 iş günü)": the checkout shows only
	 * the label, so the delivery estimate goes into it.
	 */
	private function rate_label( $rate ) {
		$label = isset( $rate['label'] ) && '' !== $rate['label'] ? (string) $rate['label'] : $this->title;
		if ( ! empty( $rate['description'] ) ) {
			$label .= ' (' . (string) $rate['description'] . ')';
		}
		return $label;
	}

	/**
	 * The gateway chosen so far, id and title: Shipink tells cash/card on
	 * delivery apart by them.
	 */
	private function payment_method() {
		$id    = Shipink_Live_Rates::chosen_payment_method();
		$title = '';
		if ( '' !== $id && function_exists( 'WC' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			if ( isset( $gateways[ $id ] ) ) {
				$title = (string) $gateways[ $id ]->get_title();
			}
		}
		return array(
			'id'    => $id,
			'title' => $title,
		);
	}

	/**
	 * The cart as Shipink's WooCommerce adapter reads it.
	 */
	private function rate_request( $package ) {
		$destination = isset( $package['destination'] ) ? $package['destination'] : array();
		$country     = isset( $destination['country'] ) ? (string) $destination['country'] : '';
		$state_code  = isset( $destination['state'] ) ? (string) $destination['state'] : '';

		// Shipink matches regions by name ("İstanbul"), WooCommerce stores a code ("TR34").
		$state_name = '';
		if ( '' !== $country && '' !== $state_code ) {
			$states     = WC()->countries->get_states( $country );
			$state_name = is_array( $states ) && isset( $states[ $state_code ] ) ? html_entity_decode( $states[ $state_code ], ENT_QUOTES, 'UTF-8' ) : '';
		}

		$items = array();
		foreach ( isset( $package['contents'] ) ? $package['contents'] : array() as $line ) {
			$product = isset( $line['data'] ) ? $line['data'] : null;
			if ( ! $product instanceof WC_Product || ! $product->needs_shipping() ) {
				continue;
			}
			$items[] = array(
				'quantity'     => (int) $line['quantity'],
				'weight_grams' => (float) wc_get_weight( (float) $product->get_weight(), 'g' ),
			);
		}

		return array(
			'destination' => array(
				'country'    => $country,
				'state_code' => $state_code,
				'state'      => $state_name,
				'city'       => isset( $destination['city'] ) ? (string) $destination['city'] : '',
				'postcode'   => isset( $destination['postcode'] ) ? (string) $destination['postcode'] : '',
				'address_1'  => isset( $destination['address_1'] ) ? (string) $destination['address_1'] : (string) ( isset( $destination['address'] ) ? $destination['address'] : '' ),
				'address_2'  => isset( $destination['address_2'] ) ? (string) $destination['address_2'] : '',
			),
			'items'       => $items,
			'currency'       => get_woocommerce_currency(),
			'locale'         => determine_locale(),
			'payment_method' => $this->payment_method(),
		);
	}

	/**
	 * @return array[] Rates as Shipink sent them; empty on any failure, so the
	 *                 zone's other methods are what the customer sees.
	 */
	private function fetch_rates( $callback_url, $payload ) {
		$cache_key = 'shipink_rates_' . md5( get_option( 'shipink_live_rates_cache_version', '' ) . '|' . $callback_url . '|' . wp_json_encode( $payload ) );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = wp_remote_post(
			$callback_url,
			array(
				'timeout' => self::TIMEOUT,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			// Not cached: Shipink being slow once shouldn't hide rates for minutes.
			$this->log( is_wp_error( $response ) ? $response->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code( $response ) );
			return array();
		}

		$body  = json_decode( wp_remote_retrieve_body( $response ), true );
		$rates = is_array( $body ) && isset( $body['rates'] ) && is_array( $body['rates'] ) ? $body['rates'] : array();

		set_transient( $cache_key, $rates, empty( $rates ) ? self::EMPTY_CACHE_TTL : self::CACHE_TTL );
		return $rates;
	}

	private function log( $message ) {
		if ( function_exists( 'wc_get_logger' ) ) {
			wc_get_logger()->warning( 'Shipink live rates: ' . $message, array( 'source' => 'shipink' ) );
		}
	}
}
