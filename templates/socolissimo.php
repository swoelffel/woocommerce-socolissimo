<?php
$xhtml_header = '<html><body><div>';
$xhtml_footer = '</div></body></html>';

$shipping_infos = array();

$myWC_SoColissimo = new WC_SoColissimo();

$so_trReturnUrlOk = htmlentities($myWC_SoColissimo->socolissimo_url_ok, ENT_NOQUOTES, 'UTF-8');
$so_trReturnUrlKo = htmlentities($myWC_SoColissimo->socolissimo_url_ko, ENT_NOQUOTES, 'UTF-8');

/* formAction_trReturnUrlKo */
$so_formAction_trReturnUrlKo = $myWC_SoColissimo->socolissimo_post_url . '?trReturnUrlKo=' . $so_trReturnUrlKo;

/* PudoFOID */
$so_PudoFOID = $myWC_SoColissimo->socolissimo_PudoFOID;

$checkout = $woocommerce->checkout();

if ($checkout->get_value('shiptobilling') == 1) {
    $shipping_infos = array(
        'email' => $checkout->get_value('billing_email'),
        'first_name' => $checkout->get_value('billing_first_name'),
        'last_name' => $checkout->get_value('billing_last_name'),
        'company' => $checkout->get_value('billing_company'),
        'address_1' => $checkout->get_value('billing_address_1'),
        'address_2' => $checkout->get_value('billing_address_2'),
        'city' => $checkout->get_value('billing_city'),
        'state' => $checkout->get_value('billing_state'),
        'postcode' => $checkout->get_value('billing_postcode'),
        'country' => $checkout->get_value('billing_country'),
        'weight' => $woocommerce->session->cart_contents_weight
    );
} else {
    $shipping_infos = array(
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
        'weight' => $woocommerce->session->cart_contents_weight
    );
}


$so_ceCivility = '';
/* ceCivility
  if (strtolower($shipping_infos['entry_gender']) == 'm') {
  $so_ceCivility = 'MR';
  } elseif (strtolower($shipping_infos['entry_gender']) == 'f') {
  $so_ceCivility = 'MME';
  } else {
  $so_ceCivility = 'MLE';
  }
 */

/* ceName */
$so_ceName = substr(remove_accents($shipping_infos['last_name']), 0, 34);

/* ceFirstName */
$so_ceFirstName = substr(remove_accents($shipping_infos['first_name']), 0, 29);

/* ceCompanyName */
if (!empty($shipping_infos['company'])) {
    $so_ceCompanyName = substr(remove_accents($shipping_infos['company']), 0, 38);
} else {
    $so_ceCompanyName = '';
}

/* ceAdress1 */
$so_ceAdress1 = substr(remove_accents(''), 0, 38);

/* ceAdress2 */
$so_ceAdress2 = substr(remove_accents(''), 0, 38);

/* ceAdress3 */
$so_ceAdress3 = substr(remove_accents($shipping_infos['shipping_address_1']), 0, 38);

/* ceAdress4 */
$so_ceAdress4 = substr(remove_accents($shipping_infos['shipping_address_2']), 0, 38);

/* ceZipCode */
if (preg_match("/^(?:[0-9]{1}|A{1})(?:[0-9]{1}|D{1})(?:[0-9a-zA-Z]{1}|0{1})(?:[0-9a-zA-Z]{2})$/", $shipping_infos['entry_postcode'])) {
    $so_ceZipCode = $shipping_infos['postcode'];
} else {
    $so_ceZipCode = '';
}

/* ceTown */
$so_ceTown = substr(remove_accents($shipping_infos['city']), 0, 32);

/* ceDoorCode1 */
$so_ceDoorCode1 = '';

/* ceDoorCode2 */
$so_ceDoorCode2 = '';

/* ceEntryPhone
  $shipping_infos['customers_telephone'] = str_replace(array('.', ' '), array('', ''), $shipping_infos['customers_telephone']);
  if (preg_match("/^[0-9]{10,30}$/", $shipping_infos['customers_telephone'])) {
  $so_ceEntryPhone = $shipping_infos['customers_telephone'];
  } else {
  $so_ceEntryPhone = '';
  }
 */
$so_ceEntryPhone = '';

