<?php
/**
 * Plugin Name: Doliconnect PRO
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Premium Enhancement of Doliconnect
 * Version: 3.10.2
 * Author: ptibogxiv
 * Author URI: https://www.ptibogxiv.net/en
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect-pro
 * Domain Path: /languages
 * Donate link: https://www.paypal.me/ptibogxiv
 * Icon1x: https://www.ptibogxiv.net/wp-content/wppus/icons/doliconnect-pro-128x128.png
 * Icon2x: https://www.ptibogxiv.net/wp-content/wppus/icons/doliconnect-pro-256x256.png
 * BannerHigh: https://www.ptibogxiv.net/wp-content/wppus/banners/doliconnect-pro-1544x500.png
 * BannerLow: https://www.ptibogxiv.net/wp-content/wppus/banners/doliconnect-pro-722x250.png
 *
 * @author ptibogxiv.net <support@ptibogxiv.net>
 * @copyright Copyright (c) 2017-2019, ptibogxiv.net
**/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'lib/wp-package-updater/class-wp-package-updater.php';

 $doliconnectpro = new WP_Package_Updater(
 	'https://www.ptibogxiv.net',
 	wp_normalize_path( __FILE__ ),
 	wp_normalize_path( plugin_dir_path( __FILE__ ) ),
 	true
 );

