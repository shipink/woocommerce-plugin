<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://shipink.io
 * @since      1.5.1
 *
 * @package    Shipink
 * @subpackage Shipink/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.5.1
 * @package    Shipink
 * @subpackage Shipink/includes
 * @author     Shipink <info@shipink.com>
 */
class Shipink_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.5.1
	 */
	public function load_plugin_textdomain() {

		$locale = apply_filters( 'plugin_locale', determine_locale(), 'shipink' );

		// Plugin'in kendi MO'sunu önceliklendir.
		// WP varsayılanı önce wp-content/languages/plugins/ altına bakar; orada
		// eski veya boş bir MO bulunursa plugin'in güncel MO'sunu hiç denemez.
		$plugin_mofile = SHIPINK_PLUGIN_DIR . 'languages/shipink-' . $locale . '.mo';
		if ( file_exists( $plugin_mofile ) && load_textdomain( 'shipink', $plugin_mofile ) ) {
			return;
		}

		// Fallback: standart WP yolu (resmi language pack indirilmişse onu kullanır).
		load_plugin_textdomain(
			'shipink',
			false,
			dirname( plugin_basename( SHIPINK_PLUGIN_FILE ) ) . '/languages/'
		);

	}



}
