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
	 * Registers [shipink_returns] and [shipink_tracking].
	 *
	 * @since    1.7.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'shipink_returns', array( $this, 'returns_shortcode' ) );
		add_shortcode( 'shipink_tracking', array( $this, 'tracking_shortcode' ) );
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
			esc_url( untrailingslashit( $base ) . '/api/woocommerce/return-requests' ),
			esc_attr( strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ),
			esc_attr( determine_locale() ),
			esc_attr( $atts['heading'] )
		);
	}

	/**
	 * [shipink_tracking heading="" accent="#111827"]: the block buyers track
	 * their order with. The markup and copy follow the Shopify app's
	 * blocks/tracking.liquid and locales; shipink-tracking.js and
	 * shipink-tracking.css are copies of its assets/tracking.js and
	 * assets/tracking.css, so change them there first. SHIPINK_RETURNS_BASE
	 * points this block at another Shipink environment too.
	 *
	 * @since    1.8.0
	 * @param    array|string    $atts    Shortcode attributes.
	 * @return   string
	 */
	public function tracking_shortcode( $atts ) {
		$atts   = shortcode_atts( array( 'heading' => '', 'accent' => '#111827' ), $atts, 'shipink_tracking' );
		$accent = sanitize_hex_color( $atts['accent'] );
		$base   = defined( 'SHIPINK_RETURNS_BASE' ) ? SHIPINK_RETURNS_BASE : 'https://ship.ink';
		$t      = 0 === strpos( determine_locale(), 'tr' ) ? self::tracking_copy_tr() : self::tracking_copy_en();

		wp_enqueue_style( 'shipink-tracking', plugin_dir_url( __FILE__ ) . 'css/shipink-tracking.css', array(), $this->version );
		wp_enqueue_script( 'shipink-tracking', plugin_dir_url( __FILE__ ) . 'js/shipink-tracking.js', array(), $this->version, true );

		$i18n = $t;
		unset( $i18n['heading'], $i18n['order_number'], $i18n['email_or_phone'], $i18n['submit'] );

		ob_start();
		?>
<div class="shipink-tracking" style="--shipink-accent: <?php echo esc_attr( $accent ? $accent : '#111827' ); ?>;" data-endpoint="<?php echo esc_url( untrailingslashit( $base ) . '/api/woocommerce/trackings' ); ?>" data-shop="<?php echo esc_attr( strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) ) ); ?>">
	<h2 class="shipink-tracking__heading"><?php echo esc_html( '' !== $atts['heading'] ? $atts['heading'] : $t['heading'] ); ?></h2>
	<form class="shipink-tracking__form" novalidate>
		<label class="shipink-tracking__field">
			<span><?php echo esc_html( $t['order_number'] ); ?></span>
			<input type="text" name="order" required autocomplete="off" placeholder="#1001">
		</label>
		<label class="shipink-tracking__field">
			<span><?php echo esc_html( $t['email_or_phone'] ); ?></span>
			<input type="text" name="contact" required autocomplete="email">
		</label>
		<button type="submit" class="shipink-tracking__button"><?php echo esc_html( $t['submit'] ); ?></button>
	</form>
	<p class="shipink-tracking__message" role="status" aria-live="polite" hidden></p>
	<div class="shipink-tracking__results"></div>
	<script type="application/json" class="shipink-tracking__i18n"><?php echo wp_json_encode( $i18n, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Turkish copy of the tracking block, from the Shopify app's locales/tr.json.
	 *
	 * @since    1.8.0
	 * @return   array
	 */
	private static function tracking_copy_tr() {
		return array(
			'heading'         => 'Kargo takip',
			'order_number'    => 'Sipariş numarası',
			'email_or_phone'  => 'E-posta veya telefon',
			'submit'          => 'Sorgula',
			'loading'         => 'Kargo bilgileri getiriliyor…',
			'not_found'       => 'Bu bilgilerle eşleşen bir gönderi bulunamadı. Sipariş numarasını ve siparişte kullandığınız e-posta ya da telefonu kontrol edin.',
			'invalid'         => 'Sipariş numarasını ve geçerli bir e-posta ya da telefon girin.',
			'too_many'        => 'Çok fazla deneme yaptınız. Lütfen bir dakika sonra tekrar deneyin.',
			'error'           => 'Kargo bilgileri şu an alınamıyor. Lütfen daha sonra tekrar deneyin.',
			'tracking_number' => 'Takip numarası',
			'details'         => 'Detaylı takip',
			'return_shipment' => 'İade gönderisi',
			'status'          => array(
				'pending'            => 'Bekliyor',
				'in-transit'         => 'Taşıma aşamasında',
				'in-delivery'        => 'Dağıtımda',
				'delivered'          => 'Teslim edildi',
				'delayed'            => 'Bekletiliyor',
				'returned'           => 'Geri geldi',
				'undelivered'        => 'Teslim edilemedi',
				'canceled'           => 'İptal edildi',
				'unknown'            => 'Bilinmiyor',
				'return-delivered'   => 'İade teslim edildi',
				'return-returned'    => 'İade geri geldi',
				'return-undelivered' => 'İade teslim edilemedi',
			),
		);
	}

	/**
	 * English copy of the tracking block, from the Shopify app's locales/en.default.json.
	 *
	 * @since    1.8.0
	 * @return   array
	 */
	private static function tracking_copy_en() {
		return array(
			'heading'         => 'Track your order',
			'order_number'    => 'Order number',
			'email_or_phone'  => 'Email or phone',
			'submit'          => 'Track',
			'loading'         => 'Fetching shipment details…',
			'not_found'       => 'No shipment matches these details. Check the order number and the email or phone you used for the order.',
			'invalid'         => 'Enter the order number and a valid email or phone.',
			'too_many'        => 'Too many attempts. Please try again in a minute.',
			'error'           => 'Shipment details are unavailable right now. Please try again later.',
			'tracking_number' => 'Tracking number',
			'details'         => 'Full tracking details',
			'return_shipment' => 'Return shipment',
			'status'          => array(
				'pending'            => 'Pending',
				'in-transit'         => 'In transit',
				'in-delivery'        => 'In delivery',
				'delivered'          => 'Delivered',
				'delayed'            => 'Delayed',
				'returned'           => 'Returned to sender',
				'undelivered'        => 'Undelivered',
				'canceled'           => 'Canceled',
				'unknown'            => 'Unknown',
				'return-delivered'   => 'Return delivered',
				'return-returned'    => 'Return sent back',
				'return-undelivered' => 'Return undelivered',
			),
		);
	}

}