load_plugin_textdomain( 'doliconnect-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

//function doliconnectpro_run() {

add_action( 'user_doliconnect_menu', 'paymentmethods_menu', 4, 1);
add_action( 'user_doliconnect_paymentmethods', 'paymentmethods_module');

function dolipaymentmodes_lock() {
return apply_filters( 'doliconnect_paymentmethods_lock', null);
}

//function example_callback( $string ) {
//    // (maybe) modify $string
//    return 'test';
//}
//add_filter( 'doliconnect_paymentmodes_lock', 'example_callback', 10, 1);

function paymentmethods_menu( $arg ) {
print "<a href='".esc_url( add_query_arg( 'module', 'paymentmethods', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='paymentmethods') { print " active";}
print "'>".__( 'Manage payment methods', 'doliconnect-pro')."</a>";
}

function paymentmethods_module( $url ) {
global $wpdb,$current_user;

if ( isset($_POST['default_paymentmethod']) ) {

$data = [
'default' => 1
];

$gateway = callDoliApi("PUT", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods/".sanitize_text_field($_POST['default_paymentmethod']), $data, dolidelay( 0, true));
$gateway = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', true));
$msg = dolialert ('success', __( 'You changed your default payment method', 'doliconnect-pro' ));
} elseif ( isset($_POST['delete_paymentmethod']) ) {

$gateway = callDoliApi("DELETE", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods/".sanitize_text_field($_POST['delete_paymentmethod']), null, dolidelay( 0, true));
$gateway = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', true));

} elseif ( isset($_POST['add_paymentmethod'])  ) {

$data = [
'default' => $_POST['default']?$_POST['default']:0,
];

$gateway = callDoliApi("POST", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods/".sanitize_text_field($_POST['add_paymentmethod']), $data, dolidelay( 0, true));
$gateway = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', true));
$msg = dolialert ('success', __( 'You have a new payment method', 'doliconnect-pro' ));
} 

$listpaymentmethods = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $listsource;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
doliconnect_enqueues();

$lock = dolipaymentmodes_lock();

print "<form role='form' action='$url' id='paymentmethods-form' method='post'>";

if ( isset($msg) ) { print $msg; }

print "<script src='https://js.stripe.com/v3/'></script>";

print doliloaderscript('paymentmethods-form');

print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if (empty($listpaymentmethods->stripe)) {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect-pro')."</b></li>";
}

if ( doliversion('10.0.0') ) {
print '<button id="ButtonAddPaymentMethod" type="button" class="list-group-item lh-condensed list-group-item-action list-group-item-primary" data-toggle="modal" data-target="#addsource" ><center><i class="fas fa-plus-circle"></i> '.__( 'New payment method', 'doliconnect-pro' ).'</center></button>';
} elseif ( current_user_can( 'administrator' ) ) {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".sprintf( esc_html__( "Register payment methods needs Dolibarr %s but your version is %s", 'doliconnect-pro'), '10.0.0',$versiondoli[0])."</b></li>";
}

//SAVED SOURCES 
$i=0;
if ( $listpaymentmethods->paymentmethods != null ) {
foreach ( $listpaymentmethods->paymentmethods as $method ) {
$i++;                                                                                                                       
print "<li class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'>";
print "<div class='d-none d-md-block col-md-2 col-lg-1'><i ";
if ( $method->type == 'sepa_debit' ) {
print 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $method->brand == 'visa' ) { print 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { print 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { print 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else {print 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
print '></i></center>';
print "</div><div class='col-8 col-sm-7 col-md-6 col-lg-7'><h6 class='my-0'>";
if ( $method->type == 'sepa_debit' ) {
print __( 'Account', 'doliconnect-pro' ).' '.$method->reference.'<small> <a href="'.$method->mandate_url.'" title="'.__( 'Mandate', 'doliconnect-pro' ).' '.$method->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
print __( 'Card', 'doliconnect-pro' ).' '.$method->reference;
}
if ( !empty($method->expiration) ) { print " - ".date("m/Y", strtotime($method->expiration.'/1')); }
print "</h6><small class='text-muted'>".$method->holder."</small></div>";
print "<div class='d-none d-md-block col-md-2 align-middle text-right'>";
print "<img src='".plugins_url('doliconnect/images/flag/'.strtolower($method->country).'.png')."' class='img-fluid' alt='$method->country'>";
print "</div>";

print "<div class='col-4 col-sm-3 col-md-2 btn-group-vertical' role='group'>";
if ( !empty($method->default_source) ) { 
print "<button class='btn btn-light' title='".__( 'Favorite', 'doliconnect-pro' )."' disabled><i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i></button>";
} elseif ( (current_time( 'timestamp', 1) >= strtotime($method->expiration.'/1')) || ! preg_match('/pm_/', $method->id) ) {
print "<button class='btn btn-light' title='".__( 'Can not be set as favorite', 'doliconnect-pro' )."' disabled><i class='fas fa-ban fa-1x fa-fw'></i></button>";
} else {
print "<button name='default_paymentmethod' value='".$method->id."' class='btn btn-light' type='submit' title='".__( 'Set as favorite', 'doliconnect-pro' )."'><i class='far fa-star fa-1x fa-fw'></i></button>";
}
print "<button name='delete_paymentmethod' value='".$method->id."' class='btn btn-light text-danger' type='submit' title='".__( 'Delete', 'doliconnect' )."'><i class='fas fa-trash fa-fw'></i></button>";
print "</div></li>";
}
print "</li>";

} else {
print "<li class='list-group-item list-group-item-light'><center>".__( 'No payment method', 'doliconnect-pro' )."</center></li>";
}
print "</ul></div></form>";

if ( $i < 5 && doliversion('10.0.0') ) {

print "<div class='modal fade' id='addsource' tabindex='-1' role='dialog' aria-labelledby='addsourceTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>
<h5 class='modal-title' id='addsourceTitle'>".__( 'New payment method', 'doliconnect-pro' )."</h5><button id='CloseAddPaymentMethod' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
</div><div id='BodyAddPaymentMethod'><ul class='list-group list-group-flush'><li class='list-group-item'>"; 
print "<form role='form' action='$url' id='newpaymentmethod-form' method='post'>";
print '<input id="cardholder-name" name="cardholder-name" value="" type="text" class="form-control" placeholder="'.__( 'Owner as on your card', 'doliconnect-pro' ).'" autocomplete="off" required>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
print "</li><li class='list-group-item'><small><div class='custom-control custom-checkbox my-1 mr-sm-2'><input type='checkbox' class='custom-control-input' value='1' id='default' name='default'";
if (empty($i)) { print " checked disabled"; }
print "><label class='custom-control-label' for='default'> ".__( 'Set as default payment mode', 'doliconnect-pro' )."</label></div>";
if (empty($i)) { print "<input type='hidden' name='default' value='1'>"; }
print '</small></li></ul></div>';
print doliloading('addnewpaymentmethod');
print "<div id='FooterAddPaymentMethod' class='modal-footer'><button name='add_card' id='buttontoaddcard' value='add_card' class='btn btn-warning btn-block' type='submit' title='".__( 'Add', 'doliconnect' )."'><b>".__( 'Add', 'doliconnect' )."</b></button></form>";
print "</div></div></div></div>";

print "<script>";
if ( $listpaymentmethods->code_account != null ) {
print "var stripe = Stripe('".$listpaymentmethods->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->code_account."'
});";
} else {
print "var stripe = Stripe('".$listpaymentmethods->publishable_key."');";
}
print 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};'; 

// Create an instance of Elements
print 'var elements = stripe.elements();';
print 'var cardElement = elements.create("card", {style: style});';
print 'cardElement.mount("#card-element");';

// Handle real-time validation errors from the card Element.
print 'var displayError = document.getElementById("card-errors");
displayError.textContent = "";
cardElement.addEventListener("change", function(event) {
  if (event.error) {
    console.log("Show event error");
    displayError.textContent = event.error.message;
  } else {
    console.log("Reset error message");
    displayError.textContent = "";
  }
});';

// Handle form submission
print 'var cardholderName = document.getElementById("cardholder-name");';
print 'var cardButton = document.getElementById("buttontoaddcard");';
print 'var form = document.getElementById("newpaymentmethod-form");';

// Actions
print 'cardButton.addEventListener("click", function(event) {
console.log("We click on buttontoaddcard");
event.preventDefault();
jQuery("#CloseAddPaymentMethod").hide();
jQuery("#FooterAddPaymentMethod").hide();
jQuery("#BodyAddPaymentMethod").hide();   
jQuery("#doliloading-addnewpaymentmethod").show();
        if (cardholderName.value == "")
        	{
jQuery("#CloseAddPaymentMethod").show();
jQuery("#FooterAddPaymentMethod").show();
jQuery("#BodyAddPaymentMethod").show();   
jQuery("#doliloading-addnewpaymentmethod").hide();         
				console.log("Field Card holder is empty");
				var displayError = document.getElementById("card-errors");
				displayError.textContent = "'.__( "We need an owner as on your card.", "doliconnect-pro").'";
        	}
        else
        	{
        stripe.createPaymentMethod(
  "card",
  cardElement, {
  billing_details: {
    name: cardholderName.value
  },
}
).then(function(result) {
  if (result.error) {
    // Show error in payment form
jQuery("#CloseAddPaymentMethod").show();
jQuery("#FooterAddPaymentMethod").show();
jQuery("#BodyAddPaymentMethod").show();   
jQuery("#doliloading-addnewpaymentmethod").hide(); 
console.log("Error occured when adding card");
var displayError = document.getElementById("card-errors");
displayError.textContent = "'.__( "Your card number seems to be wrong.", "doliconnect-pro").'";    
  } else {
	      var hiddenInput = document.createElement("input");
	      hiddenInput.setAttribute("type", "hidden");
	      hiddenInput.setAttribute("name", "add_paymentmethod");
	      hiddenInput.setAttribute("value", result.paymentMethod.id);
	      form.appendChild(hiddenInput); 

jQuery(window).scrollTop(0);
console.log("submit");
jQuery("#newpaymentmethod-form").submit();  
  }
});         
          }
});';
print "</script>";

}

print "<small><div class='float-left'>";
print dolirefresh($request, $url, dolidelay('paymentmethods'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";

}

function dolipaymentmodes($paymentintent, $listpaymentmethods, $object, $redirect, $url) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
doliconnect_enqueues();

if ( isset($object) ) { 
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
} else {
$currency=strtolower('eur');
$stripeAmount=0;
}

$lock = dolipaymentmodes_lock();

$paymentmethod = "<script src='https://js.stripe.com/v3/'></script>";

$paymentmethod .= "<div id='payment-errors' class='alert alert-danger' role='alert' style='display: none'></div>";

$paymentmethod .= "<div id='payment-form'><div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if (empty($listpaymentmethods->stripe)) {
$paymentmethod .= "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect-pro')."</b></li>";
}

if ( empty($object) ) { //$  &&  ( listsource->discount != 0 || $listsource->discount_product != null )
$paymentmethod .= "<li id='DiscountForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='discount' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='discount' ";
if ( !empty($object) && !current_user_can( 'administrator' ) ) { $paymentmethod .= " disabled "; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='discount'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-piggy-bank fa-3x fa-fw' style='color:HotPink'></i></center>";
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>";
if ( $listpaymentmethods->discount >= 0 ) {
$paymentmethod .= __( 'Credit of', 'doliconnect-pro' );
} else {
$paymentmethod .= __( 'Debit of', 'doliconnect-pro' );
}
$paymentmethod .= " ".doliprice($listpaymentmethods->discount)."</h6><small class='text-muted'>".__( 'Soon available', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
//if ( empty($object) && get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
//print '<li class="list-group-item list-group-item-secondary" id="Recharge" style="display: none">';
//print 'Prochainement, vous pourrez recharger votre compte!';
//print '<div class="input-group mb-3">
//  <div class="input-group-prepend">
//    <span class="input-group-text">$</span>
//  </div>
//  <input type="num" class="form-control" aria-label="Amount (to the nearest dollar)">
//  <div class="input-group-append">
//    <span class="input-group-text">.00</span>
//  </div>
//</div>';
//print '</li>';
//}
}

//SAVED SOURCES
if ( $listpaymentmethods->paymentmethods != null ) {
$i=0;    
foreach ( $listpaymentmethods->paymentmethods as $method ) {
$i++;                                                                                                                         
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$method->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='$method->id' ";
if ( date('Y/n') >= $method->expiration && !empty($object) && !empty($method->expiration) ) { $paymentmethod .= " disabled "; }
elseif ( $i == 1 || !empty($method->default_source) ) { $paymentmethod .= " checked "; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='$method->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i ';
if ( $method->type == 'sepa_debit' ) {
$paymentmethod .= 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $method->brand == 'visa' ) { $paymentmethod .= 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethod .= 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethod .= 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else { $paymentmethod .= 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
$paymentmethod .= '></i></center>';
$paymentmethod .= '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $method->type == 'sepa_debit' ) {
$paymentmethod .= __( 'Account', 'doliconnect-pro' ).' '.$method->reference.'<small> <a href="'.$method->mandate_url.'" title="'.__( 'Mandate', 'doliconnect-pro' ).' '.$method->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
$paymentmethod .= __( 'Card', 'doliconnect-pro' ).' '.$method->reference;
}
if ( !empty($method->expiration) ) { $paymentmethod .= " - ".date("m/Y", strtotime($method->expiration.'/1')); }
$paymentmethod .= "</h6><small class='text-muted'>".$method->holder."</small></div>";
$paymentmethod .= "<div class='d-none d-sm-block col-2 align-middle text-right'>";
$paymentmethod .= "<img src='".plugins_url('doliconnect/images/flag/'.strtolower($method->country).'.png')."' class='img-fluid' alt='$method->country'>";
//print "<div class='btn-group-vertical' role='group'><a class='btn btn-light text-primary' href='#' role='button'><i class='fas fa-edit fa-fw'></i></a>
//<button name='delete_source' value='".$method->id."' class='btn btn-light text-danger' type='submit'><i class='fas fa-trash fa-fw'></i></button></div>";
$paymentmethod .= "</div></div></label></div></li>";
} }

//NEW CARD
if ( $i < 5 && $listpaymentmethods->code_client != null && !empty($listpaymentmethods->card) ) {      
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='CdDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newcard' ";
if ( empty($i) && empty($listpaymentmethods->paymentmethods) ) { $paymentmethod .= " checked"; }
$paymentmethod .= "><label class='custom-control-label w-100' for='CdDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-credit-card fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Credit card', 'doliconnect-pro' )."</h6><small class='text-muted'>Visa, MasterCard, Amex...</small></div></div>";
$paymentmethod .= "</label></div></li>";

$paymentmethod .= '<li class="list-group-item list-group-item-secondary" id="CardForm" style="display: none"><form action="'.$url.'" >'; //onchange="ShowHideDiv()"
$paymentmethod .= '<input id="cardholder-name" name="cardholder-name" value="" type="text" class="form-control" placeholder="'.__( 'Owner as on your credit card', 'doliconnect-pro' ).'" autocomplete="off" required>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
$paymentmethod .= '</form></li>';
}

//NEW SEPA DIRECT DEBIT
if ( $i < 5 && $listpaymentmethods->code_client != null && !empty($listpaymentmethods->sepa_direct_debit) ) {    
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='BkDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newbank' ";
//if ($listsource["sources"]==null) {print " checked";}
$paymentmethod .= " ><label class='custom-control-label w-100' for='BkDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= "<center><i class='fas fa-university fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Via SEPA Direct Debit', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
$paymentmethod .= '<li class="list-group-item list-group-item-secondary" id="BankForm" style="display: none">';
$paymentmethod .= "<p class='text-justify'>";
$blogname=get_bloginfo('name');
$paymentmethod .= '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect-pro' ), $blogname).'</small>';
$paymentmethod .= "</p>";
$paymentmethod .= '<input id="ibanholder-name" name="ibanholder-name" value="" type="text" class="form-control" placeholder="'.__( 'Owner as on your bank account', 'doliconnect-pro' ).'" autocomplete="off">
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- A Stripe Element will be inserted here. --></div>';
$paymentmethod .= '<div id="bank-name"></div>';
$paymentmethod .= '<div id="iban-errors" role="alert"></div>';
$paymentmethod .= '</li>';
}

//PAYMENT REQUEST API
if ( ! empty($object) && get_option('doliconnectbeta')=='1' && !empty($listpaymentmethods->payment_request_api) ) {  
$paymentmethod .= "<li id='PraForm' class='list-group-item list-group-item-action flex-column align-items-start' style='display: none'><div class='custom-control custom-radio'>
<input id='src_pra' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PRA' ";
//if ($listsource["sources"] == null) { $paymentmethod .= " checked";}
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_pra'>";
//$paymentmethod .= "<div class='row' id='googlepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
//$paymentmethod .= '<center><i class="fab fa-google fa-3x fa-fw" style="color:Black"></i></center>';
//$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Google Pay', 'doliconnect-pro' )."</h6>";
//$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect-pro' )."</small></div></div>";
$paymentmethod .= "<div class='row' id='applepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-apple-pay fa-3x fa-fw" style="color:Black"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Apple Pay', 'doliconnect-pro' )."</h6>";
$paymentmethod .= "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect-pro' )."</small></div></div>";
$paymentmethod .= '</label></div></li>';
}

//alternative payment modes & offline
if ( ! empty($object) ) {

if ( isset($listpaymentmethods->PAYPAL) && $listpaymentmethods->PAYPAL != null && get_option('doliconnectbeta') == '1' && current_user_can( 'administrator' ) ) {
$paymentmethod .= "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='PAYPAL' ";
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fab fa-paypal fa-3x fa-fw" style="color:#2997D8"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Redirect to Paypal', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

if ( isset($listpaymentmethods->RIB) && $listpaymentmethods->RIB != null ) {
$paymentmethod .= "<li id='VirForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_vir' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='VIR' ";
if ( $listpaymentmethods->paymentmethods == null && empty($listpaymentmethods->card) ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

if ( isset($listpaymentmethods->CHQ) && $listpaymentmethods->CHQ != null ) {
$paymentmethod .= "<li id='ChqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_chq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='CHQ' ";
if ( $listpaymentmethods->paymentmethods == null && $listpaymentmethods->card != 1 && $listpaymentmethods->RIB == null ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
} 

if ( ! empty(dolikiosk()) ) {
$paymentmethod .= "<li id='LiqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_liq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='LIQ' ";
if ( $listpaymentmethods->paymentmethods == null && empty($listpaymentmethods->card) && $listpaymentmethods->CHQ == null && $listpaymentmethods->RIB == null ) { $paymentmethod .= " checked"; }
$paymentmethod .= " ><label class='custom-control-label w-100' for='src_liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethod .= '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
$paymentmethod .= "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect-pro' )."</small>";
$paymentmethod .= '</div></div></label></div></li>';
}

}

// save new source button
$paymentmethod .= "<li id='SaveFormButton' class='list-group-item list-group-item-action flex-column align-items-start'  style='display: none'>";
if ( ! empty($object) ) { $paymentmethod .= '<div class="custom-control custom-checkbox"><input id="savethesource" class="custom-control-input form-control-sm" type="checkbox" name="savethesource" value="1" ><label class="custom-control-label w-100" for="savethesource"><small class="form-text text-muted"> '.__( 'Save this payment method', 'doliconnect-pro' ).'</small></label></div>';}
else { $paymentmethod .= '<div class="custom-control custom-checkbox"><input id="savethesource" type="hidden" name="savethesource" value="1"><input id="setasdefault" class="custom-control-input form-control-sm" type="checkbox" name="setasdefault" value="1" checked><label class="custom-control-label w-100" for="setasdefault"><small class="form-text text-muted"> '.__( 'Set as default mode', 'doliconnect-pro' ).'</small></label></div>';}
$paymentmethod .= "</li>";

$paymentmethod .= "</ul><div class='card-body'>";

if ( $listpaymentmethods->paymentmethods == null ) { $paymentmethod .= "<input type='hidden' name='defaultsource' value='nosavedsource'>"; }  

$paymentmethod .= "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'>";
$paymentmethod .= "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>";
$paymentmethod .= "<button id='pay-Button' class='btn btn-danger btn-block' type='submit'><b>".__( 'Pay', 'doliconnect-pro' )." ".doliprice($object, 'ttc',$currency)."</b></button>";

$paymentmethod .= "</div></div>";

if ( empty($object) ) {
$paymentmethod .= "<small><div class='float-left'>";
$paymentmethod .= dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethod .= "</div><div class='float-right'>";
$paymentmethod .= dolihelp('ISSUE');
$paymentmethod .= "</div></small>";
}

$paymentmethod .= "</div>";

$paymentmethod .= '<div id="payment-success" class="card text-white bg-success" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Success Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
$paymentmethod .= '<div id="payment-waiting" class="card text-white bg-warning" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Waiting Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
$paymentmethod .= '<div id="payment-error" class="card text-white bg-danger" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Error Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';

$paymentmethod .= doliloading('payment');  

$paymentmethod .= "<script>";
if ( $listpaymentmethods->code_account != null ) {
$paymentmethod .= "var stripe = Stripe('".$listpaymentmethods->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->code_account."'
});";
} else {
$paymentmethod .= "var stripe = Stripe('".$listpaymentmethods->publishable_key."');";
}
$paymentmethod .= 'var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};';

//VARIABLES
$paymentmethod .= '//VARIABLES
var CdDbt = document.getElementById("CdDbt");
var BkDbt = document.getElementById("BkDbt");  
var discount = document.getElementById("discount");

var src_chq = document.getElementById("src_chq");
var src_vir = document.getElementById("src_vir");
var src_liq = document.getElementById("src_liq");
var src_pra = document.getElementById("src_pra");

var montant = '.($object->total_ttc*100).';
var currency = "'.strtolower(isset($object->multicurrency_code) ? $object->multicurrency_code : 'EUR').'";
';

$paymentmethod .= 'function ShowHideDiv() {
//CARD
if ( CdDbt && CdDbt.checked ) {
// Create an instance of Elements
var elements = stripe.elements();
var cardElement = elements.create("card", {style: style});
cardElement.mount("#card-element");';

// Handle real-time validation errors from the card Element.
$paymentmethod .= 'var cardholderName = document.getElementById("cardholder-name");
cardholderName.value = "";
var displayError = document.getElementById("card-errors");
displayError.textContent = "";
cardElement.addEventListener("change", function(event) {
document.getElementById("pay-Button").disabled = false;
  if (event.error) {
    console.log("Show event error");
    displayError.textContent = event.error.message;
  } else {
    console.log("Reset error message");
    displayError.textContent = "";
  }
});


}';

$paymentmethod .= '
if (CdDbt) {
document.getElementById("CardForm").style.display = CdDbt.checked ? "block" : "none";
}

if (src_pra && src_pra.checked) {
  document.getElementById("pay-Button").style.display = "none";
  document.getElementById("payment-request-button").style.display = "block";
} else {
  document.getElementById("pay-Button").style.display = "block";
  document.getElementById("payment-request-button").style.display = "none";
}

var payButton = document.getElementById("pay-Button");
var clientSecret = "'.$paymentintent->stripe->client_secret.'";

payButton.addEventListener("click", function(ev) {
console.log("We click on buttontoaddcard");
event.preventDefault();
document.getElementById("pay-Button").disabled = true; 
        if (cardholderName.value == "")
        	{        
				console.log("Field Card holder is empty");
				var displayError = document.getElementById("card-errors");
				displayError.textContent = "'.__( "We need an owner as on your card.", "doliconnect-pro").'";
        document.getElementById("pay-Button").disabled = false;    
        	}
        else
        	{
  stripe.handleCardPayment(
    clientSecret, cardElement, {
      payment_method_data: {
        billing_details: {name: cardholderName}
      }
    }
  ).then(function(result) {
    if (result.error) {
    // Show error in payment form
jQuery("#DoliconnectLoadingModal").modal("hide");
console.log("Error occured when adding card");
var displayError = document.getElementById("card-errors");
displayError.textContent = "'.__( "Your card number seems to be wrong.", "doliconnect-pro").'";    
    } else {
      // The payment has succeeded. Display a success message.
    }
  }); 
}
});

}
window.onload=ShowHideDiv;
';

//PAYMENT REQUEST API
$paymentmethod .= '
var paymentRequest = stripe.paymentRequest({
  country: "FR",
  currency: currency,
  total: {
    label: "Demo total",
    amount: montant,
  },
});
//requestPayerName: true,
//requestPayerEmail: true,

var elements = stripe.elements();
var prButton = elements.create("paymentRequestButton", {
  paymentRequest: paymentRequest,
});

// Check the availability of the Payment Request API first.
paymentRequest.canMakePayment().then(function(result) {
  if (result) {
    prButton.mount("#payment-request-button");
    document.getElementById("payment-request-button").style.display = "none";
    document.getElementById("PraForm").style.display = "block";
  } else {
    document.getElementById("payment-request-button").style.display = "none";
    document.getElementById("PraForm").style.display = "none";
  }
});

paymentRequest.on("paymentmethod", function(ev) {
  stripe.confirmPaymentIntent(clientSecret, {
    payment_method: ev.paymentMethod.id,
  }).then(function(confirmResult) {
    if (confirmResult.error) {
      // Report to the browser that the payment failed, prompting it to
      // re-show the payment interface, or show an error message and close
      // the payment interface.
      ev.complete("fail");
    } else {
      // Report to the browser that the confirmation was successful, prompting
      // it to close the browser payment method collection interface.
      ev.complete("success");
      // Let Stripe.js handle the rest of the payment flow.
      stripe.handleCardPayment(clientSecret).then(function(result) {
        if (result.error) {
          // The payment failed -- ask your customer for a new payment method.
        } else {
          // The payment has succeeded.
        }
      });
    }
  });
});
';

$paymentmethod .= "</script>";

return $paymentmethod;
}

function dolimembership($current_user, $statut, $type, $delay) {
if ($statut=='1') {
$statut='-1';
$action='POST';
} elseif ($statut=='2') {
$statut='0';
$action='PUT';
} elseif ($statut=='3') {
$statut='-1';
$action='PUT';
} elseif ($statut=='4') {
$statut='1';
$action='PUT';
} elseif ($statut=='5') {
$statut='1';
$action='POST';
} 

list($year, $month, $day) = explode("-", $current_user->billing_birth);
$birth = mktime(0, 0, 0, $month, $day, $year);

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));  

$data = [
    'login' => $current_user->user_login,
    'company'  => $current_user->billing_company,
    'morphy' => $current_user->billing_type,
    'civility_id' => $current_user->civility_id,    
    'lastname' => $current_user->user_lastname,
    'firstname' => $current_user->user_firstname,
    'address' => $thirdparty->address,    
    'zip' => $thirdparty->zip,
    'town' => $thirdparty->town,
    'country_id' => $thirdparty->country_id,
    'email' => $thirdparty->email,
    'phone' => $thirdparty->phone,
    'birth' => $birth,
    'typeid' => $type,
    'socid' => doliconnector($current_user, 'fk_soc'),
    'array_options' => $thirdparty->array_options,
		'statut'	=> $statut,
	];
  
if ($action=='POST') {
$mbr = callDoliApi("POST", "/adherentsplus", $data, 0);
$adhesion = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member', true), null, dolidelay('member', true));
} else {
$adhesion = callDoliApi("PUT", "/adherentsplus/".doliconnector($current_user, 'fk_member', true), $data, 0);
}

return $adhesion;
}

function dolimembership_modal($current_user, $adherent = null, $delay) {

doliconnect_enqueues();

print "<div class='modal fade' id='activatemember' tabindex='-1' role='dialog' aria-labelledby='activatememberLabel' aria-hidden='true' data-backdrop='static' data-keyboard='false'><div class='modal-dialog modal-dialog-centered modal-lg' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>";
print "<h4 class='modal-title' id='myModalLabel'>".__( 'Subscription', 'doliconnect-pro' )." ".$adherent->next_subscription_season."</h4><button id='subscription-close' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>";
if ( $adherent->id > 0 ) {
print "<h6 id ='subscription-h6' class='text-center'>".sprintf(__('Available from %s to %s', 'doliconnect-pro'), strftime("%d/%m/%Y",$adherent->next_subscription_date_start), strftime("%d/%m/%Y",$adherent->next_subscription_date_end));

if ( isset($nextdebut) ) {
$daterenew =  date_i18n('d/m/Y', $nextdebut);
} else {
$daterenew =  date_i18n('d/m/Y', current_time('timestamp', 1));
}

//if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
//print "<center>".sprintf(__('Renew from %s', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_renew))."</center>";
//}

if ($adherent->datefin == null) {print "<br />".__( 'An entry fee can be applied to you depending on the type', 'doliconnect-pro' );} 
elseif ( $adherent->next_subscription_valid > current_time( 'timestamp',1) && $adherent->next_subscription_renew < current_time( 'timestamp',1) ) {print "<br />".sprintf(__('From %s, a welcome fee can be apply', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_valid)); }
print "</h6>";  
$tx=1;  
} else {
$tx=1;
}
print "<table class='table table-striped' id ='subscription-table'>";

if ( ! empty($adherent) && $adherent->statut != 0 ) {
print "<tr><td><div class='row'><div class='col-md-8'><b><i class='fas fa-user-slash'></i> ".__( 'Cancel my subscription', 'doliconnect-pro' );

print "<small></small></b><br /><small class='text-justify text-muted '>".__( 'Will be terminated', 'doliconnect-pro' );
if ($adherent->datefin > current_time('timestamp', 1) )  {
print " ".sprintf( __( 'from the %s', 'doliconnect-pro' ), date_i18n('d/m/Y', $adherent->datefin));
} else { print " ".__( 'immediately', 'doliconnect-pro' ); }
print "</small></div><div class='col-md-4'>";
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value=''><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='2'><input type='hidden' name='typeadherent' value=''><button class='btn btn-dark btn-block' type='submit'>".__( 'Resiliate', 'doliconnect-pro' )."</button></form>";
print "</td></tr>";
}

if ( !isset($adherent->datefin) || ( $adherent->datefin>current_time( 'timestamp',1)) || ( $adherent->datefin < current_time( 'timestamp',1)) ) {
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$current_user->billing_type."')", null, $delay);
//print $typeadhesion;

if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
$montant1 = $postadh->price;
$montant2 = $tx*$postadh->price; 

if ( ( $postadh->subscription == '1' || ( $postadh->subscription != '1' && $adherent->typeid == $postadh->id ) ) && $postadh->statut == '1' || ( $postadh->statut == '0' && $postadh->id == $adherent->typeid && $adherent->statut == '1' ) ) {
print "<tr><td><div class='row'><div class='col-md-8'><b>";
if ($postadh->morphy == 'mor') {
print "<i class='fas fa-user-tie fa-fw'></i> "; 
} elseif ($postadh->morphy == 'phy') {
print "<i class='fas fa-user fa-fw'></i> "; 
} else {print "<i class='fas fa-user-friends fa-fw'></i> ";}
print doliproduct($postadh, 'label');
if (! empty ($postadh->duration_value)) print " - ".doliduration($postadh);
print " <small>";
if ( !empty($postadh->subscription) ) {
if ( ( ($postadh->welcome > '0') && ($adherent->datefin == null )) || (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $adherent->next_subscription_valid) && (current_time( 'timestamp',1) > $adherent->datefin) && $adherent->next_subscription_valid != $adherent->datefin ) ) { 
$montantdata=($tx*$postadh->price)+$postadh->welcome;
print "(";
print doliprice($montantdata)." ";
print __( 'then', 'doliconnect-pro' )." ".doliprice($montant1)." ".__( 'yearly', 'doliconnect-pro' ); 
} else {
print "(".doliprice($montant1);
print " ".__( 'yearly', 'doliconnect-pro' );
$montantdata=($tx*$postadh->price);
} 
print ")"; } else { print "<span class='badge badge-pill badge-primary'>".__( 'Free', 'doliconnect-pro' )."</span>"; }
print "</small></b><br /><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small></div><div class='col-md-4'>";
if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
print "<button class='btn btn-info btn-block' disabled>".sprintf(__('From %s', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_renew))."</a>";
} elseif ( $postadh->family == '1' ) {
print "<a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info btn-block' role='button'>".__( 'Contact us', 'doliconnect-pro' )."</a>";
} 
elseif ( ( $postadh->statut == '0' && $postadh->id == $adherent->typeid ) ) { 
print "<button class='btn btn-secondary btn-block' disabled>".__( 'Non-renewable', 'doliconnect-pro' )."</a>";
} 
elseif ( ( $postadh->automatic_renew != '1' && $postadh->id == $adherent->typeid ) ) { //to do add security for avoid loop  in revali
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";
} 
elseif ( ($postadh->automatic == '1' ) && ($postadh->id == $adherent->typeid) ) {
if ( $adherent->statut == '1' ) {
if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro' )."</button></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><center><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}
}
} elseif ( $adhesionstatut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";
}

} elseif (($postadh->automatic == '1') && ($postadh->id != $adherent->typeid)) {

if ( $adherent->statut == '1' ) {

if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
} else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
} else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";}
}

} elseif ( $adherent->statut == '0' ) {

print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";

} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
}

} elseif ( ($postadh->automatic != '1' ) && ( $postadh->id == $adherent->typeid ) ) {

if ( $adherent->statut == '1' ) {

if ($adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro' )."</button></form>";
} else {
if ($adherent->datefin>current_time( 'timestamp',1)) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}
}

} elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
elseif ( $adherent->statut == '-1' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect-pro' )."</button></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit' >".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
}
elseif ( ($postadh->automatic != '1' ) and ( $postadh->id != $adherent->typeid) ) {
if ($adherent->statut == '1') {
if ($adherent->datefin == null ){print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><center><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}
}
}
elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
} 
}
}
print "</div></div></td></tr>"; 
}
} else { 
print "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect-pro' )."</center></li>";
}

}
print "</table>";