/*
  $tmp_ICS_finding = array('%u20AC', 'Ä', ';', '#', '{', '(', '[', '|', "\r\n", "\n", '\\', '^', ')', ']', '=', '}', '$', '§', '£', '%', 'µ', '*', 'ß', '!', '≤', '∞', '"');
  $tmp_ICS_replace = array('', '', '', '', '', '', '', '', ' ', ' ', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
  $so_ceDeliveryInformation = substr(remove_accents(str_replace(
  $tmp_ICS_finding, $tmp_ICS_replace, $_COOKIE['cookie__CommentsCollection'])), 0, 70);
 */
$so_ceDeliveryInformation = '';

/* ceEmail */
if (strlen($shipping_infos['email']) >= 5 && strlen($shipping_infos['email']) <= 80) {
    if (preg_match("/^[a-zA-Z0-9_.-]{2,}\@{1}[a-zA-Z0-9_.-]{2,}\.{1}[a-zA-Z]{2,}$/", $shipping_infos['email'])) {
        $so_ceEmail = remove_accents($shipping_infos['email']);
    } else {
        $so_ceEmail = '';
    }
} else {
    $so_ceEmail = '';
}

/* cePhoneNumber
  $shipping_infos['customers_telephone'] = str_replace(array('.', ' '), array('', ''), $shipping_infos['customers_telephone']);
  if (preg_match("/^[0-9]{0,10}$/", $shipping_infos['customers_telephone'])) {
  $so_cePhoneNumber = $shipping_infos['customers_telephone'];
  } else {
  $so_cePhoneNumber = '';
  }
 */
$so_cePhoneNumber = '';

/* dyWeight */
$so_dyWeight = (int) ($shipping_infos['weight'] * 1000);

/* dyPreparationTime */
$so_dyPreparationTime = intval($myWC_SoColissimo->socolissimo_PreparationTime) ? $myWC_SoColissimo->socolissimo_PreparationTime : '';

/* dyForwardingCharges */
$so_dyForwardingCharges = number_format($myWC_SoColissimo->cost, 2);
$so_dyForwardingCharges = number_format(10.00, 2);


/* trClientNumber */
if (preg_match("/^[a-zA-Z0-9]{1,30}$/", $customer_id)) {
    $so_trClientNumber = $customer_id;
} else {
    $so_trClientNumber = '';
}

/* trOrderNumber */
if (preg_match("/^[a-zA-Z0-9]{1,30}$/", '')) {
    $so_trOrderNumber = '';
} else {
    $so_trOrderNumber = '';
}

/* trFirstOrder */
/*
  Une valeur 0 indique qu'il ne s'agit pas d'une première commande, dans ce cas les modes de livraison hors-domicile seront proposés.
  Une valeur 1 indique qu'il s'agit d'une première commande, dans ce cas les modes de livraison hors-domicile ne seront pas proposés.
  Toute autre valeur que 1 est considèérée comme équivalente à 0, dans ce cas les modes de livraison hors-domicile seront proposés.
 */
$so_trFirstOrder = $myWC_SoColissimo->first_order;

/* trParamPlus */
$so_trParamPlus = substr(remove_accents(''), 0, 256);

/* orderId */
$generated_order_id = date('dHis') . $so_trClientNumber;
if (preg_match("/^[a-zA-Z0-9]{5,16}$/", $generated_order_id)) {
    $so_orderId = $generated_order_id;
} else {
    die('<script type="text/javascript" charset="utf-8">alert("ERREUR: module:So_Colissimo -> $so_orderId - l\'orderId \'' . $so_orderId . '\' est invalide, veuillez contacter le gérant du magasin");</script>');
}

/* numVersion */
$so_numVersion = '3.0'; // type: INTEGER

/* clefSHA */
$so_merchantKey = $myWC_SoColissimo->socolissimo_merchantKey;

/* So_SIGNATURE */
/*
  selon la doc d'intégration:  pudoFOId    +  dyForwardingCharges    +  orderId    +  numVersion    +  trReturnUrlKo    +  cléSHA
  SONT DONC OBLIGATOIRE:       $so_PudoFOID,  $so_dyForwardingCharges,  $so_orderId,  $so_numVersion,  $so_trReturnUrlKo,  $so_merchantKey
 */

