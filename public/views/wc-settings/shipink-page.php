<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user = wp_get_current_user();

$params = array(
    'source'            => 'woocommerce',
    'email'             => $user->user_email,
    'first_name'        => !empty($user->first_name) ? $user->first_name : $user->user_nicename,
    'last_name'         => !empty($user->last_name) ? $user->last_name : '',
    'mobile_phone'      => !empty($user->billing_phone) ? $user->billing_phone : '',
    'platform_store_id' => get_current_blog_id(),
    'name'              => get_option('blogname'),
    'url'               => get_option('home'),
);
$signup_url = add_query_arg($params, 'https://app.shipink.io/');

$connection_status = isset($is_connected) && $is_connected;

$button_text = $connection_status
    ? __('Open Shipink Dashboard', 'shipink')
    : __('Connect to Shipink', 'shipink');

$target_url = $connection_status
    ? 'https://app.shipink.io'
    : $signup_url;

$features = array(
    array(
        'icon'  => 'cart',
        'title' => __('Order Management', 'shipink'),
        'desc'  => __('Ship e-commerce orders easily from one place to shorten fulfillment time', 'shipink'),
    ),
    array(
        'icon'  => 'archive',
        'title' => __('All Labels in One Place', 'shipink'),
        'desc'  => __('Manage shipping labels for various global carriers like Aras, MNG, UPS, Aramex, or FedEx with just a few clicks', 'shipink'),
    ),
    array(
        'icon'  => 'chart-line',
        'title' => __('Best Shipping Rates', 'shipink'),
        'desc'  => __('Find the cheapest or fastest shipping company for your shipment with up to 60% discounts', 'shipink'),
    ),
    array(
        'icon'  => 'location',
        'title' => __('Advanced Tracking', 'shipink'),
        'desc'  => __('Track all packages in one place with branded tracking pages and automatic notifications', 'shipink'),
    ),
    array(
        'icon'  => 'admin-site-alt3',
        'title' => __('International Shipping', 'shipink'),
        'desc'  => __('Save cost and time on international shipping with many carriers worldwide', 'shipink'),
    ),
    array(
        'icon'  => 'rest-api',
        'title' => __('Multi-carrier API', 'shipink'),
        'desc'  => __('Shipping APIs designed for developers to integrate carriers worldwide', 'shipink'),
    ),
);

$carriers = array(
    'MNG Kargo', 'Aramex', 'Yurtiçi Kargo', 'Sendeo', 'Aras Kargo',
    'Kolay Gelsin', 'HepsiJET', 'UPS', 'FedEx', 'PTT',
    'Octovan Express', 'Sürat Kargo', 'KargoTurk', 'Horoz Lojistik', 'Banabikurye',
);

$checks = array();

if (class_exists('WooCommerce')) {
    $wc_version = defined('WC_VERSION') ? WC_VERSION : '';
    $wc_min     = '5.0';
    $wc_ok      = $wc_version && version_compare($wc_version, $wc_min, '>=');
    $checks[]   = array(
        'label' => __('WooCommerce', 'shipink'),
        'value' => $wc_version ? 'v' . $wc_version : __('Unknown', 'shipink'),
        'ok'    => $wc_ok,
        'note'  => $wc_ok ? '' : sprintf(__('Requires v%s or higher', 'shipink'), $wc_min),
    );
} else {
    $checks[] = array(
        'label' => __('WooCommerce', 'shipink'),
        'value' => __('Not active', 'shipink'),
        'ok'    => false,
        'note'  => __('WooCommerce must be installed and active', 'shipink'),
    );
}

$php_min   = '7.4';
$php_ok    = version_compare(PHP_VERSION, $php_min, '>=');
$checks[]  = array(
    'label' => __('PHP version', 'shipink'),
    'value' => 'v' . PHP_VERSION,
    'ok'    => $php_ok,
    'note'  => $php_ok ? '' : sprintf(__('Requires v%s or higher', 'shipink'), $php_min),
);

$curl_ok  = function_exists('curl_version');
$checks[] = array(
    'label' => __('cURL extension', 'shipink'),
    'value' => $curl_ok ? __('Available', 'shipink') : __('Missing', 'shipink'),
    'ok'    => $curl_ok,
    'note'  => $curl_ok ? '' : __('Required for API requests', 'shipink'),
);

$ssl_ok   = is_ssl();
$checks[] = array(
    'label' => __('SSL (HTTPS)', 'shipink'),
    'value' => $ssl_ok ? __('Enabled', 'shipink') : __('Disabled', 'shipink'),
    'ok'    => $ssl_ok,
    'note'  => $ssl_ok ? '' : __('Recommended for secure webhook delivery', 'shipink'),
);

