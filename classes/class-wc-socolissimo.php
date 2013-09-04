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
add_action('plugins_loaded', 'woocommerce_socolissimo_init', 0);

function woocommerce_socolissimo_init() {
    if (!class_exists('WC_Shipping_Method')) {
        return;
    };

    DEFINE('PLUGIN_DIR', plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__)) . '/');

    /*
     * Inspire Commerce Gateway Class
     */

    class WC_SoColissimo extends WC_Shipping_Method {

        function __construct() {
            $this->id = 'socolissimo';
            $this->method_title = __('So Colissimo', 'woocommerce');
            // Load the form fields
            $this->init_form_fields();
            // Load the settings.
            $this->init_settings();
            // Get setting values
            foreach ($this->settings as $key => $val)
                $this->$key = $val;
            // Logs
            if ($this->debug == 'yes')
                $this->log = $woocommerce->logger();

            // Ajout des Hooks
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_flat_rates'));
            add_action('init', array(&$this, 'check_response'));
        }

        function calculate_shipping() {
            $active = true;
            //TMP Bug v2 sauvegarde des params -> je force
            $this->socolissimo_test_url = 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp';
            error_log('socolissimo_test_url=' . $this->socolissimo_test_url);
            if (preg_match('/http/', $this->socolissimo_test_url) === 1) { // On execute le test
                $cache = file_get_contents($this->socolissimo_test_url); // 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp'
                error_log('cache = ' . $cache);
                // Si le service ne retourne pas OK on n'active pas le mode de livraison
                if (preg_match('/\[OK\]/', $cache) === 0) {
                    error_log('On desactive');
                    $active = false;
                } else {
                    error_log('Calculate activé');
                }
            }
            if ($active) {
                error_log('calc');
                $rate = array(
                    'id' => $this->id,
                    'label' => $this->title,
                    //'cost' => $shipping_total + $cost_per_order, // TODO
                    'cost' => 20
                );
                $this->add_rate($rate);
            }
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         *
         * @since 1.0.0
         * @access public
         * @return void
         */
        public function admin_options() {
            ?>
            <h3><?php __('So Colissimo'); ?></h3>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table><!--/.form-table-->
            <?php
        }

        /**
         * Reponse Paybox (Pour le serveur Paybox)
         *
         * @access public
         * @return void
         */
        function check_response() {
            error_log('CR CS');
            $order = new WC_Order((int) $_GET['order']); // On récupère la commande
            if ($order) {
                
            } else {
                header('HTTP/1.1 200 OK');
                wp_die('KO Unknown order : ' . $_GET['order']);
            }
        }

        /*
         * Initialize Gateway Settings Form Fields.
         */

        function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Enable SoColissimo Shipping', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('So Colissimo', 'woocommerce')
                ),
                'socolissimo_PudoFOID' => array(
                    'title' => __('So Colissimo Merchant ID', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter you Font Office ID provided by SoColissimo.', 'woocommerce'),
                    'default' => ''
                ),
                'socolissimo_merchantKey' => array(
                    'title' => __('So Colissimo Encryption SHA Merchant Key', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter you SHA Key provided by SoColissimo.', 'woocommerce'),
                    'default' => ''
                ),
                'socolissimo_PreparationTime' => array(
                    'title' => __('Preparation delay', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter the delay took to prepare order.', 'woocommerce'),
                    'default' => ''
                ),
                'socolissimo_testing_url' => array(
                    'title' => __('WorPress Testing URL', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter the testing URL provided by SoColissimo to check service availabitity.', 'woocommerce'),
                    'default' => 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp'
                ),
                'socolissimo_post_url' => array(
                    'title' => __('SoColissimo WS URL', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter the So Colissimo WebService URL.', 'woocommerce'),
                    'default' => 'http://ws.colissimo.fr/pudo-fo-frame/storeCall.do'
                ),
                'socolissimo_url_socolissimo' => array(
                    'title' => __('SoColissimo iframe Page', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please provide the URL for the page handling SoColissimo iframe. This page <b>must include</b> the [ob-iframe-socolissimo] shortcode', 'woocommerce'),
                    'default' => ''
                ),
                'socolissimo_url_ok' => array(
                    'title' => __('So Colissimo return URL OK', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter return URL which correspond to the [woocommerce_pay] page.', 'woocommerce'),
                    'default' => ''
                ),
                'socolissimo_url_ko' => array(
                    'title' => __('So Colissimo return URL KO', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Please enter return URL in case of error.', 'woocommerce'),
                    'default' => ''
                )
            );
        }

        static function getRealIpAddr() {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            return $ip;
        }

    }

    // Fin de la classe

    /*
     * Ajout de la "gateway" SoColissimo à woocommerce
     */

    function add_socolissimo_delivery_method($methods) {
        $methods[] = 'WC_SoColissimo';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_socolissimo_delivery_method');
}

add_action('woocommerce_checkout_init', 'woocommerce_socolissimo_redirect');

function woocommerce_socolissimo_redirect() {
    global $woocommerce;
    if (session_id() == '')
        session_start();
    if (($woocommerce->session->chosen_shipping_method == 'socolissimo')) { // On redirige vers la page adhoc
        //error_log('SWO2808-3|' . $_SERVER['REQUEST_URI'] . '|' . print_r($woocommerce->session->chosen_shipping_method, true));
        $my_WC_SoColissimo = new WC_SoColissimo();
        error_log('Session' . $wp_session['woocommerce_socolissimo_signature']);
        if ((get_permalink($post->ID) != $my_WC_SoColissimo->socolissimo_url_socolissimo) && (empty($_SESSION['woocommerce_socolissimo_signature']))) { // sauf si on est déjà dessus ou passé dessus
            error_log('Redirect vers Page SOCO');
            wp_redirect($my_WC_SoColissimo->socolissimo_url_socolissimo, '302');
            exit;
        } else {
            error_log('Pas de redirect');
        }
    }
}

add_action('init', 'woocommerce_socolissimo_check_response');

function woocommerce_socolissimo_check_response() { // On traite le retour SOCO
    global $woocommerce;
    if (session_id() == '')
        session_start();
    if (isset($_GET['TRRETURNURLKO']) && (isset($_POST['SIGNATURE']))) {
        error_log('Retour SOCO ' . $_POST['SIGNATURE']);
        error_log('Retour : Session WC : ' . $_SESSION['woocommerce_socolissimo_signature']);
        // On verifie la signature
        // A revoir : le test de la signature
        if (($_POST['SIGNATURE'] == $_SESSION['woocommerce_socolissimo_signature']) || true) { 
            $checkout = $woocommerce->checkout();
            // On traite le retour
            if (isset($_POST['CEZIPCODE']))
                $checkout->set_shipping_postcode($_POST['CEZIPCODE']);
            if (isset($_POST['CEADRESS3']))
                $woocommerce->customer->set_shipping_address($_POST['CEADRESS3']);
            if (isset($_POST['CEADRESS1']))
                $woocommerce->customer->set_shipping_address_2($_POST['CEADRESS1']);
            if (isset($_POST['CEPAYS']))
                $woocommerce->customer->set_shipping_country($_POST['CEPAYS']);
            if (isset($_POST['CETOWN']))
                $woocommerce->customer->set_shipping_city($_POST['CETOWN']);
            $woocommerce->cart->calculate_totals();
            /*
             * A completer ...
                'email' => $checkout->get_value('billing_email'),
                'first_name' => $checkout->get_value('shipping_first_name'),
                'last_name' => $checkout->get_value('shipping_last_name'),
                'company' => $checkout->get_value('shipping_company'),
                'address_1' => $checkout->get_value('shipping_address_1'),
                'address_2' => $checkout->get_value('shipping_address_2'),
                'city' => $checkout->get_value('shipping_city'),
                'state' => $checkout->get_value('shipping_state'),
                'postcode' => $checkout->get_value('shipping_postcode'),
                'country' => $checkout->get_value('shipping_country'),
             */
            //$_SESSION['woocommerce_socolissimo_signature'] = '';
            $my_WC_SoColissimo = new WC_SoColissimo();
            error_log('Redirect Paiement : '.$my_WC_SoColissimo->socolissimo_url_ok);
            wp_redirect($my_WC_SoColissimo->socolissimo_url_ok, '302');
        }
    }
}