$So_SIGNATURE = sha1(
        $so_PudoFOID
        . $so_ceName
        . $so_dyPreparationTime
        . $so_dyForwardingCharges
        . $so_trClientNumber
        . $so_trOrderNumber
        . $so_orderId
        . $so_numVersion
        . $so_ceCivility
        . $so_ceFirstName
        . $so_ceCompanyName
        . $so_ceAdress1
        . $so_ceAdress2
        . $so_ceAdress3
        . $so_ceAdress4
        . $so_ceZipCode
        . $so_ceTown
        . $so_ceEntryPhone
        . $so_ceDeliveryInformation
        . $so_ceEmail
        . $so_cePhoneNumber
        . $so_ceDoorCode1
        . $so_ceDoorCode2
        . $so_dyWeight
        . $so_trFirstOrder
        . $so_trParamPlus
        . $so_trReturnUrlKo
        . $so_trReturnUrlOk
        . $so_merchantKey
);
error_log('Session_id=' . session_id());
if (session_id() == '')
    session_start();
error_log('Session_id=' . session_id());
$_SESSION['woocommerce_socolissimo_signature'] = $So_SIGNATURE;

error_log('Template : Session WC : ' . $_SESSION['woocommerce_socolissimo_signature']);

$champs_pudo_fo = array();
array_push($champs_pudo_fo, '<form name="formpudocall" action="' . $so_formAction_trReturnUrlKo . '" method="post">', '<input type="hidden" name="pudoFOId" value="' . $so_PudoFOID . '">');

if (!empty($so_ceCompanyName)) {
    // on envoie le champ uniquement dans le cas que le client qui commande est une société.
    // (entreprise avec une Raison Sociale)
    array_push($champs_pudo_fo, '<input type="hidden" name="ceCompanyName" value="' . $so_ceCompanyName . '">');
}

array_push($champs_pudo_fo, '<input type="hidden" name="ceCivility" value="' . $so_ceCivility . '">', '<input type="hidden" name="ceName" value="' . $so_ceName . '">', '<input type="hidden" name="dyPreparationTime" value="' . $so_dyPreparationTime . '">', '<input type="hidden" name="dyForwardingCharges" value="' . $so_dyForwardingCharges . '">', '<input type="hidden" name="ceFirstName" value="' . $so_ceFirstName . '">', '<input type="hidden" name="ceAdress1" value="' . $so_ceAdress1 . '">', '<input type="hidden" name="ceAdress2" value="' . $so_ceAdress2 . '">', '<input type="hidden" name="ceAdress3" value="' . $so_ceAdress3 . '">', '<input type="hidden" name="ceAdress4" value="' . $so_ceAdress4 . '">', '<input type="hidden" name="ceZipCode" value="' . $so_ceZipCode . '">', '<input type="hidden" name="ceTown" value="' . $so_ceTown . '">', '<input type="hidden" name="ceDoorCode1" value="' . $so_ceDoorCode1 . '">', '<input type="hidden" name="ceDoorCode2" value="' . $so_ceDoorCode2 . '">', '<input type="hidden" name="ceEntryPhone" value="' . $so_ceEntryPhone . '">', '<input type="hidden" name="ceDeliveryInformation" value="' . $so_ceDeliveryInformation . '">', '<input type="hidden" name="ceEmail" value="' . $so_ceEmail . '">', '<input type="hidden" name="cePhoneNumber" value="' . $so_cePhoneNumber . '">', '<input type="hidden" name="dyWeight" value="' . $so_dyWeight . '">', '<input type="hidden" name="trClientNumber" value="' . $so_trClientNumber . '">', '<input type="hidden" name="trOrderNumber" value="' . $so_trOrderNumber . '">', '<input type="hidden" name="trFirstOrder" value="' . $so_trFirstOrder . '">', '<input type="hidden" name="orderId" value="' . $so_orderId . '">', '<input type="hidden" name="numVersion" value="' . $so_numVersion . '">', '<input type="hidden" name="signature" value="' . $So_SIGNATURE . '">', '<input type="hidden" name="trReturnUrlOk" value="' . $so_trReturnUrlOk . '">');

array_push($champs_pudo_fo, '</form>');

$form_socolissimo = $xhtml_header . implode("", $champs_pudo_fo) . $xhtml_footer;
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        if ( document.getElementById("SoColissimo_IFrame") ) {
            var data='<?php echo($form_socolissimo) ?>';
            $("#SoColissimo_IFrame").contents().find("html").html(data);
            $("#SoColissimo_IFrame").contents().find("form[name=formpudocall]").submit();
        }
    });
</script>
<iframe id="SoColissimo_IFrame" style="width:100%; height:1100px;"></iframe>