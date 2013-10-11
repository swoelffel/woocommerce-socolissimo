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

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Install table.
 *
 * @access public
 * @return void
 */
function install_table() {
    global $wpdb;
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
	$socolissimo_checkout_infos = "
		CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_socolissimo (
			`id` int(11) NOT NULL AUTO_INCREMENT,
            `id_panier` int(11) NOT NULL,
            `delivery_mode` varchar(63) DEFAULT NULL,
            `ce_civility` varchar(8) DEFAULT NULL,
            `ce_name` varchar(255) DEFAULT NULL,
            `ce_first_name` varchar(255) DEFAULT NULL,
            `ce_company_name` varchar(127) DEFAULT NULL,
            `ce_phone_number` varchar(15) DEFAULT NULL,
            `ce_delivery_information` varchar(127) DEFAULT NULL,
            `ce_door_code1` varchar(8) DEFAULT NULL,
            `ce_door_code2` varchar(8) DEFAULT NULL,
            `ce_entry_phone` varchar(15) DEFAULT NULL,
            `ce_email` varchar(127) DEFAULT NULL,
            `ce_adress1` varchar(127) DEFAULT NULL,
            `ce_adress2` varchar(127) DEFAULT NULL,
            `ce_adress3` varchar(127) DEFAULT NULL,
            `ce_adress4` varchar(127) DEFAULT NULL,
            `ce_zip_code` varchar(15) DEFAULT NULL,
            `ce_town` varchar(127) DEFAULT NULL,
            `tr_param_plus` varchar(127) DEFAULT NULL,
            `pr_name` varchar(255) DEFAULT NULL,
            `pr_id` varchar(63) DEFAULT NULL,
            `pr_adress1` varchar(255) DEFAULT NULL,
            `pr_town` varchar(255) DEFAULT NULL,
            `pr_zip_code` varchar(15) DEFAULT NULL,
            `dy_preparationtime` varchar(2) DEFAULT NULL,
            `dy_forwarding_charges` varchar(8) DEFAULT NULL,
            `error_code` varchar(8) DEFAULT NULL,
            `tr_order_number` varchar(31) DEFAULT NULL,
            `order_id` varchar(31) DEFAULT NULL,
            `trader_company_name` varchar(127) DEFAULT NULL,
            `id_user` int(11) DEFAULT NULL,
            `date_creation` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
	";
	dbDelta($socolissimo_checkout_infos);
}

function uninstall_table() {
    global $wpdb;
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
	$socolissimo_checkout_infos = "DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_socolissimo";
	dbDelta($socolissimo_checkout_infos);
}