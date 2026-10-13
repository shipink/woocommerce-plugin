=== Shipink ===
Version: 1.6.0
Contributors: shipink
Tags: shipping,kargo,label,etiket
Requires at least: 5.4
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.6.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect your WooCommerce orders to 15+ carriers. Discounted rates, bulk labels, branded tracking, and AI address correction.
== Description ==

[youtube https://www.youtube.com/watch?v=arJ7d_5ddJI]

[Shipink](https://shipink.io/) turns WooCommerce into a multi-carrier shipping powerhouse. Pull orders automatically, print labels with discounted rates across 15+ carriers, and give customers a branded tracking experience — all from one panel.

Shipink Overview
* Discounted Carrier Rates — Save up to 60%. Aras, DHL eCommerce, Yurtiçi, UPS, FedEx, PTT, Sendeo, HepsiJET, Aramex, and more.
* Automatic Order Sync — WooCommerce orders flow into Shipink instantly. Tracking codes write back automatically.
* Bulk Shipping — Print hundreds of labels in one click. Picking lists, multi-package, multi-warehouse out of the box.
* Branded Tracking Page — Your domain, your design. Customers track every shipment in one place, across all carriers.
* AI Address Correction — Auto-fix addresses with 99.81% accuracy. Cut failed deliveries to near zero.
* Email + SMS Notifications — Every step gets an automatic customer update.
* Cash on Delivery & Returns Management
* Full API + Webhooks for developers.

Supported Carriers:
*   Aras Kargo
*   DHL eCommerce
*   Yurtiçi Kargo
*   PTT
*   HepsiJET
*   Kolay Gelsin
*   Sürat Kargo
*   UPS Global
*   Fedex
*   Aramex
*   and [more](https://shipink.io/carriers/)

How It Works:
1. Install the Shipink plugin and connect your WooCommerce store.
2. Orders flow into Shipink automatically.
3. Print labels at our discounted rates or with your own carrier agreement.
4. Tracking codes sync back to WooCommerce; customers get automatic notifications.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/shipink` directory,
2. Activate the plugin through the 'Plugins' screen in WordPress or install the plugin through the WordPress plugins screen directly (recommended).

== Frequently Asked Questions ==

= What WooCommerce version does the plugin support? =

The plugin requires WooCommerce 5.0 or higher.

== Screenshots ==

1. Sync orders
2. Compare rates
3. Bulk operations
4. Branded tracking page
5. Automation and AI

== Changelog ==

= 1.6.0 =
* New "Shipink" shipping method: show your Shipink carrier rates to customers at checkout. Turn on live rates for the store in the Shipink panel, then add the method to your shipping zones.
* Rates follow the chosen payment method: with cash or card on delivery, only carriers that can collect at the door are offered.

= 1.5.5 =
* Added an "Open in Shipink" button to the order edit screen

= 1.5.4 =
* Added support for WordPress 7.1
* Fixed inaccurate minimum requirements (now WordPress 5.4 and PHP 7.4)
* Admin styles and scripts are no longer loaded on unrelated admin pages
* The admin page logo is now bundled with the plugin instead of being loaded from a remote server

= 1.5.3 =
* Fixed "Connect to Shipink" link for the new Shipink app (signup page moved)

= 1.5.2 =
* Fixed XSS vulnerability in the admin settings page
* Fixed broken signup query string encoding
* Fixed connection status check (now stored as a global option)
* Fixed "Mark as Shipped" / "Mark as Partially Shipped" order actions
* Added HPOS (High-Performance Order Storage) compatibility
* Fixed admin page rendering and multisite store id handling

= 1.5.1 =
* Added "Reconnect Shipink" button for disconnected status
* Added "Partially Shipped" order status for WooCommerce

= 1.5.0 =
* Added "Open Shipink" button for already connected status

= 1.4.0 =
* Added support for WordPress 6.8.1

= 1.4.0 =
* Added support for WordPress 6.7.2

= 1.4.0 =
* Added "Tracking" button for WooCommerce Orders Page
* Added support for WordPress 6.4.3

= 1.1.0 =
* Added "Shipped" order status for WooCommerce
* Added support for WordPress 6.4.1

= 1.0.0 =
* Init