<?php
/**
 * iframe SoColissimo Shortcode
 *
 * A include dans la page montrant l'iframe socolissimo.
 *
 * @author 	Castelis (SWO)
 * @category 	Shortcodes
 * @package 	Wordpress
 * @version     0.1.0
 */

/**
 * Récupère le shortcode.
 *
 * @access public
 * @param array $atts
 * @return string
 */
function get_os_iframe_socolissimo( $atts ) {
	global $woocommerce;
	return $woocommerce->shortcode_wrapper('os_iframe_socolissimo', $atts);
}


/**
 * Affiche la page
 *
 * @access public
 * @param mixed $atts
 * @return void
 */
function os_iframe_socolissimo( $atts ) {
	global $woocommerce;
        global $wp_session;
        
	$woocommerce->nocache();
	$woocommerce->show_messages();
	include (OS_socolissimo_FILE_PATH.'templates/socolissimo.php');
}