$permalink_ok = (bool) get_option('permalink_structure');
$checks[]     = array(
    'label' => __('Permalinks', 'shipink'),
    'value' => $permalink_ok ? __('Pretty', 'shipink') : __('Plain', 'shipink'),
    'ok'    => $permalink_ok,
    'note'  => $permalink_ok ? '' : __('Plain permalinks can break webhooks. Settings → Permalinks', 'shipink'),
);

$issues_count = count(array_filter($checks, function ($c) { return ! $c['ok']; }));
?>

<style>
.shipink-wrap .shipink-heading-logo {
    width: 24px;
    height: 24px;
    vertical-align: text-bottom;
    margin-right: 6px;
}
.shipink-wrap .shipink-status-pill {
    display: inline-block;
    margin-left: 8px;
    padding: 2px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1.6;
    vertical-align: middle;
}
.shipink-wrap .shipink-status-pill.is-connected {
    background: #edfaef;
    color: #00733b;
    border: 1px solid #b8e6c1;
}
.shipink-wrap .shipink-status-pill.is-disconnected {
    background: #fcf0f1;
    color: #b32d2e;
    border: 1px solid #f1b3b4;
}

.shipink-wrap .shipink-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
    gap: 20px;
    margin-top: 20px;
}
.shipink-wrap .shipink-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    padding: 20px 24px;
}
.shipink-wrap .shipink-card h2 {
    font-size: 16px;
    margin: 0 0 16px;
    padding: 0;
    color: #1d2327;
}
.shipink-wrap .shipink-card h2:not(:first-child) {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f1;
}

.shipink-wrap .shipink-feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}
.shipink-wrap .shipink-feature {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.shipink-wrap .shipink-feature .dashicons {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    font-size: 20px;
    line-height: 32px;
    text-align: center;
    background: #f0f6fc;
    color: #2271b1;
    border-radius: 3px;
}
.shipink-wrap .shipink-feature h3 {
    margin: 0 0 4px;
    font-size: 13px;
    font-weight: 600;
    color: #1d2327;
}
.shipink-wrap .shipink-feature p {
    margin: 0;
    font-size: 13px;
    line-height: 1.5;
    color: #50575e;
}

.shipink-wrap .shipink-carriers {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}
.shipink-wrap .shipink-carrier {
    padding: 4px 10px;
    background: #f6f7f7;
    border: 1px solid #dcdcde;
    border-radius: 3px;
    font-size: 12px;
    color: #2c3338;
    line-height: 1.5;
}

.shipink-wrap .shipink-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-width: 0;
}

.shipink-wrap .shipink-cta-card .button-hero {
    display: block;
    width: 100%;
    text-align: center;
    margin-bottom: 8px;
}
.shipink-wrap .shipink-cta-card .button:not(.button-hero) {
    display: block;
    width: 100%;
    text-align: center;
}
.shipink-wrap .shipink-cta-card p {
    color: #50575e;
    font-size: 13px;
    line-height: 1.6;
    margin: 0 0 16px;
}
.shipink-wrap .shipink-benefits {
    margin: 20px 0 0;
    padding: 16px 0 0;
    border-top: 1px solid #f0f0f1;
    list-style: none;
}
.shipink-wrap .shipink-benefits li {
    margin: 0 0 6px;
    padding-left: 22px;
    position: relative;
    font-size: 13px;
    color: #2c3338;
    line-height: 1.5;
}
.shipink-wrap .shipink-benefits li:last-child {
    margin-bottom: 0;
}
.shipink-wrap .shipink-benefits li:before {
    content: "\f147";
    font-family: dashicons;
    position: absolute;
    left: 0;
    top: -1px;
    color: #00a32a;
    font-size: 18px;
    line-height: 1;
}

