<?php

/**
 * Plugin Name: WooCommerce SoColissimo Shipping Gateway
 * Plugin URI: http://www.castelis.com/woocommerce/
 * Description: Gateway e-commerce pour SoColissimo.
 * Version: 0.1.0
 * Author: Castelis
 * Author URI: http://www.castelis.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @package WordPress
 * @author Castelis
 * @since 0.1.0
 */
class OS_socolissimo_wrapper {

    /**
     * On charge le tout
     */
    function OS_socolissimo_main() {
        /*
          add_action('wp_ajax_woocommerce_update_shipping_method', array($this, 'generate_sc_url'));
          add_action('wp_ajax_nopriv_woocommerce_update_shipping_method', array($this, 'generate_sc_url'));
          add_action('woocommerce_check_cart_items', array($this, 'generate_sc_url'));
         */
        add_action('woocommerce_after_checkout_validation', array($this, 'generate_sc_url'));
        add_action('plugins_loaded', array($this, 'init'), 8);
    }

    /**
     * Point d'entrée IM4WP
     */
    function init() {
        // Variable du chemin de 'application
        define('OS_socolissimo_FILE_PATH', plugin_dir_path(__FILE__));
        define('OS_socolissimo_ROOT_URL', plugins_url('', __FILE__));
        // Initialize
        require_once(OS_socolissimo_FILE_PATH . '/classes/class-wc-socolissimo.php');
        woocommerce_socolissimo_init();
    }

// Ajout du ShortCode pour la page iframe

    /**
     * IM4WP Hook d'activation
     */
    function install() {
        global $wp_version;
        if ((float) $wp_version < 3.5) {
            deactivate_plugins(plugin_basename(__FILE__)); // Deactivate ourselves
            wp_die(__('You must run at least WP verion 3.0 to install Castelis SoCollissimo plugin'), __('WP not compatible'), array('back_link' => true));
            return;
        }
        define('OS_socolissimo_FILE_PATH', dirname(__FILE__));
	
        // Installation ici
	include_once('admin/socolissimo-install-table.php');
	install_table();
	
        error_log('install');
    }

    /**
     * IM4WP Hook de désactivation
     */
    public function deactivate() {
        // Desinstallation ici
	include_once('admin/socolissimo-install-table.php');
	uninstall_table();
	
        error_log('uninstall');
    }

}

$im4wp = new OS_socolissimo_wrapper();

// Activation / Désactivation
register_activation_hook(__FILE__, array($im4wp, 'install'));
register_deactivation_hook(__FILE__, array($im4wp, 'deactivate'));

// Let's Go !!
$im4wp->OS_socolissimo_main();

include_once('shortcode-os_iframe_socolissimo.php');
add_shortcode('ob-iframe-socolissimo', 'get_os_iframe_socolissimo');
?>