print doliloading('subscription'); 

print "</div><div id='subscription-footer' class='modal-footer border-0'><small class='text-justify'>".__( 'Note: the admins reserve the right to change your membership (type/status) in relation to your personal situation when you finalize your order. A validation of the membership may be necessary depending on the cases.', 'doliconnect-pro' )."</small></div></div></div></div>";

}

function addtodolibasket($product, $quantity = null, $price = null, $remise_percent = null, $timestart = null, $timeend = null, $url = null) {
global $current_user;

if (!is_null($timestart) && $timestart > 0 ) {
$date_start=strftime('%Y-%m-%d 00:00:00', $timestart);
} else {
$date_start=null;
}

if ( !is_null($timeend) && $timeend > 0 ) {
$date_end=strftime('%Y-%m-%d 00:00:00', $timeend);
} else {
$date_end=null;
}

if ( empty(doliconnector($current_user, 'fk_order', true)) ) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));
$rdr = [
    'socid' => doliconnector($current_user, 'fk_soc'),
    'date_commande' => mktime(),
    'demand_reason_id' => 1,
    'cond_reglement_id' => $thirdparty->cond_reglement_id,
    'module_source' => 'doliconnect',
    'pos_source' => get_current_blog_id(),
	];                  
$order = callDoliApi("POST", "/orders", $rdr, 0);
}

$orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));

if ( $orderfo->lines != null ) {
foreach ( $orderfo->lines as $ln ) {
if ( $ln->fk_product == $product ) {
//$deleteline = callDoliApi("DELETE", "/orders/".$orderid."/lines/".$ln[id], null, 0);
//$qty=$ln[qty];
$line=$ln->id;
}
}}

if (!$line > 0) { $line=null; }

if ( doliconnector($current_user, 'fk_order') > 0 && $quantity > 0 && is_null($line) ) {
$prdt = callDoliApi("GET", "/products/".$product."?includestockdata=1", null, dolidelay('product', true));
$adln = [
    'fk_product' => $product,
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price
	];                 
$addline = callDoliApi("POST", "/orders/".doliconnector($current_user, 'fk_order')."/lines", $adln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
set_transient( 'doliconnect_cartlinelink_'.$addline, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
}
return $addline;

} elseif ( doliconnector($current_user, 'fk_order') > 0 && $line > 0 ) {

if ( $quantity < 1 ) {

$deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, null, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
delete_transient( 'doliconnect_cartlinelink_'.$line );

return $deleteline;
 
} else {

$prdt = callDoliApi("GET", "/products/".$product."?includestockdata=1", null, 0);
 $ln = [
    'desc' => $prdt->description,
    'date_start' => $date_start,
    'date_end' => $date_end,
    'qty' => $quantity,
    'tva_tx' => $prdt->tva_tx, 
    'remise_percent' => isset($remise_percent) ? $remise_percent : doliconnector($current_user, 'remise_percent'),
    'subprice' => $price
	];                  
$updateline = callDoliApi("PUT", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$line, $ln, 0);
$order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order', true)."?contact_list=0", null, dolidelay('order', true));
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
if ( !empty($url) ) {
set_transient( 'doliconnect_cartlinelink_'.$line, esc_url($url), dolidelay(MONTH_IN_SECONDS, true));
} else {
delete_transient( 'doliconnect_cartlinelink_'.$line );
}
return $updateline;

}
}
}

