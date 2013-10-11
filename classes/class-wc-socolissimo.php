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
            if (preg_match('/http/', $this->socolissimo_test_url) === 1) { // On execute le test
                $cache = file_get_contents($this->socolissimo_test_url); // 'http://ws.colissimo.fr/supervision-pudo/supervision.jsp'
                error_log('cache = ' . $cache);
                // Si le service ne retourne pas OK on n'active pas le mode de livraison
                if (preg_match('/\[OK\]/', $cache) === 0) {
                    error_log('On desactive');
                    $active = false;
                } else {
                    error_log('Calculate activÃ©');
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
     * Ajout de la "gateway" SoColissimo Ã  woocommerce
     */

    function add_socolissimo_delivery_method($methods) {
        $methods[] = 'WC_SoColissimo';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'add_socolissimo_delivery_method');
}

add_action('wp_enqueue_scripts', 'woocommerce_socolissimo_checkout_controller');

function woocommerce_socolissimo_checkout_controller() {
    global $woocommerce;
    error_log('Shipping Method '.$woocommerce->session->chosen_shipping_method);
    if ($woocommerce->session->chosen_shipping_method == 'socolissimo') {
        $my_WC_SoColissimo = new WC_SoColissimo();
        error_log(get_permalink($post->ID) . ' ?= ' . $my_WC_SoColissimo->socolissimo_url_ok);
        if (get_permalink($post->ID) == $my_WC_SoColissimo->socolissimo_url_ok) { // On est sur la page de paiement
            //error_log('SWO2808-3|' . $_SERVER['REQUEST_URI'] . '|' . print_r($woocommerce->session->chosen_shipping_method, true));
            if ((get_permalink($post->ID) != $my_WC_SoColissimo->socolissimo_url_socolissimo) && ($_GET['SOCO'] != 'ok')) { // sauf si on est dÃ©jÃ  dessus ou passÃ© dessus
                error_log('Redirect vers Page SOCO Get de SOCO=' . $_GET['SOCO']);
                error_log('PM=' . get_permalink($post->ID));
                wp_redirect($my_WC_SoColissimo->socolissimo_url_socolissimo, '302');
                exit;
            } else {
                error_log('Pas de redirect');
                wp_enqueue_script( 'socolissimo_custom_script', OS_socolissimo_ROOT_URL.'/assets/js/socolissimo.js', array('jquery'), '0.3' );
            }
        }
    }
}

add_action('init', 'woocommerce_socolissimo_check_response');
function woocommerce_socolissimo_check_response() { 
	// On traite le retour SOCO
    //error_log('Retour SOCO :: ' . $_SERVER['REQUEST_URI']);
    /*global $woocommerce;
    if (session_id() == '')
        session_start();
    if (isset($_GET['TRRETURNURLKO']) && (isset($_POST['SIGNATURE']))) {
        // On verifie la signature
        // A revoir : le test de la signature
        if (($_POST['SIGNATURE'] == $_SESSION['woocommerce_socolissimo_signature']) || true) {
            $woocommerce->customer->set_country('FR');
            $woocommerce->customer->set_shipping_country('FR');
            // On traite le retour
            if (isset($_POST['CEZIPCODE'])) {
                $woocommerce->customer->set_shipping_postcode($_POST['CEZIPCODE']);
                $woocommerce->customer->set_postcode($_POST['CEZIPCODE']);
            }
            if (isset($_POST['CEADRESS3'])) {
                $woocommerce->customer->set_shipping_address($_POST['CEADRESS3']);
                $woocommerce->customer->set_address($_POST['CEADRESS3']);
            }
            if (isset($_POST['CEADRESS1'])) {
                $woocommerce->customer->set_shipping_address_2($_POST['CEADRESS1']);
                $woocommerce->customer->set_address_2($_POST['CEADRESS1']);
            }
            $woocommerce->customer->set_shipping_country('FR');
            $woocommerce->customer->set_country('FR');
            if (isset($_POST['CETOWN'])) {
                error_log('CETOWN'.$_POST['CETOWN']);
                $woocommerce->customer->set_shipping_city($_POST['CETOWN']);
                $woocommerce->customer->set_city($_POST['CETOWN']);
            }*/
            
            
            
            //wp_update_user( array ( 'first_name' => 'StÃ©phane', 'last_name' => 'WOELFFEL' ) ) ;
            
            /* if (isset($_POST['CEEMAIL'])) {
              $woocommerce->customer->set_email($_POST['CEEMAIL']);
              } */
            //$woocommerce->customer->save_data();
            //error_log('Custo : '.print_r($GLOBALS['woocommerce']->session->customer, true));
            //$woocommerce->cart->calculate_totals();
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
            
            global $wpdb;
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            
            // si l'utilisateur est connecté, on récupère ses infos
            $idUser = null;
            if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
                $idUser = $current_user->ID;
            }
                
            
            // TODO : controle des données
            error_log(print_r($_POST, true));
            if (isset($_GET['TRRETURNURLKO']) && (isset($_POST['SIGNATURE']))) {
                
                // on récupère la signature
                $signature = (!empty($_POST['SIGNATURE'])) ? ($_POST['SIGNATURE']) : '';
                // on vérifie la signature => a faire
                //
                
                $checkout = array(
                    // 'id_panier' => '', ID panier à définir
                    'delivery_mode' => (!empty($_POST['DELIVERYMODE'])) ? ($_POST['DELIVERYMODE']) : '',
                    'ce_civility' => (!empty($_POST['CECIVILITY'])) ? ($_POST['CECIVILITY']) : '',
                    'ce_name' => (!empty($_POST['CENAME'])) ? ($_POST['CENAME']) : '',
                    'ce_first_name' => (!empty($_POST['CEFIRSTNAME'])) ? ($_POST['CEFIRSTNAME']) : '',
                    'ce_company_name' => (!empty($_POST['CECOMPANYNAME'])) ? ($_POST['CECOMPANYNAME']) : '',
                    'ce_phone_number' => (!empty($_POST['CEPHONENUMBER'])) ? ($_POST['CEPHONENUMBER']) : '',
                    'ce_delivery_information' => (!empty($_POST['CEDELIVERYINFORMATION'])) ? ($_POST['CEDELIVERYINFORMATION']) : '',
                    'ce_door_code1' => (!empty($_POST['CEDOORCODE1'])) ? ($_POST['CEDOORCODE1']) : '',
                    'ce_door_code2' => (!empty($_POST['CEDOORCODE2'])) ? ($_POST['CEDOORCODE2']) : '',
                    'ce_entry_phone' => (!empty($_POST['CEENTRYPHONE'])) ? ($_POST['CEENTRYPHONE']) : '', // Aucune info
                    'ce_email' => (!empty($_POST['CEEMAIL'])) ? ($_POST['CEEMAIL']) : '',
                    'ce_adress1' => (!empty($_POST['CEADRESS1'])) ? ($_POST['CEADRESS1']) : '',
                    'ce_adress2' => (!empty($_POST['CEADRESS2'])) ? ($_POST['CEADRESS2']) : '',
                    'ce_adress3' => (!empty($_POST['CEADRESS3'])) ? ($_POST['CEADRESS3']) : '',
                    'ce_adress4' => (!empty($_POST['CEADRESS4'])) ? ($_POST['CEADRESS4']) : '',
                    'ce_zip_code' => (!empty($_POST['CEZIPCODE'])) ? ($_POST['CEZIPCODE']) : '',
                    'ce_town' => (!empty($_POST['CETOWN'])) ? ($_POST['CETOWN']) : '',
                    
                    'pr_name' => (!empty($_POST['PRNAME'])) ? ($_POST['PRNAME']) : '',
                    'pr_id' => (!empty($_POST['PRID'])) ? ($_POST['PRID']) : '',
                    'pr_adress1' => (!empty($_POST['PRADRESS1'])) ? ($_POST['PRADRESS1']) : '',
                    'pr_town' => (!empty($_POST['PRTOWN'])) ? ($_POST['PRTOWN']) : '',
                    'pr_zip_code' => (!empty($_POST['PRZIPCODE'])) ? ($_POST['PRZIPCODE']) : '',
                    
                    'dy_preparationtime' => (!empty($_POST['DYPREPARATIONTIME'])) ? ($_POST['DYPREPARATIONTIME']) : '',
                    'dy_forwarding_charges' => (!empty($_POST['DYFORWARDINGCHARGES'])) ? ($_POST['DYFORWARDINGCHARGES']) : '',
                    
                    'order_id' => (!empty($_POST['ORDERID'])) ? ($_POST['ORDERID']) : '',
                    'tr_order_number' => (!empty($_POST['TRORDERNUMBER'])) ? ($_POST['TRORDERNUMBER']) : '',
                    'trader_company_name' => (!empty($_POST['TRADERCOMPANYNAME'])) ? ($_POST['TRADERCOMPANYNAME']) : '',
                    
                    'error_code' => (!empty($_POST['ERRORCODE'])) ? ($_POST['ERRORCODE']) : '',
 
                    'id_user' => $idUser,
                    'date_creation' => date('Y-m-d H:i:s')
                );
           
            $keys = implode(', ', array_keys($checkout));
            $values = "'" . implode("', '", $checkout) . "'";
            
            $sql = "INSERT INTO {$wpdb->prefix}woocommerce_socolissimo($keys) VALUES($values)";
            
            dbDelta($sql);
            $insertId = $wpdb->query("SELECT LAST_INSERT_ID() FROM {$wpdb->prefix}woocommerce_socolissimo");
            
            $my_WC_SoColissimo = new WC_SoColissimo();
            error_log('Redirect Paiement : ' . $my_WC_SoColissimo->socolissimo_url_ok . '&SOCO=ok');
            wp_redirect($my_WC_SoColissimo->socolissimo_url_ok . '&SOCO=ok&id_chekout_customer=' . $insertId , '302');
            exit;
            }
       // }
   // }
}

