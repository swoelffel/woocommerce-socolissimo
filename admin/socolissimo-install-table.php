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
		CREATE TABLE IF NOT EXISTS {$wpdb->prefix}socolissimo_checkout_shipping (
			id bigint(20) NOT NULL auto_increment,
			civility varchar(4) NULL,
			first_name varchar(30) NULL,
			last_name varchar(30) NULL,
			company_name varchar(255) NULL,
			infos_appart varchar(255) NULL,
			infos_bat varchar(255) NULL,
			adresse_1 varchar(255) NULL,
			adresse_2 varchar(255) NULL,
			postcode varchar(20) NULL,
			city varchar(255) NULL,
			country varchar(255) NULL,
			mail varchar(255) NULL,
			phone_number varchar(255) NULL,
            delivery_info varchar(255) NULL,
            door_code_1 varchar(30) NULL,
            door_code_2 varchar(30) NULL,
			PRIMARY KEY  (id)
		)
	";
	dbDelta($socolissimo_checkout_infos);
}

function uninstall_table() {
    global $wpdb;
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
	$socolissimo_checkout_infos = "DROP TABLE IF EXISTS {$wpdb->prefix}socolissimo_checkout_shipping";
	dbDelta($socolissimo_checkout_infos);
}