.shipink-wrap .shipink-status-card h2 .shipink-issue-count {
    display: inline-block;
    margin-left: 6px;
    padding: 1px 8px;
    border-radius: 10px;
    background: #fcf0f1;
    color: #b32d2e;
    font-size: 11px;
    font-weight: 500;
    vertical-align: middle;
}
.shipink-wrap .shipink-status-list {
    margin: 0;
    padding: 0;
    list-style: none;
}
.shipink-wrap .shipink-status-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f1;
}
.shipink-wrap .shipink-status-item:first-child {
    padding-top: 0;
}
.shipink-wrap .shipink-status-item:last-child {
    border-bottom: 0;
    padding-bottom: 0;
}
.shipink-wrap .shipink-status-item .dashicons {
    flex-shrink: 0;
    font-size: 18px;
    width: 18px;
    height: 18px;
    line-height: 18px;
    margin-top: 1px;
}
.shipink-wrap .shipink-status-item.is-ok .dashicons { color: #00a32a; }
.shipink-wrap .shipink-status-item.is-warn .dashicons { color: #dba617; }
.shipink-wrap .shipink-status-text {
    flex: 1;
    min-width: 0;
    font-size: 13px;
    line-height: 1.5;
}
.shipink-wrap .shipink-status-text .shipink-status-row {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    color: #1d2327;
    font-weight: 500;
}
.shipink-wrap .shipink-status-text .shipink-status-row span {
    color: #50575e;
    font-weight: 400;
}
.shipink-wrap .shipink-status-text em {
    display: block;
    margin-top: 2px;
    font-size: 12px;
    font-style: normal;
    color: #b32d2e;
}

@media (max-width: 960px) {
    .shipink-wrap .shipink-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="wrap shipink-wrap">
    <h1 class="wp-heading-inline">
        <img src="https://app.shipink.io/logo-icon-login.svg" alt="" class="shipink-heading-logo" />
        <?php esc_html_e('Shipink Multi-Carrier Shipping', 'shipink'); ?>
    </h1>
    <span class="shipink-status-pill <?php echo $connection_status ? 'is-connected' : 'is-disconnected'; ?>">
        <?php echo $connection_status ? esc_html__('Connected', 'shipink') : esc_html__('Not Connected', 'shipink'); ?>
    </span>
    <p class="description"><?php esc_html_e('Complete shipping solution for e-commerce', 'shipink'); ?></p>
    <hr class="wp-header-end">

    <div class="shipink-grid">
        <div class="shipink-card">
            <h2><?php esc_html_e('Complete Shipping Solution', 'shipink'); ?></h2>

            <div class="shipink-feature-grid">
                <?php foreach ($features as $feature): ?>
                    <div class="shipink-feature">
                        <span class="dashicons dashicons-<?php echo esc_attr($feature['icon']); ?>" aria-hidden="true"></span>
                        <div>
                            <h3><?php echo esc_html($feature['title']); ?></h3>
                            <p><?php echo esc_html($feature['desc']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2><?php esc_html_e('Supported Carriers', 'shipink'); ?></h2>
            <div class="shipink-carriers">
                <?php foreach ($carriers as $carrier): ?>
                    <span class="shipink-carrier"><?php echo esc_html($carrier); ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="shipink-sidebar">
        <div class="shipink-card shipink-cta-card">
            <h2>
                <?php echo $connection_status ? esc_html__('Dashboard Access', 'shipink') : esc_html__('Get Started', 'shipink'); ?>
            </h2>

            <p>
                <?php if ($connection_status): ?>
                    <?php esc_html_e('Access your Shipink dashboard to manage shipments, track orders, and configure shipping settings.', 'shipink'); ?>
                <?php else: ?>
                    <?php esc_html_e('Connect your WooCommerce store to Shipink and start shipping with multiple carriers in minutes. Pay as you receive orders!', 'shipink'); ?>
                <?php endif; ?>
            </p>

            <a href="<?php echo esc_url($target_url); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($button_text); ?>
            </a>

            <?php if ($connection_status): ?>
                <a href="<?php echo esc_url($signup_url); ?>" class="button" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Reconnect Shipink', 'shipink'); ?>
                </a>
            <?php endif; ?>

            <?php if (!$connection_status): ?>
                <ul class="shipink-benefits">
                    <li><?php esc_html_e('Pay as you receive orders', 'shipink'); ?></li>
                    <li><?php esc_html_e('No credit card required', 'shipink'); ?></li>
                    <li><?php esc_html_e('Up to 60% shipping discounts', 'shipink'); ?></li>
                    <li><?php esc_html_e('98% customer satisfaction', 'shipink'); ?></li>
                    <li><?php esc_html_e('Easy WooCommerce integration', 'shipink'); ?></li>
                </ul>
            <?php endif; ?>
        </div>

        <div class="shipink-card shipink-status-card">
            <h2>
                <?php esc_html_e('System Status', 'shipink'); ?>
                <?php if ($issues_count > 0): ?>
                    <span class="shipink-issue-count">
                        <?php echo esc_html(sprintf(_n('%d issue', '%d issues', $issues_count, 'shipink'), $issues_count)); ?>
                    </span>
                <?php endif; ?>
            </h2>

            <ul class="shipink-status-list">
                <?php foreach ($checks as $check): ?>
                    <li class="shipink-status-item <?php echo $check['ok'] ? 'is-ok' : 'is-warn'; ?>">
                        <span class="dashicons dashicons-<?php echo $check['ok'] ? 'yes-alt' : 'warning'; ?>" aria-hidden="true"></span>
                        <div class="shipink-status-text">
                            <div class="shipink-status-row">
                                <strong><?php echo esc_html($check['label']); ?></strong>
                                <span><?php echo esc_html($check['value']); ?></span>
                            </div>
                            <?php if (!empty($check['note'])): ?>
                                <em><?php echo esc_html($check['note']); ?></em>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        </div>
    </div>
</div>