// Fonction appelé avant l'affichage du formulaire finale
// On check les données et on les stock dans les $_POST[] pour rentrer dans le premier if de la méthode get_value de woocommerce (class : WC_Checkout)
add_action('woocommerce_before_checkout_billing_form', 'woocommerce_socolissimo_overload_checkout_value');
function	woocommerce_socolissimo_overload_checkout_value() {
	
    global $wpdb;

    // Si on a un id pour récupérer les données
    if (!empty($_GET['id_chekout_customer'])) {
        // On récupère les données
        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}woocommerce_socolissimo WHERE id=%d LIMIT 1", $_GET['id_chekout_customer']));
        
        // Si la requete renvoie rien, on quitte directement la fonction
        if (!isset($data[0]))
            return;
        
        error_log('Data : ' . print_r($data[0], true));

        // Setting des infos de facturation
        $_POST['billing_first_name'] = $data[0]->ce_first_name;
        $_POST['billing_last_name'] = $data[0]->ce_name;
        $_POST['billing_company'] = $data[0]->ce_company_name;
        $_POST['billing_address_1'] = $data[0]->ce_adress3;
        $_POST['billing_address_2'] = $data[0]->ce_adress4;
        $_POST['billing_address_2'] .= ' ' . $data[0]->ce_adress1;
        $_POST['billing_address_2'] .= ' ' . $data[0]->ce_adress2;
        $_POST['billing_postcode'] = $data[0]->ce_zip_code;
        $_POST['billing_city'] = $data[0]->ce_town;
        $_POST['billing_country'] = 'FR';
        $_POST['billing_email'] = $data[0]->ce_email;
        $_POST['billing_phone'] = $data[0]->ce_phone_number;
        
        if ($data[0]->delivery_mode == 'A2P')
            $data[0]->delivery_mode = 'BPR';
        
        switch ($data[0]->delivery_mode) {
            case 'DOM': // DOMICILE
                $_POST['shipping_first_name'] = $data[0]->ce_first_name;
                $_POST['shipping_last_name'] = $data[0]->ce_name;
                $_POST['shipping_company'] = $data[0]->ce_company_name;
                $_POST['shipping_address_1'] = $data[0]->ce_adress3;
                $_POST['shipping_address_2'] = $data[0]->ce_adress4;
                $_POST['shipping_address_2'] .= ' ' . $data[0]->ce_adress1;
                $_POST['shipping_address_2'] .= ' ' . $data[0]->ce_adress2;
                $_POST['shipping_postcode'] = $data[0]->ce_zip_code;
                $_POST['shipping_city'] = $data[0]->ce_town;
                $_POST['shipping_country'] = 'FR';
                $_POST['order_comments'] = '';
                if (!empty($data[0]->door_code_1))
                    $_POST['order_comments'] .= 'Code porte 1 : ' . $data[0]->door_code_1 . PHP_EOL;
                if (!empty($data[0]->door_code_2))
                    $_POST['order_comments'] .= 'Code porte 2 : ' . $data[0]->door_code_2 . PHP_EOL;
                if (!empty($data[0]->delivery_info))
                    $_POST['order_comments'] .= 'Informations de livraison : ' . $data[0]->delivery_info . PHP_EOL;
                break;
            case 'BPR': // BUREAU DE POSTE
                $_POST['shipping_first_name'] = $data[0]->ce_first_name;
                $_POST['shipping_last_name'] = $data[0]->ce_name;
                $_POST['shipping_company'] = $data[0]->pr_name;
                $_POST['shipping_address_1'] = $data[0]->pr_adress1;
                $_POST['shipping_postcode'] = $data[0]->pr_zip_code;
                $_POST['shipping_city'] = $data[0]->pr_town;
                $_POST['billing_address_1'] = ' ';
                $_POST['billing_address_2'] = ' ';
                $_POST['billing_postcode'] = ' ';
                $_POST['billing_city'] = ' ';
                break;
        }
        
    }
    error_log('POST : ' . print_r($_POST, true));
    
}