function doliminicart($object) {
global $current_user;

$remise=0;
$subprice=0;
$qty=0;

if ( $object->lines != null ) {
$list = null;
foreach ($object->lines as $line) {
//$product = callDoliApi("GET", "/products/".$post->product_id."?includestockdata=1", null, 0);
$list .= "<li class='list-group-item d-flex justify-content-between lh-condensed'><div><h6 class='my-0'>".$line->libelle."</h6><small class='text-muted'>".__( 'Quantity', 'doliconnect-pro' ).": ".$line->qty."</small></div>";
$remise+=$line->subprice-$line->price;
$subprice+=$line->subprice;
$qty+=$line->qty;
$list .= "<span class='text-muted'>".doliprice($line, 'total_ttc',isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</span></li>";
}
}

$cart = "<div class='card'><div class='card-header'>".__( 'Cart', 'doliconnect-pro' )." - ".sprintf( _n( '%s item', '%s items', $qty, 'doliconnect-pro' ), $qty);
if ( !isset($object->resteapayer) && $object->statut == 0 ) { $cart .= " <small>(<a href='".doliconnecturl('dolicart')."' >".__( 'update', 'doliconnect-pro' )."</a>)</small>"; }
$cart .= "</div><ul class='list-group list-group-flush'>";
$cart .= $list;

if ( doliconnector($current_user, 'remise_percent') > 0 && $remise > 0 ) { 
$remise_percent = (0*doliconnector($current_user, 'remise_percent'))/100;
$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>
<div class='text-success'><small class='my-0'>".__( 'Discount', 'doliconnect-pro' )."</small>";
//$cart .= "<br><small>-".number_format(100*$remise/$subprice, 0)." %</small>";
$cart .= "</div><small class='text-success'>-".doliprice($remise, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";
}

$cart .= "<li class='list-group-item d-flex justify-content-between bg-light'>";
$cart .= "<small>".__( 'VAT', 'doliconnect-pro' )."</small>";
$cart .= "<small>".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</small></li>";

//$total=$subtotal-$remise_percent;            
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
if ( isset($object->resteapayer) ) { 
$cart .= "<span>".__( 'Already paid', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object->total_ttc-$object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
$cart .= "<li class='list-group-item d-flex justify-content-between'>";
$cart .= "<span>".__( 'Remains to be paid', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object->resteapayer, null, isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
} else {
$cart .= "<span>".__( 'Total to pay', 'doliconnect-pro' )."</span>";
$cart .= "<strong>".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</strong></li>";
}
$cart .= "</ul></div><br>";
return $cart;
}
// ********************************************************
function dolicart_display($content) {
global $wpdb, $current_user;

if ( in_the_loop() && is_main_query() && is_page(doliconnectid('dolicart')) && !empty(doliconnectid('dolicart')) )  {

doliconnect_enqueues();

$current_offset = get_option('gmt_offset');
$tzstring = get_option('timezone_string');
$check_zone_info = true;
// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}
//define( 'MY_TIMEZONE', (get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : date_default_timezone_get() ) );
//date_default_timezone_set( MY_TIMEZONE );
date_default_timezone_set($tzstring); 
$time = current_time( 'timestamp', 1);

$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( isset($_GET['module']) && ($_GET['module'] == 'orders' || $_GET['module'] == 'invoices') && isset($_GET['id']) && isset($_GET['ref']) ) {
$request = "/".esc_attr($_GET['module'])."/".esc_attr($_GET['id'])."?contact_list=0";
$module=esc_attr($_GET['module']);
} else {
$request = "/orders/".doliconnector($current_user, 'fk_order')."?contact_list=0";
$module='orders';
}

//if ( doliconnector($current_user, 'fk_order') > 0 ) {
$object = callDoliApi("GET", $request, null, dolidelay('cart'), true);
//print var_dump($object);
//}

if ( defined("DOLIBUG") ) {

print dolibug();

} elseif ( is_object($order) && $order->value != 1 ) {

print "<div class='card shadow-sm'><div class='card-body'>";
print dolibug(__( "Oops, Order's module is not available", "doliconnect-pro"));
print "</div></div>";

} else {

if ( isset($_GET['validation']) && isset($_GET['id']) & isset($_GET['ref']) ) {

$object = callDoliApi("GET", "/".$module."/".$_GET['id']."?contact_list=0", null, dolidelay('cart', true));

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw ";

if ( $object->billed == 1 && $object->statut > 0 ) {
print "text-success";
}
elseif ( $object->statut > -1 ) {
print "text-warning";
}
else {
print "text-danger";
}

print "' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>"; 

if ( ( !isset($object->id) ) || (doliconnector($current_user, 'fk_soc') != $object->socid) ) {
$return = esc_url(doliconnecturl('doliaccount'));
$order = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, 0);
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_safe_redirect($return);
exit;
}
print "<div class='card shadow-sm' id='cart-form'><div class='card-body'><center><h2>".__( 'Your order has been registered', 'doliconnect-pro' )."</h2>".__( 'Reference', 'doliconnect-pro' ).": ".$_GET['ref']."<br />".__( 'Payment method', 'doliconnect-pro' ).": $object->mode_reglement<br /><br />";
$TTC = doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null);

if ( $object->statut == '1' && !isset($_GET['error']) ) {
if ( $object->mode_reglement_code == 'CHQ') {

$chq = callDoliApi("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$chq->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect-pro' ), $TTC, $bank->proprio, $object->ref ).":</p><p><b>$bank->owner_address</b></p>";

} elseif ($object->mode_reglement_code == 'VIR') {

$vir = callDoliApi("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay('constante'));

$bank = callDoliApi("GET", "/bankaccounts/".$vir->value, null, dolidelay('constante'));

print "<div class='alert alert-info' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect-pro' ), $TTC, $object->ref ).":";
print "<br><b>".__( 'Bank', 'doliconnect-pro' ).": $bank->bank</b>";
print "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { print "<br><b>BIC/SWIFT : $bank->bic</b>";}
print "</p>";

} elseif ($object->mode_reglement_id == '6') {
print "<div class='alert alert-success' role='alert'><p>".__( 'Your payment has been registered', 'doliconnect-pro' );
if (isset($_GET['charge'])) "<br>".__( 'Reference', 'doliconnect-pro' ).": ".$_GET['charge'];
print "</p>";
}
} else {
print "<div class='alert alert-danger' role='alert'><p>".__( 'An error is occurred', 'doliconnect-pro' )."</p>";
}
print "<br /><a href='".doliconnecturl('doliaccount')."?module=orders&id=".$_GET['id']."&ref=".$_GET['ref'];
print "' class='btn btn-primary'>".__( 'See my order', 'doliconnect-pro' )."</a></center></div></div></div>";

} elseif ( isset($_GET['pay']) && ((doliconnector($current_user, 'fk_order_nb_item') > 0 && $object->statut == 0 && !isset($_GET['module']) ) || ( ($_GET['module'] == 'orders' && $object->billed != 1 ) || ($_GET['module'] == 'invoices' && $object->paye != 1) )) && $object->socid == doliconnector($current_user, 'fk_soc') ) {

if ( isset($_POST['source']) && $_POST['source'] == 'validation' && !isset($_GET['info']) && isset($_GET['pay']) && !isset($_GET['validation'])) {

if ($_POST['modepayment']=='2') {
$source="2";
}
elseif ($_POST['modepayment']=='7') {
$source="7";
}
elseif ($_POST['modepayment']=='4') {
$source="4";
}
elseif ($_POST['modepayment']=='src_payplug') {
$source="6";
}
elseif (isset($_POST['token']) || $_POST['modepayment']=='src_newcard' || $_POST['modepayment']=='src_newbank' ) {
if (isset($_POST['token'])){
$source=$_POST['token'];
}else{
$source=$_POST['stripeSource'];
}

if ($_POST['savethesource']=='ok') {
$src = [
'token' => $_POST['stripeSource'],
'default' => $_POST['setasdefault']
];
$addsource = callDoliApi("POST", "/doliconnector/".doliconnector($current_user, 'fk_soc')."paymentmethods", $src, dolidelay('paymentmethods'));
}

}
else{
$source=$_POST['modepayment'];
}

$rdr = [
    'date_commande'  => mktime(),
    'demand_reason_id' => 1,
    'mode_reglement_id' => $source
	];                  
$orderipdate = callDoliApi("PUT", "/".$module."/".$object->id, $rdr, 0);

if ( $object->id > 0 ) {

$successurl = doliconnecturl('dolicart')."?validation&module=".$module."&id=".$object->id;
$returnurl = doliconnecturl('doliaccount')."?module=".$module."&id=".$object->id;

if ( ($_POST['modepayment']!='7' && $_POST['modepayment']!='2' && $_POST['modepayment']!='4' && $_POST['modepayment']!='src_payplug' && $_POST['modepayment']!='src_paypal') && $source ){

$warehouse = callDoliApi("GET", "/doliconnector/constante/DOLICONNECT_ID_WAREHOUSE", null, dolidelay('constante'));
if (!isset($_GET['module'])) {
$vld = [
    'idwarehouse' => $warehouse->value,
    'notrigger' => 0
	];
$validate = callDoliApi("POST", "/orders/".$object->id."/validate", $vld, 0);
}
$src = [
    'source' => "".$source.""
	];
$pay = callDoliApi("POST", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/pay/".$module."/".$object->id, $src, 0);
//print $pay;

if (isset($pay->error)){
$error=$pa->error;
print "<center>".$pay->error->message."</center><br >";
} else {
//print $pay;
$object = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, 0);

$successurl2 = $successurl."&ref=".$object->ref;
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_safe_redirect( $successurl2 );
exit;
}

} elseif ( $_POST['modepayment']=='7' || $_POST['modepayment']=='2'or $_POST['modepayment']=='4' ) {

$warehouse = callDoliApi("GET", "/doliconnector/constante/DOLICONNECT_ID_WAREHOUSE", null, dolidelay('constante'));
if (!isset($_GET['module'])) {
$vld = [
    'idwarehouse' => $warehouse->value,
    'notrigger' => 0
	];
$validate = callDoliApi("POST", "/orders/".$object->id."/validate", $vld, 0);
}
$object = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, 0);

$successurl2 = $successurl."&ref=".$object->ref;

$order = callDoliApi("GET", "/".$module."/".$object->id."?contact_list=0", null, 0);
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_safe_redirect($successurl2);
exit;
}
elseif ($_POST['modepayment'] == 'src_payplug')  {

} else {
if ($object->id <=0 || $error || !$source) {
print "<center><h4 class='alert-heading'>".__( 'Oops', 'doliconnect-pro' )."</h4><p>".__( 'An error is occured. Please retry!', 'doliconnect-pro' )."</p>";
print "<br /><a href='".doliconnecturl('dolicart')."' class='btn btn-primary'>Retourner sur la page de paiement</a></center>";
}
}
}                                  
} elseif ( !is_object($object) && empty($object->lines) ) {
//$order = callDoliApi("GET", "/".$module."/".$object->id, null, 0);
//$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0);
//wp_safe_redirect(doliconnecturl('dolicart'));
//exit;
}

//header('Refresh: 300; URL='.esc_url(get_permalink()).'');

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

print "<div class='row'><div class='col-12 col-md-4  d-none d-sm-none d-md-block'>";
print doliminicart($object);
print "<div class='card'><div class='card-header'>".__( 'Contacts', 'doliconnect-pro' );
if ( !isset($object->resteapayer) && $object->statut == 0 ) { print " <small>(<a href='".doliconnecturl('dolicart')."?info' >".__( 'update', 'doliconnect-pro' )."</a>)</small>"; }
print "</div><ul class='list-group list-group-flush'>";

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {

foreach ($object->contacts_ids as $contact) {
if ('BILLING' == $contact->code) {
print "<li class='list-group-item'><h6>".__( 'Billing address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
} else {
print "<li class='list-group-item'><h6>".__( 'Billing address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
}
if ('SHIPPING' == $contact->code) {
print "<li class='list-group-item'><h6>".__( 'Shipping address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
} else {
print "<li class='list-group-item'><h6>".__( 'Shipping address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
}
}

} else {
print "<li class='list-group-item'><h6>".__( 'Billing and shipping address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print doliaddress($thirdparty, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print "</small></li>";
}

if ( ! empty($object->note_public) ) {
print "<li class='list-group-item'><h6>".__( 'Message', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print $object->note_public;
print "</small></li>";
}

print "</ul></div></div><div class='col-12 col-md-8'>";

$paymentmethods = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods", null, dolidelay('paymentmethods',  esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $listsource;

if ( current_user_can( 'administrator' ) && get_option('doliconnectbeta') =='1'  ) {
$paymentintent = callDoliApi("GET", "/doliconnector/paymentintent/".substr($module, 0, -1)."/".$object->id, null, dolidelay('paymentmethods',  esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print $listsource;
print dolipaymentmodes($paymentintent, $paymentmethods, $object, doliconnecturl('dolicart')."?pay", doliconnecturl('dolicart')."?pay");
} else {
if ( isset($_GET["ref"]) && $object->statut != 0 ) { $ref = $object->ref; } else { $ref= 'commande #'.$object->id; }
if ( isset($object->resteapayer) ) { 
$montant=$object->resteapayer;
} else { 
$montant=$object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc;
}
doligateway($paymentmethods, $ref, $montant, $object->multicurrency_code, doliconnecturl('dolicart')."?pay", 'full');
print doliloading('paymentmodes');
}

print "</div></div>";

print "<small><div class='float-left'>";
print dolirefresh( $request, doliconnecturl('dolicart')."?pay", dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";

} elseif ( isset($_GET['info']) && doliconnector($current_user, 'fk_order_nb_item') > 0 && $object->socid == doliconnector($current_user, 'fk_soc')) {

if ( isset($_GET['info']) && !isset($_GET['pay']) && !isset($_GET['validation']) && isset($_POST['update_thirdparty']) && $_POST['update_thirdparty'] == 'validation' ) {

$thirdparty=$_POST['contact'][''.doliconnector($current_user, 'fk_soc').''];
$ID = $current_user->ID;
if ( $thirdparty['morphy'] == 'phy' ) {
$thirdparty['name'] = ucfirst(strtolower($thirdparty['firstname']))." ".strtoupper($thirdparty['lastname']);
} 
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($thirdparty['email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => sanitize_user($thirdparty['name'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($thirdparty['firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($thirdparty['lastname']))));
wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
wp_update_user( array( 'ID' => $ID, 'user_url' => sanitize_textarea_field($thirdparty['url'])));
update_user_meta( $ID, 'civility_id', sanitize_text_field($thirdparty['civility_id']));
update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
if ( $thirdparty['morphy'] == 'mor' ) { update_user_meta( $ID, 'billing_company', sanitize_text_field($thirdparty['name'])); }
update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);

do_action('wp_dolibarr_sync', $thirdparty);
                                   
} elseif ( isset($_GET['info']) && isset($_POST['info']) && $_POST['info'] == 'validation' && !isset($_GET['pay']) && !isset($_GET['validation']) ) {

if ($_POST['contact_shipping']) {
$order_shipping= callDoliApi("POST", "/".$module."/".$object->id."/contact/".$_POST['contact_shipping']."/SHIPPING", null, dolidelay('order', true));
}

if ( isset($_POST['note_public']) && $_POST['note_public'] != $object->note_public) {
$data = [
    'note_public' => $_POST['note_public']
	];
$object = callDoliApi("PUT", "/".$module."/".$object->id, $data, dolidelay('order', true));
}

wp_safe_redirect(doliconnecturl('dolicart').'?pay');
exit;
                                   
} elseif ( !$object->id > 0 && $object->lines == null ) {

wp_safe_redirect(doliconnecturl('dolicart'));
exit;

}
//header('Refresh: 300; URL='.esc_url(get_permalink()).'');
$ID = $current_user->ID;

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

print "<div class='row' id='informations-form'><div class='col-12 col-md-4 d-none d-sm-none d-md-block'>";
print doliminicart($object);
print "</div><div class='col-12 col-md-8'>";
  
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 

print "<div class='modal fade' id='updatethirdparty' tabindex='-1' role='dialog' aria-labelledby='updatethirdpartyTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>
<h5 class='modal-title' id='updatethirdpartyTitle'>".__( 'Billing address', 'doliconnect-pro' )."</h5><button id='Closeupdatethirdparty-form' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
</div><div id='updatethirdparty-form'>";

print "<form class='was-validated' role='form' action='".doliconnecturl('dolicart')."?info' name='updatethirdparty-form' method='post'>"; 

print dolimodalloaderscript('updatethirdparty-form');

print doliconnectuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'contact');

print "</div>".doliloading('updatethirdparty-form');

print "<div id='Footerupdatethirdparty-form' class='modal-footer'><button name='update_thirdparty' value='validation' class='btn btn-warning btn-block' type='submit'><b>".__( 'Update', 'doliconnect-pro' )."</b></button></form></div>
</div></div></div>";

print "<form role='form' action='".doliconnecturl('dolicart')."?info' id ='doliconnect-infoscartform' method='post'>"; //class='was-validated'

print doliloaderscript('doliconnect-infoscartform');

print "<div class='card'><ul class='list-group list-group-flush'>";

if ( doliversion('10.0.0') ) {
print "<li class='list-group-item'><h6>".__( 'Billing address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
} else {
print "<li class='list-group-item'><h6>".__( 'Billing and shipping address', 'doliconnect-pro' )."</h6><small class='text-muted'>";
}
print '<div class="custom-control custom-radio">
<input type="radio" id="billing0" name="contact_billing" class="custom-control-input" value="" checked>
<label class="custom-control-label" for="billing0">'.doliaddress($thirdparty).'</label>
</div>';

print '<div class="float-right"><button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#updatethirdparty"><center>'.__( 'Update', 'doliconnect-pro' ).'</center></button></div>';
print "</small></li>";

if ( doliversion('10.0.0') ) {

print "<li class='list-group-item'><h6>".__( 'Shipping address', 'doliconnect-pro' )."</h6><small class='text-muted'>";

print '<div class="custom-control custom-radio">
<input type="radio" id="shipping0" name="contact_shipping" class="custom-control-input" value="" checked>
<label class="custom-control-label" for="shipping0">'.__( "Same address that billing", "doliconnect-pro").'</label>
</div>';

$listcontact = callDoliApi("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1", null, dolidelay('contact', true));

if (!empty($object->contacts_ids) && is_array($object->contacts_ids)) {

foreach ($object->contacts_ids as $contact) {
if ('SHIPPING' == $contact->code) {
$contactshipping = $contact->id;
}
}

}
if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) {
print '<div class="custom-control custom-radio"><input type="radio" id="customRadio2" name="contact_shipping" class="custom-control-input" value="'.$contact->id.'" ';
if ( !empty($contact->default) || $contactshipping == $contact->id ) { print "checked"; }
print '><label class="custom-control-label" for="customRadio2">';
print dolicontact($contact->id, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
print '</label></div>';
}
}
print "</small></li>";

} elseif ( current_user_can( 'administrator' ) ) {
print "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".sprintf( esc_html__( "Add shipping contact needs Dolibarr %s but your version is %s", 'doliconnect-pro'), '10.0.0',$versiondoli[0])."</b></li>";
}

print "<li class='list-group-item'><h6>".__( 'Message', 'doliconnect-pro' )."</h6><small class='text-muted'>";
print "<textarea class='form-control' id='note_public' name='note_public' rows='3' placeholder='".__( 'Enter a message here that you want to send us about your order', 'doliconnect-pro' )."'>".$object->note_public."</textarea>";
print "</small></li></ul>";

print "<div class='card-body'><input type='hidden' name='info' value='validation'><input type='hidden' name='dolicart' value='validation'><center><button class='btn btn-warning btn-block' type='submit'><b>".__( 'Validate', 'doliconnect-pro' )."</b></button></center></div></div></form>";
print "</div></div>";

print "<small><div class='float-left'>";
print dolirefresh( $request, doliconnecturl('dolicart')."?info", dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('ISSUE');
print "</div></small>";

} else {

print "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

if ( isset($_POST['dolicart']) && $_POST['dolicart'] == 'validation' && !isset($_GET['user']) && !isset($_GET['pay']) && !isset($_GET['validation']) && $object->lines != null ) {
wp_safe_redirect(doliconnecturl('dolicart').'?info');
exit;                                   
} elseif ( isset($_POST['dolicart']) && $_POST['dolicart'] == 'purge' ) {
$orderdelete = callDoliApi("DELETE", "/".$module."/".doliconnector($current_user, 'fk_order'), null);
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector'), true);
if (1==1) {
doliconnector($current_user, 'fk_order', true);
wp_safe_redirect(doliconnecturl('dolicart'));
exit;
} else {
print "<div class='alert alert-warning' role='alert'><p><strong>".__( 'Oops!', 'doliconnect-pro' )."</strong> ".__( 'An error is occured. Please contact us!', 'doliconnect-pro' )."</p></div>"; 
}
}
 
if ( isset($_POST['updateorderproduct']) ) {
foreach ( $_POST['updateorderproduct'] as $productupdate ) {
$result = addtodolibasket($productupdate['product'], $productupdate['qty'], $productupdate['price'], $productupdate['remise_percent'], $productupdate['date_start'], $productupdate['date_end']);
//print var_dump($_POST['updateorderproduct']);
if (1==1) {
if (doliconnector($current_user, 'fk_order') > 0) {
$object = callDoliApi("GET", $request, null, dolidelay('cart'), true);
//print $object;
}
//wp_safe_redirect(esc_url(get_permalink()));
//exit;
} else {
print "<div class='alert alert-warning' role='alert'><p><strong>".__( 'Oops!', 'doliconnect-pro' )."</strong> ".__( 'An error is occured. Please contact us!', 'doliconnect-pro' )."</p></div>"; 
}
}
}



if ( isset($object) && is_object($object) ) {
$timeout=$object->date_modification-current_time('timestamp',1)+1200;
//print "<script>";
//var tmp=<?php print ($timeout)*10;
// 
//var chrono=setInterval(function (){
//     min=Math.floor(tmp/600);
//     sec=Math.floor((tmp-min*600)/10);
//     dse=tmp-((min*60)+sec)*10;
//     tmp--;
//     jQuery('#duration').text(min+'mn '+sec+'sec');
//},100);
//print "</script>";
//header('Refresh: 120; URL='.esc_url(get_permalink()).'');
//header('Refresh: '.$timeout.'; URL='.esc_url(get_permalink()).'');
//print date_i18n('d/m/Y H:i', $object[date_modification]);
}

$stock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));

if ( doliconnector($current_user, 'fk_order')>0 && $object->lines != null ) {  //&& $timeout>'0'                                                                                         
//print "<div id='timer' class='text-center'><small>".sprintf( esc_html__('Your basket #%s is reserved for', 'doliconnect-pro'), doliconnector($current_user, 'fk_order'))." <span class='duration'></span></small></div>";
}

print "<form role='form' action='".doliconnecturl('dolicart')."' id='doliconnect-basecartform' method='post'>";

print doliloaderscript('doliconnect-basecartform');

print "<div class='card shadow-sm' id='cart-form'><ul class='list-group list-group-flush'>";

print doliline($object, 'cart');

if ( isset($object) && is_object($object) && (doliconnector($current_user, 'fk_soc') == $object->socid) ) {
print "<li class='list-group-item list-group-item-info'>";
print dolitotal($object);
print "</li>";
}

print "</ul>";

if ( get_option('dolishop') || (!get_option('dolishop') && isset($object) && $object->lines != null) ) {
print "<div class='card-body'><div class='row'>";
if ( get_option('dolishop') ) {
print "<div class='col-12 col-md'><a href='".doliconnecturl('dolishop')."' class='btn btn-outline-info w-100' role='button' aria-pressed='true'><b>".__( 'Continue shopping', 'doliconnect-pro')."</b></a></div>";
} 
if ( isset($object) && is_object($object) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) { 
if ( $object->lines != null && $object->statut == 0 ) {
print "<div class='col-12 col-md'><button type='submit' name='dolicart' value='purge' class='btn btn-outline-secondary w-100' role='button' aria-pressed='true'><b>".__( 'Empty the basket', 'doliconnect-pro')."</b></button></div>";
}
if ( $object->lines != null ) {
print "<div class='col-12 col-md'><button type='submit' name='dolicart' value='validation' class='btn btn-warning w-100' role='button' aria-pressed='true'><b>".__( 'Process', 'doliconnect-pro')."</b></button></div>";
} 
}
print "</div>";
//print "<ul class='list-group list-group-horizontal-lg mw-100'>
//<a href='".doliconnecturl('dolishop')."' class='list-group-item list-group-item-info list-group-item-action' role='button' aria-pressed='true'><b>".__( 'Continue shopping', 'doliconnect-pro')."</b></a>
//<button type='button' type='submit' name='dolicart' value='purge' class='list-group-item list-group-item-secondary list-group-item-action' role='button' aria-pressed='true'><b>".__( 'Empty the basket', 'doliconnect-pro')."</b></button>
//<button type='button' type='submit' name='dolicart' value='validation' class='list-group-item list-group-item-warning list-group-item-action' role='button' aria-pressed='true'><b>".__( 'Process', 'doliconnect-pro')."</b></button>
//</ul>";
print "</div>";
}

print "</form></div>"; 

print "<small><div class='float-left'>";
print dolirefresh($request, doliconnecturl('dolicart'), dolidelay('cart'));
print "</div><div class='float-right'>";
print dolihelp('COM');
print "</div></small>";
}
}

} else {

return $content;

}

}

add_filter( 'the_content', 'dolicart_display');

//}
//add_action( 'plugins_loaded', 'doliconnectpro_run', 10, 0 );

// ********************************************************

function doliconnect_privacy($arg) {
global $current_user;

if ( is_user_logged_in() && get_option('doliconnectbeta') == '2' && ( $current_user->$privacy < get_the_modified_date( 'U', get_option( 'wp_page_for_privacy_policy' ))) ) {  

doliconnect_enqueues();

print "<script>";
?>
function DoliconnectShowPrivacyDiv() {
jQuery('#DoliconnectPrivacyModal').modal('show');
}

window.onload=DoliconnectShowPrivacyDiv;
<?php
print "</script>";

print '<div id="DoliconnectPrivacyModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-show="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title" id="exampleModalLabel">Confidentialite - V'.get_the_modified_date( $d, get_option( 'wp_page_for_privacy_policy' ) ).'</h5>';
//print '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
print '</div><div class="bg-light text-dark" data-spy="scroll" data-target="#navbar-example2" data-offset="0" style="overflow: auto; height:55vh;">';
print apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' ))); 
print '</div>    
      <div class="modal-footer">
        <button type="button" class="btn btn-success" >'.__( 'I approve', 'doliconnect-pro' ).'</button>
        <a href="'.wp_logout_url( get_permalink() ).'" type="button" class="btn btn-danger">'.__( 'I refuse', 'doliconnect-pro' ).'</a>
      </div>
    </div>
  </div>
</div>';
}

if ( ( !is_user_logged_in() && !empty(get_option('doliconnectrestrict')) ) || (!is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict')) )) {
print "<script>";
?>
function DoliconnectShowLoginDiv() {
jQuery('#DoliconnectLogin').modal('show');
}

window.onload=DoliconnectShowLoginDiv;
<?php
print "</script>";
}

}
add_action( 'wp_footer', 'doliconnect_privacy', 10, 1);

function doliconnect_langs($arg) {
global $current_user;

if (function_exists('pll_the_languages')) {       

print '<div class="modal fade" id="DoliconnectSelectLang" tabindex="-1" role="dialog" aria-labelledby="DoliconnectSelectLangLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
<div class="modal-content border-0"><div class="modal-header border-0">
<h5 class="modal-title" id="DoliconnectSelectLangLabel">'.__('Change language', 'doliconnect-pro').'</h5><button id="closemodalSelectLang" type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span></button></div>';
 
print '<script>';
?>
function loadingSelectLangModal() {
jQuery("#closemodalSelectLang").hide();
jQuery("#SelectLangmodal-form").hide();
jQuery("#loadingSelectLang").show();  
}
<?php
print '</script>';

print '<div class="modal-body"><div class="card" id="SelectLangmodal-form"><ul class="list-group list-group-flush">';
$translations = pll_the_languages( array( 'raw' => 1 ) );
foreach ($translations as $key => $value) {
print "<a href='".$value['url']."?".$_SERVER["QUERY_STRING"]."' onclick='loadingSelectLangModal()' class='list-group-item list-group-item-action list-group-item-light'>
<img src='".$value['flag']."' class='img-fluid' alt='".$value['name']."'> ".$value['name'];
if ( $value['current_lang'] == true ) { print " <i class='fas fa-language fa-fw'></i>"; }
print "</a>";
}      

print '</ul></div>
<div id="loadingSelectLang" style="display:none"><br><br><br><center><div class="align-middle"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><h4>'.__('Loading', 'doliconnect-pro').'</h4></div></center><br><br><br></div>
</div></div></div></div>';

}    

}
add_action( 'wp_footer', 'doliconnect_langs', 10, 1);

function doliconnect_restrict_display($content) {
if ( ! empty(get_option('doliconnectrestrict')) && !is_user_logged_in() ) {
return "private site";
} else {
return $content;
}

}

add_filter( 'the_content', 'doliconnect_restrict_display', 10, 1);

function doliconnect_modal() {
global $current_user;
$year = strftime("%Y", current_time( 'timestamp', 1));

if ( !is_user_logged_in() && (get_option('doliloginmodal') == '1' || !empty(get_option('doliconnectrestrict'))) ) {

doliconnect_enqueues();

do_action( 'login_head' );

print "<div class='modal fade' id='DoliconnectLogin' tabindex='-1' role='dialog' aria-labelledby='DoliconnectLoginTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false' ";
if ( ! empty(get_option('doliconnectrestrict')) ) {
if ( !empty( get_background_color() )) {
print " style='background-color:#".get_background_color()."' ";
} else {
print " style='background-color:#cccccc' ";
}
}
print "><div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>";

if ( empty(get_option('doliconnectrestrict')) ) {
print "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Welcome', 'doliconnect-pro' )."</h5><button id='Closeloginmodal-form' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
} else {
print "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Access restricted to users', 'doliconnect-pro' )."</h5>";
}

print "</div><div class='modal-body'><div id='loginmodal-form'>";
print "<b>".get_option('doliaccountinfo')."</b>";

if ( ! function_exists('dolikiosk') || ( function_exists('dolikiosk') && empty(dolikiosk())) ) {
print socialconnect ( get_permalink() );
}

if ( function_exists('secupress_get_module_option') && secupress_get_module_option('move-login_slug-login', null, 'users-login' )) {
$login_url=site_url()."/".secupress_get_module_option('move-login_slug-login', null, 'users-login' ); 
}else{
$login_url=site_url()."/wp-login.php"; }

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
$redirect_to=doliconnecturl('doliaccount');
} else {
$redirect_to=get_permalink();
}

print "<form name='loginmodal-form' action='$login_url' method='post' class='was-validated'>";

print dolimodalloaderscript('loginmodal-form');

print "<div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_login' type='email' placeholder='".__( 'Email', 'doliconnect-pro' )."' name='log' value='' required>";
print "</div></div><div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='user_pass' type='password' placeholder='".__( 'Password', 'doliconnect-pro' )."' name='pwd' value ='' required>";
print "</div></div>";

do_action( 'login_form' );

if ( get_site_option('doliconnect_mode') == 'one' && function_exists('switch_to_blog') ) {
switch_to_blog(1);
} 
print "<div><div class='float-left'><small>";
if (((!is_multisite() && get_option( 'users_can_register' )) || (get_option('users_can_register') == '1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
print "<a href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect-pro' )."'>".__( 'Create an account', 'doliconnect-pro' )."</a>";
}

print "</div><div class='float-right'><a href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect-pro' )."'>".__( 'Forgot password?', 'doliconnect-pro' )."</a></small></div></div>"; 
if (get_site_option('doliconnect_mode')=='one') {
restore_current_blog();
}

print "<input type='hidden' value='$redirect_to' name='redirect_to'></div>";

print "".doliloading('loginmodal-form');

print "</div><div id='Footerloginmodal-form' class='modal-footer'><button id='submit' class='btn btn-block btn-primary' type='submit' name='submit' value='Submit'";
print "><b>".__( 'Sign in', 'doliconnect-pro' )."</b></button></form></div>";
print "</div></div></div>";

//if( !array_key_exists( 'login_footer' , $GLOBALS['wp_filter']) ) { 
do_action( 'login_footer' );
//}

}

// modal for CGU
if (get_option('dolicgvcgu')){
print "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'><div class='modal-dialog modal-dialog-centered modal-lg' role='document'><div class='modal-content'><div class='modal-header'>
<h5 class='modal-title' id='exampleModalLongTitle'>Conditions Generales d'Utilisation</h5>
<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
<div class='modal-body'>
en cours d'integration
</div></div></div></div>";}
}
add_action( 'wp_footer', 'doliconnect_modal' );
// ********************************************************
function socialconnect( $url ) {
$connect = null;

include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/lib/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'doliconnect-pro/lib/hybridauth/src/config.php');

$hybridauth = new Hybridauth\Hybridauth($config);
$adapters = $hybridauth->getConnectedAdapters();

foreach ($hybridauth->getProviders() as $name) {

if (!isset($adapters[$name])) {
$connect .= "<a href='".doliconnecturl('doliaccount')."?provider=".$name."' onclick='loadingLoginModal()' role='button' class='btn btn-block btn-outline-dark' title='".__( 'Sign in with', 'doliconnect-pro' )." ".$name."'><b><i class='fab fa-".strtolower($name)." fa-lg float-left'></i> ".__( 'Sign in with', 'doliconnect-pro' )." ".$name."</b></a>";
}
}
if (!empty($hybridauth->getProviders())) {
$connect .= '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect-pro' ).'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
}

return $connect;
}
// ********************************************************

function doligateway($listsource, $ref, $total, $currency, $redirect, $mode) {
global $current_user;
$currency=strtolower($currency);

doliconnect_enqueues();

print "<script src='https://js.stripe.com/v3/'></script><script>";
if ( $listsource->code_account != null ) {
?>
var stripe = Stripe('<?php print $listsource->publishable_key; ?>',{
    stripeAccount: '<?php print $listsource->code_account; ?>'
    });
<?php
} else {
?>
var stripe = Stripe('<?php print $listsource->publishable_key; ?>');
<?php
}
?> 

var mode = '<?php print $mode; ?>';
var montant = <?php print $total*100; ?>;
var monnaie = '<?php print $currency; ?>';
var ref = '<?php print $ref; ?>';
var lang = '<?php print $lang; ?>';
var courriel = '<?php print $current_user->user_email; ?>';
var comcountrycode = '<?php print $listsource->com_countrycode; ?>';
var cuscountrycode = '<?php print $listsource->cus_countrycode; ?>';
if (montant >= '100'){
var paymentRequest = stripe.paymentRequest({
  country: comcountrycode,
  currency: monnaie,
  total: {
    label: ref,
    amount: montant,     
  },
});
}

function ShowHideDiv() {

var style = {
  base: {   
    lineHeight: '25px',
    fontSize: '14px',

  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};
 
var form = document.getElementById('gateway-form');

var CdDbt = document.getElementById("CdDbt");
var CardForm = document.getElementById("CardForm"); 
var CardFormButton = document.getElementById("CardFormButton"); 
if (CardForm){
CardForm.style.display = CdDbt.checked ? "block" : "none";
CardFormButton.style.display = CdDbt.checked ? "block" : "none";
if (document.getElementById("CdDbt").checked){
document.getElementById("dvDelete").style.display = "none";
document.getElementById("SaveFormButton").style.display = "block";
} else if (document.getElementById("src_vir") && document.getElementById("src_vir").checked){
document.getElementById("dvDelete").style.display = "none";
document.getElementById("SaveFormButton").style.display = "block"; 
} else if (document.getElementById("src_chq") && document.getElementById("src_chq").checked){
document.getElementById("dvDelete").style.display = "none"; 
document.getElementById("SaveFormButton").style.display = "block";
} else{
document.getElementById("dvDelete").style.display = "block";
document.getElementById("SaveFormButton").style.display = "none";
}
} 

var BkDbt = document.getElementById("BkDbt");
var BankForm = document.getElementById("BankForm");
var BankFormButton = document.getElementById("BankFormButton");
if (BankForm){
BankForm.style.display = BkDbt.checked ? "block" : "none"; 
BankFormButton.style.display = BkDbt.checked ? "block" : "none";

if (CdDbt.checked || BkDbt.checked){
document.getElementById("dvDelete").style.display = "none"; 
document.getElementById("SaveFormButton").style.display = "block";
}else{
document.getElementById("dvDelete").style.display = "block";
document.getElementById("SaveFormButton").style.display = "none";
}
}

var ownerinf = document.getElementById('card-owner').value;
var ownerInfo = {
  owner: {
      name: ownerinf,
  },
};

var VrDbt = document.getElementById("src_vir");
var CqDbt = document.getElementById("src_chq");

var errorMessageIban = document.getElementById('iban-errors');
var bankName = document.getElementById('bank-name');

var elements = stripe.elements();

if (CardForm){
document.getElementById("card-owner").required = false;
var card = elements.create('card', {style: style});
card.mount('#card-element');
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});
}

var DctForm = document.getElementById("DctForm");
if (DctForm){
if (src_dct.checked){
document.getElementById("dvDelete").style.display = "none";
document.getElementById("DiscountFormButton").style.display = "block"; 
document.getElementById("DctAddForm").style.display = "block";
} else {
document.getElementById("DctAddForm").style.display = "none";
document.getElementById("DiscountFormButton").style.display = "none"; 
}
}

var PaypalForm = document.getElementById("PaypalForm");
if (PaypalForm){
if (src_paypal.checked){
document.getElementById("dvDelete").style.display = "none";
}
}

var ChqForm = document.getElementById("ChqForm");
if (ChqForm){
if (src_chq.checked){
document.getElementById("SaveFormButton").style.display = "none";
document.getElementById("dvDelete").style.display = "none";
}
}

var VirForm = document.getElementById("VirForm");
if (VirForm){
if (src_vir.checked){
document.getElementById("SaveFormButton").style.display = "none";
document.getElementById("dvDelete").style.display = "none";
}
}

if (BankForm){
document.getElementById("iban-owner").required = false;
var iban = elements.create('iban', {
  style: style,
  supportedCountries: ['SEPA'],
  placeholderCountry: cuscountrycode,
});
iban.mount('#iban-element');
iban.addEventListener('change', function(event) {
  // Handle real-time validation errors from the iban Element.
  if (event.error) {
    errorMessageIban.textContent = event.error.message;
    errorMessageIban.classList.add('visible');
  } else {
    errorMessageIban.classList.remove('visible');
  }

  // Display bank name corresponding to IBAN, if available.
  if (event.bankName) {
    bankName.textContent = event.bankName;
    bankName.classList.add('visible');
  } else {
    bankName.classList.remove('visible');
  }
});
} 

form.addEventListener('submit', function(event) { 

if (CdDbt.checked) {
event.preventDefault();
document.getElementById("card-owner").required = true;
if ('' != document.getElementById('card-owner').value){  
stripe.createSource(card, ownerInfo).then(function(result) {
if (result.error) {
var errorElement = document.getElementById('card-errors');
errorElement.textContent = result.error.message;
    } else {
stripeSourceHandler(result.source);
    }
  }); }
} else if ((!CdDbt.checked && !BkDbt) || (!CdDbt.checked && !BkDbt.checked)) {
jQuery(window).scrollTop(0);
jQuery('#else').hide();
jQuery('#closemodalonlinepay').hide(); 
jQuery('#payment-form').hide();
jQuery('#gateway-form').hide();  
jQuery('#buttontopay').hide();            
jQuery('#button-source-payment').hide();
jQuery('#DoliconnectLoadingModal').modal('show');  
jQuery('#doliloading-paymentmodes').show();
console.log("submit");
form.submit();
} else if (BkDbt.checked) {
event.preventDefault();
document.getElementById("iban-owner").required = true;
  var sourceData = {
    type: 'sepa_debit',
    currency: 'EUR',
    owner: {
      name: document.querySelector('input[name="iban-owner"]').value,
      email: courriel,
    },
    mandate: {
      // Automatically send a mandate notification email to your customer
      // once the source is charged.
      notification_method: courriel,
    }
  }; 
if ('' != document.getElementById('iban-owner').value){  
stripe.createSource(iban, sourceData).then(function(result) {
    if (result.error) {
      // Inform the customer that there was an error.
      errorMessageIban.textContent = result.error.message;
      errorMessageIban.classList.add('visible');
      stopLoading();
    } else {
      // Send the Source to your server to create a charge.
      errorMessageIban.classList.remove('visible');
      stripeSourceHandler(result.source);
    }
});}
} 
});
}

function stripeSourceHandler(source) {

var form = document.getElementById('gateway-form');
var hiddenInput = document.createElement('input');
hiddenInput.setAttribute('type', 'hidden');
hiddenInput.setAttribute('name', 'stripeSource');
hiddenInput.setAttribute('value', source.id);
form.appendChild(hiddenInput);
jQuery(window).scrollTop(0);
jQuery('#else').hide();
jQuery('#closemodalonlinepay').hide(); 
jQuery('#payment-form').hide();
jQuery('#gateway-form').hide(); 
jQuery('#buttontopay').hide();            
jQuery('#button-source-payment').hide();
jQuery('#DoliconnectLoadingModal').modal('show');  
jQuery('#doliloading-paymentmodes').show();
console.log("submit");
form.submit();
}

window.onload=ShowHideDiv; 

var elements = stripe.elements();
var prButton = elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
});

paymentRequest.canMakePayment().then(function(result) {
  if (result) {
jQuery('#else').show();
prButton.mount('#payment-request-button');
  } else {
document.getElementById('payment-request-button').style.display = 'none';
  }
});

paymentRequest.on('token', function(ev) {

ev.complete('success');
var form = document.getElementById('gateway-form');
var hiddenInput = document.createElement('input');
hiddenInput.setAttribute('type', 'hidden');
hiddenInput.setAttribute('name', 'token');
hiddenInput.setAttribute('value', ev.token.id);
form.appendChild(hiddenInput);
$(document).ready(function(){
$(window).scrollTop(0);
});
jQuery('#else').hide();
jQuery('#closemodalonlinepay').hide(); 
jQuery('#payment-form').hide();
jQuery('#gateway-form').hide(); 
jQuery('#buttontopay').hide(); 
jQuery('#payment-request-button').hide();            
jQuery('#button-source-payment').hide();
jQuery('#doliloading-paymentmodes').show();
console.log("submit");
form.submit();
});

<?php
print '</script>';
print "<script>";
?>
(function() {
  'use strict';

  window.addEventListener('load', function() {
    var form = document.getElementById('gateway-form');
    form.addEventListener('submit', function(event) {
      if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  }, false);
})();
<?php
print "</script>";

print "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>
<div id='else' style='display: none' ><br><div style='display:inline-block;width:46%;float:left'><hr width='90%' /></div><div style='display:inline-block;width: 8%;text-align: center;vertical-align:90%'><small class='text-muted'>".__( 'or', 'doliconnect-pro' )."</small></div><div style='display:inline-block;width:46%;float:right' ><hr width='90%'/></div><br></div>";
print "<form role='form' action='$redirect' id='gateway-form' method='post' novalidate>";
print "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ($mode=='manage' && ($listsource->discount!=0 || $listsource->discount_product!=null)) {
print "<li id='DctForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_dct' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_dct' ";
//if ($listsource["discount"]>0){print " checked ";}
print " ><label class='custom-control-label w-100' for='src_dct'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print "<center><i class='fas fa-piggy-bank fa-3x fa-fw'></i></center>";
print "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>";
if ($listsource->discount>=0) {
print __( 'Credit of', 'doliconnect-pro' );
} else {
print __( 'Debit of', 'doliconnect-pro' );
}
print " ".doliprice($listsource->discount)."</h6><small class='text-muted'>".__( 'Automatic use', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
//if (get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
print '<li class="list-group-item list-group-item-secondary" id="DctAddForm" style="display: none">';
print 'Prochainement, vous pourrez recharger votre compte!';
print '<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">$</span>
  </div>
  <input type="num" class="form-control" aria-label="Amount (to the nearest dollar)">
  <div class="input-group-append">
    <span class="input-group-text">.00</span>
  </div>
</div>';
print '</li>';
//}
} 

class myCounter implements Countable {
	public function count() {
		static $count = 0;
		return ++$count;
	}
}
 
$counter = new myCounter;

//SAVED SOURCES
if ( $listsource->paymentmethods != null ) {  
foreach ( $listsource->paymentmethods as $src ) {
if (strpos($src->id, 'pm') === false) {                                                                                                                       
print "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$src->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='$src->id' ";
if ( date('Y/n') >= $src->expiration && !empty($object) && !empty($src->expiration) ) { print " disabled "; }
elseif ( $src->default_source == '1' ) { print " checked "; }
print " ><label class='custom-control-label w-100' for='$src->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print '<center><i ';
if ( $src->type == 'sepa_debit' ) {
print 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $src->brand == 'visa' ) { print 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $src->brand == 'mastercard' ) { print 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $src->brand == 'amex' ) { print 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else {print 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
print '></i></center>';
print '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $src->type == 'sepa_debit' ) {
print __( 'Account', 'doliconnect-pro' ).' '.$src->reference.'<small> <a href="'.$src->mandate_url.'" title="'.__( 'Mandate', 'doliconnect-pro' ).' '.$src->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
print __( 'Card', 'doliconnect-pro' ).' '.$src->reference;
}
if ( $src->default_source == '1' ) { print " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i><input type='hidden' name='defaultsource' value='$src->id'>"; }
print '</h6>';
print "<small class='text-muted'>".$src->holder."</small></div>";
print "<div class='d-none d-sm-block col-2 align-middle text-right'><img src='".plugins_url('doliconnect/images/flag/'.strtolower($src->country).'.png')."' class='img-fluid' alt='$src->country'></div>";
print "</div></label></div></li>";
}
} }

if ( count($counter) < 5 && $listsource->code_client!=null && $listsource->card == 1 ) {      
print "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='CdDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newcard' ";
if ( $listsource->paymentmethods == null ) {print " checked";}
print " ><label class='custom-control-label w-100' for='CdDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print "<center><i class='fas fa-credit-card fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Credit card', 'doliconnect-pro' )."</h6><small class='text-muted'>Visa, MasterCard, Amex...</small></div></div>";
print "</label></div></li>";

print '<li class="list-group-item list-group-item-secondary" id="CardForm" style="display: none">';
print '<input id="card-owner" name="card-owner" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your credit card', 'doliconnect-pro' ).'</div>
<label for="card-element"></label>
<div  class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
print '</li>';
}

//NEW SEPA DIRECT DEBIT
if ( count($counter) < 5 && $listsource->code_client != null && !empty($listsource->sepa_direct_debit) ) {   
print "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='BkDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newbank' ";
//if ($listsource["sources"]==null) {print " checked";}
print " ><label class='custom-control-label w-100' for='BkDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print "<center><i class='fas fa-university fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank levy', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Via SEPA Direct Debit', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
print '<li class="list-group-item list-group-item-secondary" id="BankForm" style="display: none">';
print "<p class='text-justify'>";
$blogname=get_bloginfo('name');
print '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect-pro' ), $blogname).'</small>';
print "</p>";
print '<input id="iban-owner" name="iban-owner" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your bank account', 'doliconnect-pro' ).'</div>
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- A Stripe Element will be inserted here. --></div>';
//print '<div id="bank-name"></div>'';
print '<div id="iban-errors" role="alert"></div>';
print '</li>';
}

if ( $mode != 'manage' ) {
if ( isset($listsource->PAYPAL) && $listsource->PAYPAL != null && get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' ) ) {
print "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_paypal' ";
print " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print "<center><i class='fab fa-cc-paypal fa-3x fa-fw'></i></center>";
print "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Pay with your Paypal account', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
}

if ( isset($listsource->RIB) && $listsource->RIB != null ) {
print "<li id='VirForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_vir' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='2' ";
if ( $listsource->paymentmethods == null && $listsource->card != 1 ) { print " checked"; }
print " ><label class='custom-control-label w-100' for='src_vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
print "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank Transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
}

if ( isset($listsource->CHQ) && $listsource->CHQ != null ) {
print "<li id='ChqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_chq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='7' ";
if ( $listsource->paymentmethods == null && $listsource->card != 1 && $listsource->RIB == null ) { print " checked"; }
print " ><label class='custom-control-label w-100' for='src_chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
print "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
} 

if ( ! empty(dolikiosk()) ) {
print "<li id='LiqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_liq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='4' ";
if ( $listsource->sources == null && $listsource->card != 1 && $listsource->CHQ == null && $listsource->RIB == null ) { print " checked"; }
print " ><label class='custom-control-label w-100' for='src_liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
print '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
print "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect-pro' )."</small>";
print '</div></div></label></div></li>';
}

}
print "<li class='list-group-item list-group-item-action flex-column align-items-start' id='SaveFormButton' style='display: none'>";
if ($mode != 'manage') {print '<div class="custom-control custom-checkbox"><input id="savethesource" class="custom-control-input form-control-sm" type="checkbox" name="savethesource" value="1" ><label class="custom-control-label w-100" for="savethesource"><small class="form-text text-muted"> '.__( 'Save this payment method', 'doliconnect-pro' ).'</small></label></div>';}
else {print '<div class="custom-control custom-checkbox"><input id="savethesource" type="hidden" name="savethesource" value="1"><input id="setasdefault" class="custom-control-input form-control-sm" type="checkbox" name="setasdefault" value="1" checked><label class="custom-control-label w-100" for="setasdefault"><small class="form-text text-muted"> '.__( 'Set as default payment mode', 'doliconnect-pro' ).'</small></label></div>';}
print "</li>";
print "</ul><div class='card-body'>";

if ($mode=='manage'){
print "<div id='DiscountFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit' disabled><b>".__( 'Recharge', 'doliconnect-pro' )."</b></button></div>";
print "<div id='CardFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit'><b>".__( 'Add credit card', 'doliconnect-pro' )."</b></button></div>";
print "<div id='BankFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit'><b>".__( 'Add bank account', 'doliconnect-pro' )."</b></button></div>";
if ( $listsource->code_client != null ){
print "<div id='dvDelete'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-danger btn-lg btn-block' type='submit'><b>".__( 'Delete', 'doliconnect-pro' )."</b></button></div>";
} elseif ( $listsource->code_client == null && $listsource->CHQ == null && $listsource->RIB == null ) {
print "<center>".__( 'No gateway', 'doliconnect-pro')."</center>";
}
}else{
print "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><div id='CardFormButton' style='display: none'></div><div id='BankFormButton' style='display: none'></div><div id='dvDelete' style='display: none'></div><button  id='buttontopay' class='btn btn-danger btn-lg btn-block' type='submit'><b>".__( 'Pay', 'doliconnect-pro' )." ".doliprice($total,$currency)."</b></button>";
}
print "</div></div>";
if ($mode=='manage'){
print "<p class='text-right'><small>";
print dolihelp('ISSUE');
print "</small></p>";
}
print "</form>";
}

function dolibuttontocart($product, $category=0, $add=0, $time=0) {
global $current_user;

$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante'));
$enablestock = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_STOCK", null, dolidelay('constante'));
$stockservices = callDoliApi("GET", "/doliconnector/constante/STOCK_SUPPORTS_SERVICES", null, dolidelay('constante'));

$button = "<div class='jumbotron'>";

if (doliconnector($current_user, 'fk_order') > 0) {
$orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, 0);
//$button .=$orderfo;
}

if ( isset($orderfo) && $orderfo->lines != null ) {
foreach ($orderfo->lines as $line) {
if  ($line->fk_product == $product->id) {
//$button = var_dump($line);
$qty=$line->qty;
$ln=$line->id;
}
}}

if (!isset($qty) ) {
$qty=null;
$ln=null;
}

$button .="<form id='product-add-form-$product->id' role='form' action='".doliconnecturl('dolishop')."?category=".$category."&product=".$product->id."'  method='post'>";

$button .= doliloaderscript('product-add-form-'.$product->id.'');

$button .="<input type='hidden' name='product_update' value='$product->id'><input type='hidden' name='product_update[".$product->id."][product]' value='$product->id'>";
$button .="<script type='text/javascript' language='javascript'>";

$button .="</script>";


$currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:'eur';

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {

if ( $product->duration_unit == 'i' ) {
$altdurvalue=60/$product->duration_value; 
}

}

if ( !empty($product->multiprices_ttc) ) {
$lvl=doliconnector($current_user, 'price_level');
$count=1;
//$button .=$lvl;
foreach ( $product->multiprices_ttc as $level => $price ) {
if ( (doliconnector($current_user, 'price_level') == 0 && $level == 1 ) || doliconnector($current_user, 'price_level') == $level ) {
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $price, $currency);
if ( empty($time) ) { $button .=' '.doliduration($product); }
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$price, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 
$button .= '<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect-pro' ).' '.doliconst(PRODUIT_MULTIPRICES_LABEL.$level).'</small>';
}
$count++; 
}
} else {
$button .= '<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $product->price_ttc, $currency);
if ( empty($time) && isset($product->duration) ) { $button .=' '.doliduration($product); } 
$button .= '</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 

}

if (doliconnector($current_user, 'price_level') > 0){
$level=doliconnector($current_user, 'price_level');
$price_min_ttc=$product->multiprices_min_ttc->$level;
$price_ttc=$product->multiprices_ttc->$level;
}
else {
$price_min_ttc=$product->price_min_ttc;
$price_ttc=$product->price_ttc;
}
//$button .=doliprice($price_ttc);

if ( is_user_logged_in() && $add==1 && is_object($order) && $order->value == 1 && doliconnectid('dolicart') > 0 ) {
$button .= "<div class='input-group'><select class='form-control' name='product_update[".$product->id."][qty]' ";
if ( empty($product->stock_reel) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ( ($product->stock_reel-$qty > '0' && $product->type == '0') ) {
if ( $product->stock_reel-$qty >= '10' || (is_object($enablestock) && $enablestock->value != 1) ) {
$m2 = 10;
} elseif ( $product->stock_reel > $line->qty ) {
$m2 = $product->stock_reel;
} else { $m2 = $qty; }
} else {
if ( isset($line) && $line->qty > 1 ) { $m2 = $qty; }
else { $m2 = 1; }
}
for ( $i=0;$i<=$m2;$i++ ) {
		if ( $i == $qty ) {
$button .= "<OPTION value='$i' selected='selected'>$i</OPTION>";
		} else {
$button .= "<OPTION value='$i' >$i</OPTION>";
		}
	}
$button .= "</SELECT><DIV class='input-group-append'><BUTTON class='btn btn-outline-secondary' type='submit' ";
if ( empty($product->stock_reel) && $product->type == '0' && (is_object($enablestock) && $enablestock->value == 1)) { $button .= " disabled"; }
$button .= ">";
if ( $qty > 0 ) {
$button .= __( 'Update', 'doliconnect-pro' )."";
} else {
$button .= __( 'Add', 'doliconnect-pro' )."";
}
$button .= "</button></div></div>";
if ( $qty > 0 ) {
$button .= "<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect-pro')."'>".__( 'Go to cart', 'doliconnect-pro')."</a></div>";
}
} elseif ( $add == 1 && doliconnectid('dolicart') > 0 ) {
$arr_params = array( 'redirect_to' => doliconnecturl('dolishop'));
$loginurl = esc_url( add_query_arg( $arr_params, wp_login_url( )) );

if ( get_option('doliloginmodal') == '1' ) {       
$button .= '<div class="input-group"><a href="#" data-toggle="modal" class="btn btn-block btn-outline-secondary" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect-pro').'</a></div>';
} else {
$button .= "<div class='input-group'><a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-block btn-outline-secondary' >".__( 'log in', 'doliconnect-pro').'</a></div>';
}

//$button .="<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".$loginurl."' role='button' title='".__( 'Login', 'doliconnect-pro' )."'>".__( 'Login', 'doliconnect-pro')."</a></div>";
} else {
$button .= "<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect-pro')."'>".__( 'Contact us', 'doliconnect-pro')."</a></div>";
}

if ( !empty(doliconnector($current_user, 'remise_percent')) ) { $button .= "<small>".sprintf( esc_html__( 'you get %u %% discount', 'doliconnect-pro'), doliconnector($current_user, 'remise_percent'))."</small>"; }
$button .= "<input type='hidden' name='product_update[".$product->id."][price]' value='$price_ttc'></form>";
$button .= '<div id="product-add-loading-'.$product->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect-pro').'</button></div>';
$button .= "</div>";
return $button;
}

 
?>
