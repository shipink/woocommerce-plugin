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

$logo_url = plugins_url('admin/images/shipink-logo.png', SHIPINK_PLUGIN_FILE);
?>

<div class="wrap shipink-wrap">
    <h1 class="wp-heading-inline">
        <img src="<?php echo esc_url($logo_url); ?>" alt="" class="shipink-heading-logo" />
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
