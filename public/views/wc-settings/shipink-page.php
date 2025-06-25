<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user = wp_get_current_user();

$params['source'] = 'woocommerce';
$params['email'] = $user->user_email;
$params['first_name'] = !empty($user->first_name) ? $user->first_name : $user->user_nicename;
$params['last_name'] = !empty($user->last_name) ? $user->last_name : '';
$params['mobile_phone'] = !empty($user->billing_phone) ? $user->billing_phone : '';
$params['platform_store_id'] = get_current_network_id();
$params['name'] = get_option('blogname');
$params['url'] = get_option('home');
$connectQuery = esc_html(http_build_query($params));

// Determine button text and URL based on connection status
$button_text = isset($is_connected) && $is_connected 
    ? __('Open Shipink Dashboard', 'shipink')
    : __('Connect to Shipink', 'shipink');

$target_url = isset($is_connected) && $is_connected
    ? 'https://app.shipink.io'
    : 'https://app.shipink.io/signup?' . $connectQuery;

$connection_status = isset($is_connected) && $is_connected;
?>

<style>
.shipink-settings-page {
    max-width: 1200px;
    margin: 20px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.shipink-header {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 30px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.shipink-logo-section {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.shipink-logo {
    width: 60px;
    height: 60px;
    background: #fff;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    overflow: hidden;
}

.shipink-logo img {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.shipink-title-section h1 {
    margin: 0 0 5px 0;
    font-size: 28px;
    font-weight: 600;
    color: #1d2327;
}

.shipink-subtitle {
    color: #646970;
    font-size: 16px;
    margin: 0;
}

.shipink-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 10px;
}

.status-connected {
    background: #d1e7dd;
    color: #0f5132;
}

.status-disconnected {
    background: #f8d7da;
    color: #721c24;
}

.shipink-main-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.shipink-features {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 30px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.shipink-connect-panel {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 30px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    height: fit-content;
}

.features-title {
    font-size: 20px;
    font-weight: 600;
    color: #1d2327;
    margin: 0 0 20px 0;
}

.feature-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.feature-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 18px;
    flex-shrink: 0;
}

.icon-shipping { background: #e3f2fd; color: #1976d2; }
.icon-tracking { background: #f3e5f5; color: #7b1fa2; }
.icon-rates { background: #e8f5e8; color: #388e3c; }
.icon-labels { background: #fff3e0; color: #f57c00; }
.icon-returns { background: #fce4ec; color: #c2185b; }
.icon-analytics { background: #e0f2f1; color: #00796b; }

.feature-content h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.feature-content p {
    margin: 0;
    font-size: 13px;
    color: #646970;
    line-height: 1.4;
}

.carriers-section {
    margin-top: 30px;
}

.carriers-title {
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
    margin: 0 0 15px 0;
}

.carriers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
    gap: 8px;
}

.carrier-item {
    padding: 8px 6px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    text-align: center;
    font-size: 11px;
    font-weight: 500;
    color: #646970;
    background: #fafafa;
    line-height: 1.2;
}

.connect-panel-title {
    font-size: 18px;
    font-weight: 600;
    color: #1d2327;
    margin: 0 0 15px 0;
}

.connect-description {
    color: #646970;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 25px;
}

.connect-button {
    width: 100%;
    padding: 12px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
    box-sizing: border-box;
    word-wrap: break-word;
    white-space: normal;
}

.connect-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    color: white;
    text-decoration: none;
}

.connect-button.connected {
    background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
    box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
}

.connect-button.connected:hover {
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 20px 0 0 0;
}

.benefits-list li {
    padding: 8px 0;
    color: #646970;
    font-size: 14px;
    position: relative;
    padding-left: 25px;
}

.benefits-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #4caf50;
    font-weight: bold;
}

@media (max-width: 768px) {
    .shipink-main-content {
        grid-template-columns: 1fr;
    }
    
    .feature-grid {
        grid-template-columns: 1fr;
    }
    
    .carriers-grid {
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
    }
    
    .carrier-item {
        font-size: 10px;
        padding: 6px 4px;
    }
}
</style>

<div class="shipink-settings-page">
    <div class="shipink-header">
        <div class="shipink-logo-section">
            <div class="shipink-logo">
                <img src="https://shipink.io/favicon.ico" alt="Shipink Logo" />
            </div>
            <div class="shipink-title-section">
                <h1><?php _e('Shipink Multi-Carrier Shipping', 'shipink'); ?></h1>
                <p class="shipink-subtitle"><?php _e('Complete shipping solution for e-commerce', 'shipink'); ?></p>
                <div class="shipink-status <?php echo $connection_status ? 'status-connected' : 'status-disconnected'; ?>">
                    <?php echo $connection_status ? __('Connected', 'shipink') : __('Not Connected', 'shipink'); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="shipink-main-content">
        <div class="shipink-features">
            <h2 class="features-title"><?php _e('Complete Shipping Solution', 'shipink'); ?></h2>
            
            <div class="feature-grid">
                <div class="feature-item">
                    <div class="feature-icon icon-shipping">🚚</div>
                    <div class="feature-content">
                        <h4><?php _e('Order Management', 'shipink'); ?></h4>
                        <p><?php _e('Ship e-commerce orders easily from one place to shorten fulfillment time', 'shipink'); ?></p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon icon-labels">📦</div>
                    <div class="feature-content">
                        <h4><?php _e('All Labels in One Place', 'shipink'); ?></h4>
                        <p><?php _e('Manage shipping labels for various global carriers like Aras, MNG, UPS, Aramex, or FedEx with just a few clicks', 'shipink'); ?></p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon icon-rates">💰</div>
                    <div class="feature-content">
                        <h4><?php _e('Best Shipping Rates', 'shipink'); ?></h4>
                        <p><?php _e('Find the cheapest or fastest shipping company for your shipment with up to 60% discounts', 'shipink'); ?></p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon icon-tracking">📍</div>
                    <div class="feature-content">
                        <h4><?php _e('Advanced Tracking', 'shipink'); ?></h4>
                        <p><?php _e('Track all packages in one place with branded tracking pages and automatic notifications', 'shipink'); ?></p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon icon-returns">🌍</div>
                    <div class="feature-content">
                        <h4><?php _e('International Shipping', 'shipink'); ?></h4>
                        <p><?php _e('Save cost and time on international shipping with many carriers worldwide', 'shipink'); ?></p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon icon-analytics">🔗</div>
                    <div class="feature-content">
                        <h4><?php _e('Multi-carrier API', 'shipink'); ?></h4>
                        <p><?php _e('Shipping APIs designed for developers to integrate carriers worldwide', 'shipink'); ?></p>
                    </div>
                </div>
            </div>

            <div class="carriers-section">
                <h3 class="carriers-title"><?php _e('Supported Carriers', 'shipink'); ?></h3>
                <div class="carriers-grid">
                    <div class="carrier-item">MNG Kargo</div>
                    <div class="carrier-item">Aramex</div>
                    <div class="carrier-item">Yurtiçi Kargo</div>
                    <div class="carrier-item">Sendeo</div>
                    <div class="carrier-item">Aras Kargo</div>
                    <div class="carrier-item">Kolay Gelsin</div>
                    <div class="carrier-item">HepsiJET</div>
                    <div class="carrier-item">UPS</div>
                    <div class="carrier-item">FedEx</div>
                    <div class="carrier-item">PTT</div>
                    <div class="carrier-item">Octovan Express</div>
                    <div class="carrier-item">Sürat Kargo</div>
                    <div class="carrier-item">KargoTurk</div>
                    <div class="carrier-item">Horoz Lojistik</div>
                    <div class="carrier-item">Banabikurye</div>
                </div>
            </div>
        </div>

        <div class="shipink-connect-panel">
            <h3 class="connect-panel-title">
                <?php echo $connection_status ? __('Dashboard Access', 'shipink') : __('Get Started', 'shipink'); ?>
            </h3>
            
            <p class="connect-description">
                <?php if ($connection_status): ?>
                    <?php _e('Access your Shipink dashboard to manage shipments, track orders, and configure shipping settings.', 'shipink'); ?>
                <?php else: ?>
                    <?php _e('Connect your WooCommerce store to Shipink and start shipping with multiple carriers in minutes. Pay as you receive orders!', 'shipink'); ?>
                <?php endif; ?>
            </p>

            <a href="<?php echo esc_url($target_url); ?>" 
               class="connect-button <?php echo $connection_status ? 'connected' : ''; ?>" 
               target="_blank">
                <?php echo esc_html($button_text); ?>
            </a>

            <?php if (!$connection_status): ?>
            <ul class="benefits-list">
                <li><?php _e('Pay as you receive orders', 'shipink'); ?></li>
                <li><?php _e('No credit card required', 'shipink'); ?></li>
                <li><?php _e('Up to 60% shipping discounts', 'shipink'); ?></li>
                <li><?php _e('98% customer satisfaction', 'shipink'); ?></li>
                <li><?php _e('Easy WooCommerce integration', 'shipink'); ?></li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function ($) {
    // Add any additional JavaScript functionality here
    $('.connect-button').on('click', function(e) {
        // Track connection attempt if needed
        if (typeof gtag !== 'undefined') {
            gtag('event', 'shipink_connect_click', {
                'event_category': 'shipping',
                'event_label': 'connect_attempt'
            });
        }
    });
});
</script>
