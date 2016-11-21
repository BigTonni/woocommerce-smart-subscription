<?php
/**
 * Plugin Name: WooCommerce Smart Subscription
 * Plugin URI: https://github.com/BigTonni
 * Description: To create a subscription during checkout.
 * Author: Anton Shulga
 * Author URI: https://github.com/BigTonni
 * Version: 1.0
 * Text Domain: woocommerce-smart-subscription
 * Domain Path: /i18n/languages
 */
if (!defined('ABSPATH')){
    exit; // Exit if accessed directly
}

if (!function_exists('woothemes_queue_update')) {
    require_once( 'woo-includes/woo-functions.php' );
}

// notify user if Localmetrix plugin is inactive
add_action('admin_notices', 'wss_inactive_notice');
function wss_inactive_notice(){
    // WC active check
    if (!is_woocommerce_active() || get_option('woocommerce_subscriptions_is_active', false) == false) {
        if (current_user_can('activate_plugins')) {
            ?>
            <div id="message" class="error">
                <p><?php
                    printf(esc_html__('%1$sWooCommerce Smart Subscription is inactive.%2$s The %3$sWooCommerce%4$s and %5$sWooCommerce Subscriptions%6$s plugins must be active for WooCommerce Smart Subscription to work. Please install & activate WooCommerce &raquo;', 'wss'), '<strong>', '</strong>', '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="http://www.woothemes.com/products/woocommerce-subscriptions/">', '</a>');
                    ?>
                </p>
            </div>
            <style>#message.updated.notice.is-dismissible{display: none;}</style>
            <?php
        }
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
}

register_uninstall_hook(__FILE__, array('WC_Smart_Subscription', 'uninstall'));

class WC_Smart_Subscription {

    /** plugin version number */
    public $version = '1.0';

    /**
     * The single instance of the class.
     */
    protected static $_instance = null;

    /**
     * Main Instance.
     *
     * @since 1.0
     * @static
     * @return Main instance.
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /* Set the constants needed by the plugin. */

    private function define_constants() {
        $this->define('WSS_PLUGIN_FILE', __FILE__);
        $this->define('WSS_PLUGIN_BASENAME', plugin_basename(__FILE__));
        $this->define('WSS_VERSION', $this->version);
        $this->define('WSS_TEXT_DOMAIN', 'woocommerce-smart-subscription');
    }

    /**
     * Define constant if not already set.
     *
     * @param  string $name
     * @param  string|bool $value
     */
    private function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        include_once( 'includes/wss-helpers.php' );
        include_once( 'includes/class-wss-assets.php' );
        include_once( 'includes/class-wss-checkout.php' );
        include_once( 'includes/wss-ajax.php' );
    }

    /**
     * Hook into actions and filters.
     * @since  2.3
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        add_action('init', array($this, 'init'), 0);
        
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }

    /**
     * Init WSS when WordPress Initialises.
     */
    public function init() {
        // Set up localisation.
        $this->load_plugin_textdomain();
        
    }

    /**
     * Load the translation of the plugin.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain('woocommerce-smart-subscription', false, plugin_basename(dirname(__FILE__)) . '/i18n/languages');
    }
  
    /**
     * Called when the plugin is deactivated.
     *
     * @since 1.0
     */
    public function deactivate() {
        flush_rewrite_rules();
    }

    public function plugin_url() {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Get the plugin path.
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Do things on plugin activation.
     */
    public function activate() {
        return true;
    }

    /**
     * Do things on plugin uninstall.
     */
    public function uninstall() {
        if (!current_user_can('activate_plugins')){
            return;
        }
        check_admin_referer('bulk-plugins');

        if (__FILE__ != WP_UNINSTALL_PLUGIN){
            return;
        }
    }
} // end WC_Smart_Subscription class

/**
 * Main instance of WSS.
 *
 * @since  1.0
 * @return WSS
 */
function WSS() {
	return WC_Smart_Subscription::instance();
}
$wss_plugin = WSS();
