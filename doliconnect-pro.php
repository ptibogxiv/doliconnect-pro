<?php
/**
 * Plugin Name: Doliconnect PRO
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Premium Enhancement of Doliconnect
 * Version: 1.2.4
 * Author: ptibogxiv
 * Author URI: https://www.ptibogxiv.net/en
 * Network: true
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: doliconnect-pro
 * Domain Path: /languages
 * Icon1x: https://www.ptibogxiv.net/wp-content/wppus/icons/doliconnect-pro-128x128.png
 * Icon2x: https://www.ptibogxiv.net/wp-content/wppus/icons/doliconnect-pro-256x256.png
 * BannerHigh: https://raw.githubusercontent.com/froger-me/wp-plugin-update-server/master/examples/banner-1544x500.png
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

function doliconnectpro_run() {

add_action( 'user_doliconnect_menu', 'paymentmodes_menu', 4, 1);
add_action( 'user_doliconnect_paymentmodes', 'paymentmodes_module');

function dolipaymentmodes_lock() {
return apply_filters( 'doliconnect_paymentmodes_lock', null);
}

//function example_callback( $string ) {
//    // (maybe) modify $string
//    return 'test';
//}
//add_filter( 'doliconnect_paymentmodes_lock', 'example_callback', 10, 1);

function paymentmodes_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'paymentmodes', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='paymentmodes') { echo " active";}
echo "'>".__( 'Manage payment methods', 'doliconnect-pro' )."</a>";
}

function paymentmodes_module( $url ) {
$action = sanitize_text_field($_GET['action']);
$srcid = sanitize_text_field($_GET['source']);

$delay = DAY_IN_SECONDS;

if ($action == 'setassourcedefault') {
$adh = [
    'default' => 1
	];

$gateway = CallAPI("PUT", "/doliconnector/".constant("DOLIBARR")."/sources/".$srcid, $adh, dolidelay( 0, true));
$gateway = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay($delay, true));
} 

if ($_POST['modepayment'] != 'src_newcard' && $srcid && $action == 'deletesource'){
$gateway = CallAPI("DELETE", "/doliconnector/".constant("DOLIBARR")."/sources/".$srcid, null, dolidelay( 0, true));
$gateway = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay($delay, true));
}

if ($action == 'addsource' && $srcid) {
$src = [
'default' => 0
];

$gateway = CallAPI("POST", "/doliconnector/".constant("DOLIBARR")."/sources/".$srcid, $src, dolidelay( 0, true));
$gateway = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay($delay, true));
} 

$listsource = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay($delay, $_GET["refresh"]));
//echo $listsource;

dolipaymentmodes($listsource, null, $url, $url);

}

if ( is_plugin_active( 'wp-plugin-update-server/wp-plugin-update-server.php' ) ) {
add_action( 'compta_doliconnect_menu', 'pluginupdatelicense_menu', 5, 1);
add_action( 'compta_doliconnect_pluginupdatelicense', 'pluginupdatelicense_module' );
}  

function pluginupdatelicense_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'pluginupdatelicense', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='pluginupdatelicense') { echo " active";}
echo "'>".__( 'Downloads & licenses', 'doliconnect-pro' )."</a>";
}

function pluginupdatelicense_module( $url ) {
global $wpdb,$current_user;
$entity = get_current_blog_id();
$ID = $current_user->ID;

echo "<div class='card shadow-sm'>";


echo "<ul class='list-group list-group-flush'><li class='list-group-item'>";

$licenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wppus_licenses WHERE email = '".$current_user->user_email."'") ;
// Parcours des resultats obtenus
foreach ($licenses as $post) {
 echo "<a href='".site_url()."/wp-update-server/?action=download&package_id=".$post->package_slug."&token=".get_site_option('wppus_package_download_url_token')."&update_license_key=".$post->license_key."&update_license_signature=xyIX4lQKvULMJ3DgqXBKvKHjR6we1jh1T7sR8KCpskJlvMB74sG3TVn6ESUWtHYKMGQaff_yEaC3uYHhCgEdtQ%3D%3D-NGE3MjFiYjVkZDNkZGQ3ZTA3MmIyYTMyMmY1YmY5MzhmODg5OTNmODYzZDMxMWI1MTUwMDU3OTNiM2ZhYTMxNTg4ZjlmNWNiNmE1M2E1MzE5N2Y2NjBlY3wx&update_type=".$post->package_type."&type=".$post->package_type."'>".$post->license_key."</a> / ".$post->max_allowed_domains." / ".maybe_unserialize($post->allowed_domains)." / ".$post->date_expiry." / ".$post->package_slug." / ".$post->package_type;
echo base64_encode(hash_hmac('sha256', $post->license_key, get_site_option('wppus_license_hmac_key')) . $post->license_key);

 echo '<br/>' ;
}

echo "</li></ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/donation/".constant("DOLIBARR"), $url, $delay);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";
}

function dolipaymentmodes($listsource, $object, $redirect, $url) {
global $current_user;

$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;

$lock = dolipaymentmodes_lock();

echo "<script src='https://js.stripe.com/v3/'></script>";

echo "<div id='payment-errors' class='alert alert-danger' role='alert' style='display: none'></div>";

echo "<div id='payment-form'><div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ( empty($object) ) { //$  &&  ( listsource->discount != 0 || $listsource->discount_product != null )
echo "<li id='DiscountForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='discount' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='discount' ";
if ( !empty($object) && !current_user_can( 'administrator' ) ) { echo " disabled "; }
echo " ><label class='custom-control-label w-100' for='discount'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-piggy-bank fa-3x fa-fw' style='color:HotPink'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>";
if ( $listsource->discount >= 0 ) {
echo __( 'Credit of', 'doliconnect-pro' );
} else {
echo __( 'Debit of', 'doliconnect-pro' );
}
echo " ".doliprice($listsource->discount)."</h6><small class='text-muted'>".__( 'Soon available', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
//if ( empty($object) && get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
//echo '<li class="list-group-item list-group-item-secondary" id="Recharge" style="display: none">';
//echo 'Prochainement, vous pourrez recharger votre compte!';
//echo '<div class="input-group mb-3">
//  <div class="input-group-prepend">
//    <span class="input-group-text">$</span>
//  </div>
//  <input type="num" class="form-control" aria-label="Amount (to the nearest dollar)">
//  <div class="input-group-append">
//    <span class="input-group-text">.00</span>
//  </div>
//</div>';
//echo '</li>';
//}
}

//SAVED SOURCES
if ( $listsource->sources != null ) {  
foreach ( $listsource->sources as $src ) {                                                                                                                       
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$src->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='$src->id' ";
if ( date('Y/n') >= $src->expiration && !empty($object) && !empty($src->expiration) ) { echo " disabled "; }
elseif ( $src->default_source == '1' ) { echo " checked "; }
echo " ><label class='custom-control-label w-100' for='$src->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i ';
if ( $src->type == sepa_debit ) {
echo 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $src->brand == 'visa' ) { echo 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $src->brand == 'mastercard' ) { echo 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $src->brand == 'amex' ) { echo 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else {echo 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
echo '></i></center>';
echo '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $src->type == sepa_debit ) {
echo __( 'Account', 'doliconnect-pro' ).' '.$src->reference.'<small> <a href="'.$src->mandate_url.'" title="'.__( 'Mandate', 'doliconnect-pro' ).' '.$src->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
echo __( 'Card', 'doliconnect-pro' ).' '.$src->reference;
}
if ( $src->default_source == '1' ) { echo " <i class='fas fa-star fa-1x fa-fw'></i><input type='hidden' name='defaultsource' value='$src->id'>"; }
echo '</h6>';
echo "<small class='text-muted'>".$src->holder."</small></div>";
echo "<div class='d-none d-sm-block col-2 align-middle text-right'><img src='".plugins_url('doliconnect/images/flag/'.strtolower($src->country).'.png')."' class='img-fluid' alt='$src->country'></div>";
echo "</div></label></div></li>";
} }

//NEW CARD
if ( count($listsource->sources) < 5 && $listsource->code_client != null && $listsource->card == 1 ) {      
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='CdDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newcard' ";
if ( $listsource->sources == null ) { echo " checked"; }
echo "><label class='custom-control-label w-100' for='CdDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-credit-card fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Credit card', 'doliconnect-pro' )."</h6><small class='text-muted'>Visa, MasterCard, Amex...</small></div></div>";
echo "</label></div></li>";

echo '<li class="list-group-item list-group-item-secondary" id="CardForm" style="display: none">';
echo '<input id="cardholder-name" name="cardholder-name" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your credit card', 'doliconnect-pro' ).'</div>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
echo '</li>';
}

//NEW SEPA DIRECT DEBIT
if ( count($listsource->sources) < 5 && $listsource->code_client != null && ( $listsource->sepa_direct == 1 || $listsource->sepa_direct != 1 && $listsource->STRIPE == 0 ) && get_current_blog_id() == 1 ) {    
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='BkDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newbank' ";
//if ($listsource["sources"]==null) {echo " checked";}
echo " ><label class='custom-control-label w-100' for='BkDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-university fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Via SEPA Direct Debit', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
echo '<li class="list-group-item list-group-item-secondary" id="BankForm" style="display: none">';
echo "<p class='text-justify'>";
$blogname=get_bloginfo('name');
echo '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect-pro' ), $blogname).'</small>';
echo "</p>";
echo '<input id="ibanholder-name" name="ibanholder-name" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your bank account', 'doliconnect-pro' ).'</div>
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- A Stripe Element will be inserted here. --></div>';
echo '<div id="bank-name"></div>';
echo '<div id="iban-errors" role="alert"></div>';
echo '</li>';
}

if ( ! empty($object) && empty(dolikiosk()) || (! empty($object) && get_option('doliconnectbeta') == '1' && current_user_can( 'administrator' )) ) {
//PAYMENT REQUEST API
echo "<li id='PraForm' class='list-group-item list-group-item-action flex-column align-items-start' style='display: none'><div class='custom-control custom-radio'>
<input id='src_pra' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_pra' ";
//if ($listsource["sources"] == null) {echo " checked";}
echo " ><label class='custom-control-label w-100' for='src_pra'>";
echo "<div class='row' id='googlepay'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fab fa-google fa-3x fa-fw" style="color:Black"></i></center>'; //<img src="' . plugins_url( 'images/googlepay.svg', __FILE__ ) . '" >
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Google Pay', 'doliconnect-pro' )."</h6>";
echo "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect-pro' )."</small></div></div>";
echo "<div class='row' id='applepay' style='display: none'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fab fa-apple-pay fa-3x fa-fw" style="color:Black"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Apple Pay', 'doliconnect-pro' )."</h6>";
echo "<small class='text-muted'>".__( 'Pay in one clic', 'doliconnect-pro' )."</small></div></div>";
echo '</label></div></li>';
}

//alternative payment modes & offline
if ( ! empty($object) ) {

if ( $listsource->PAYPAL != null && get_option('doliconnectbeta') == '1' && current_user_can( 'administrator' ) ) {
echo "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_paypal' ";
echo " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fab fa-paypal fa-3x fa-fw" style="color:#2997D8"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Redirect to Paypal', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

if ( $listsource->RIB != null ) {
echo "<li id='VirForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_vir' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_vir' ";
if ( $listsource->sources == null && $listsource->card != 1 ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

if ( $listsource->CHQ != null ) {
echo "<li id='ChqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_chq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_chq' ";
if ( $listsource->sources == null && $listsource->card != 1 && $listsource->RIB == null ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
} 

if ( ! empty(dolikiosk()) ) {
echo "<li id='LiqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_liq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_liq' ";
if ( $listsource->sources == null && $listsource->card != 1 && $listsource->CHQ == null && $listsource->RIB == null ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

}

// save new source button
echo "<li id='SaveFormButton' class='list-group-item list-group-item-action flex-column align-items-start'  style='display: none'>";
if ( ! empty($object) ) {echo '<div class="custom-control custom-checkbox"><input id="savethesource" class="custom-control-input form-control-sm" type="checkbox" name="savethesource" value="1" ><label class="custom-control-label w-100" for="savethesource"><small class="form-text text-muted"> '.__( 'Save this payment method', 'doliconnect-pro' ).'</small></label></div>';}
else {echo '<div class="custom-control custom-checkbox"><input id="savethesource" type="hidden" name="savethesource" value="1"><input id="setasdefault" class="custom-control-input form-control-sm" type="checkbox" name="setasdefault" value="1" checked><label class="custom-control-label w-100" for="setasdefault"><small class="form-text text-muted"> '.__( 'Set as default mode', 'doliconnect-pro' ).'</small></label></div>';}
echo "</li>";

echo "</ul><div class='card-body'>";

if ( $listsource->sources == null ) { echo "<input type='hidden' name='defaultsource' value='nosavedsource'>"; }  

if ( empty($object) ) {
echo "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'>";
echo "<button id='DiscountButton' style='display: none' class='btn btn-warning btn-block' type='submit' disabled><b>".__( 'Recharge', 'doliconnect-pro' )."</b></button>";
echo "<button id='CardButton' style='display: none' class='btn btn-warning btn-block' type='submit'><b>".__( 'Add a card', 'doliconnect-pro' )."</b></button>";
echo "<button id='BankButton' style='display: none' class='btn btn-warning btn-block' type='submit'><b>".__( 'Add an account', 'doliconnect-pro' )."</b></button>";
if ( $listsource->code_client != null ) {
echo "<div id='DeleteButton' class='btn-group d-flex' role='group'><button id='defaultbtn' class='btn btn-warning w-100' type='submit'><b>".__( 'Favorite', 'doliconnect-pro' )."</b></button><button id='deletebtn' class='btn btn-danger w-100' type='submit'><b>".__( 'Delete', 'doliconnect-pro' )."</b></button></div>";
} elseif ( $listsource->code_client == null && $listsource->CHQ == null && $listsource->RIB == null ) {
echo "<center>".__( 'No gateway', 'doliconnect-pro' )."</center>";
}
} else {
echo "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'>";
echo "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>";
echo "<button id='PayButton' class='btn btn-danger btn-block' type='submit'><b>".__( 'Pay', 'doliconnect-pro' )." ".doliprice($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc,$currency)."</b></button><div id='CardButton' style='display: none'></div><div id='BankButton' style='display: none'></div><div id='DiscountButton' style='display: none'></div><div id='DeleteButton' style='display: none'></div>";
}

echo "</div></div>";

if ( empty($object) ) {
echo "<small><div class='float-left'>";
echo dolirefresh("/doliconnector/".constant("DOLIBARR")."/sources",$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";
}

echo "</div>";

echo '<div id="payment-success" class="card text-white bg-success" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Success Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
echo '<div id="payment-waiting" class="card text-white bg-warning" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Waiting Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';
echo '<div id="payment-error" class="card text-white bg-danger" style="display: none">
  <div class="card-body">
    <h5 class="card-title">Error Payment</h5>
    <p class="card-text">Some quick example text to build on the card title and make up the bulk of the cards content.</p>
  </div>
</div>';

echo doliloading('payment');  

echo "<script>";
if ( $listsource->code_account != null ) {
?>
var stripe = Stripe('<?php echo $listsource->publishable_key; ?>',{
  stripeAccount: '<?php echo $listsource->code_account; ?>',
  betas: ['payment_intent_beta_3']
});
<?php
} else {
?>
var stripe = Stripe('<?php echo $listsource->publishable_key; ?>', {
  betas: ['payment_intent_beta_3']
});
<?php
}
?> 

var style = {
  base: {
    color: '#32325d',
    lineHeight: '18px',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
}; 

//VARIABLES
var CdDbt = document.getElementById("CdDbt");
var BkDbt = document.getElementById("BkDbt");  
var discount = document.getElementById("discount");

var src_chq = document.getElementById("src_chq");
var src_vir = document.getElementById("src_vir");
var src_liq = document.getElementById("src_liq");
var src_pra = document.getElementById("src_pra");

function ShowHideDiv() {

if ( CdDbt && CdDbt.checked ) {
//CARD
var elements = stripe.elements();
var cardElement = elements.create('card', {style: style});
cardElement.mount('#card-element');
var displayError = document.getElementById('card-errors');
displayError.textContent = '';
if ( document.getElementById("PayButton") ) { document.getElementById("PayButton").disabled = false; }
if ( document.getElementById("CardButton") ) { document.getElementById("CardButton").disabled = false; }
cardElement.addEventListener('change', function(ev) { 
  if (ev.error) {
    displayError.textContent = ev.error.message;
if ( document.getElementById("PayButton") ) { document.getElementById("PayButton").disabled = true; }
if ( document.getElementById("CardButton") ) { document.getElementById("CardButton").disabled = true; }
  } else {
    displayError.textContent = '';
if ( document.getElementById("PayButton") ) { document.getElementById("PayButton").disabled = false; }
if ( document.getElementById("CardButton") ) { document.getElementById("CardButton").disabled = false; }
  }
});
}

if ( BkDbt && BkDbt.checked ) {
//IBAN
var elements = stripe.elements();
var ibanElement = elements.create('iban', {
  style: style,
  supportedCountries: ['SEPA'],
});
ibanElement.mount('#iban-element');
var errorMessage = document.getElementById('error-message');
var displayError = document.getElementById('iban-errors');
var displayBankname = document.getElementById('bank-name');
displayError.textContent = '';
displayBankname.textContent = '';
document.getElementById("BankButton").disabled = false; 
ibanElement.on('change', function(ev) {
  if (ev.error) {
    displayError.textContent = ev.error.message;
if ( document.getElementById("PayButton") ) { document.getElementById("PayButton").disabled = true; }
if ( document.getElementById("BankButton") ) { document.getElementById("BankButton").disabled = true; }
  } else {
    displayError.textContent = '';
  if (ev.bankName) {
    displayBankname.textContent = ev.bankName;
  } else {
    displayBankname.textContent = '';
  }
if ( document.getElementById("PayButton") ) { document.getElementById("PayButton").disabled = false; }
if ( document.getElementById("BankButton") ) { document.getElementById("BankButton").disabled = false; }
  }
});
}

var cardholderName = document.getElementById('cardholder-name');
var ibanholderName = document.getElementById('ibanholder-name');
var selectedSource = document.querySelector('input[name=modepayment]:checked').value;
var defaultSource = document.querySelector('input[name=defaultsource]').value;

if (document.getElementById("defaultbtn")) {
if (selectedSource == defaultSource) {
document.getElementById("defaultbtn").disabled = true; 
} else {
document.getElementById("defaultbtn").disabled = false; 
}
}

if (CdDbt) {
document.getElementById("CardForm").style.display = CdDbt.checked ? "block" : "none";
document.getElementById("CardButton").style.display = CdDbt.checked ? "block" : "none"; 
}

if (BkDbt) {
document.getElementById("BankForm").style.display = BkDbt.checked ? "block" : "none";
document.getElementById("BankButton").style.display = BkDbt.checked ? "block" : "none"; 
}

if ( ( CdDbt && CdDbt.checked ) || ( BkDbt && BkDbt.checked )) {
document.getElementById("SaveFormButton").style.display = "block";
if ( document.getElementById("defaultbtn") ) {
document.getElementById("defaultbtn").style.display = "none";
document.getElementById("deletebtn").style.display = "none";
} 
if ( discount ) {
document.getElementById("DiscountButton").style.display = "none";
}
} else if ( discount && discount.checked ) {

document.getElementById("SaveFormButton").style.display = "none";
document.getElementById("defaultbtn").style.display = "none";
document.getElementById("deletebtn").style.display = "none";
document.getElementById("DiscountButton").style.display = "block";

} else {

document.getElementById("SaveFormButton").style.display = "none";
if ( document.getElementById("defaultbtn") ) {
document.getElementById("defaultbtn").style.display = "block";
document.getElementById("deletebtn").style.display = "block";

}
if ( discount ) {
document.getElementById("DiscountButton").style.display = "none";
}
}

if ( src_pra && src_pra.checked ) {
document.getElementById("PayButton").style.display = "none";
document.getElementById("payment-request-button").style.display = "block"; 
} else if ( src_pra )  {
document.getElementById("PayButton").style.display = "block";
document.getElementById("payment-request-button").style.display = "none";  
}

if ( document.getElementById("defaultbtn") ) {
document.getElementById("defaultbtn").addEventListener('click', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show'); 

window.location = "<?php echo $url."&action=setassourcedefault&source="; ?>" + document.querySelector('input[name=modepayment]:checked').value;


});
}

if ( document.getElementById("deletebtn") ) {
document.getElementById("deletebtn").addEventListener('click', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');

window.location = "<?php echo $url."&action=deletesource&source="; ?>" + document.querySelector('input[name=modepayment]:checked').value;

});
}

if ( document.getElementById("CardButton") ) {
document.getElementById("CardButton").addEventListener('click', function(event) {

jQuery('#payment-form').hide();
jQuery('#doliloading-payment').show(); 

stripe.createSource(cardElement, {
owner: {
  name: cardholderName.value
  }
      }
    ).then(function(result) {
    if (result.error) {
    
jQuery('#doliloading-payment').hide();
jQuery('#payment-form').show();  

var errorElement = document.getElementById('card-errors');
errorElement.textContent = result.error.message; 

    } else {
    
jQuery('#DoliconnectLoadingModal').modal('show');    
     
window.location = "<?php echo $url."&action=addsource&source="; ?>" + result.source.id;

    }
  });

});
}

if ( document.getElementById("BankButton") ) {
document.getElementById("BankButton").addEventListener('click', function(event) {

jQuery('#payment-form').hide();
jQuery('#doliloading-payment').show(); 

stripe.createSource(ibanElement, { 
   type: 'sepa_debit',
    currency: 'eur',
    owner: {
      name: ibanholderName.value,
    },
    mandate: {
      notification_method: 'manual',
    }
      }
    ).then(function(result) {
    if (result.error) {
    
jQuery('#doliloading-payment').hide();
jQuery('#payment-form').show();  

var errorElement = document.getElementById('iban-errors');
errorElement.textContent = result.error.message; 

    } else {
    
jQuery('#DoliconnectLoadingModal').modal('show');    
     
window.location = "<?php echo $url."&action=addsource&source="; ?>" + result.source.id;

    }
  });

});
}

//startpayment
var PayButton = document.getElementById('PayButton');
var clientSecret = '<?php echo $object->paymentintent; ?>';

stripe.retrievePaymentIntent(clientSecret).then(function(result) {

if (result.error) {

//jQuery('#payment-form').hide();
//jQuery('#payment-unknown').show();
//jQuery(window).scrollTop(0);
 
} else {

var paymentIntentid = result.paymentIntent.id;
var currency = result.paymentIntent.currency.toString(); 

PayButton.addEventListener('click', function(event) {

jQuery('#payment-errors').hide();
jQuery('#payment-form').hide();
jQuery('#doliloading-payment').show();

  if ((src_chq && src_chq.checked) || (src_vir && src_vir.checked) || (src_liq && src_liq.checked)) {
  //alternatives sources and offline

jQuery('#payment-form').hide(); 
jQuery('#payment-waiting').show();
jQuery('#doliloading-payment').show(); 

jQuery(window).scrollTop(0);
 
  } else if (CdDbt && CdDbt.checked) {
  //new card
  
document.getElementById("cardholder-name").required = true;
//if ('' != cardholderName.value){
   
stripe.handleCardPayment(clientSecret, cardElement, {
      source_data: {
        owner: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
    
jQuery('#payment-errors').show();
jQuery('#payment-form').show();
jQuery('#doliloading-payment').hide();

      var errorElement = document.getElementById('payment-errors');
      errorElement.textContent = result.error.message; 
      
  } else {
    
jQuery('#doliloading-payment').hide();
jQuery('#payment-form').hide(); 
jQuery('#payment-success').show();

jQuery(window).scrollTop(0); 

    }
  });
//}

  } else {
  //saved sources

stripe.handleCardPayment(clientSecret,
  {
    source: selectedSource
  }
).then(function(result) {
if (result.error) {

jQuery('#payment-errors').show();
jQuery('#payment-form').show();
jQuery('#doliloading-payment').hide();

jQuery(window).scrollTop(0); 
 
      var errorElement = document.getElementById('payment-errors');
      errorElement.textContent = result.error.message;
    } else {

jQuery('#payment-success').show(); 
jQuery('#doliloading-payment').hide();
 
jQuery(window).scrollTop(0);

    }   
});

  } 


});

}});

var elements = stripe.elements();
var paymentRequest = stripe.paymentRequest({
  country: 'FR',
  currency: '<?php echo $currency; ?>',
  total: {
    label: 'Total',
    amount: <?php echo $stripeAmount; ?>,
  },
//requestPayerName: true,
//requestPayerEmail: true,
});

var prButton = elements.create('paymentRequestButton', {
  paymentRequest: paymentRequest,
});

paymentRequest.canMakePayment().then(function(result) {
  if (result) {
  
  jQuery('#PraForm').show();
  
  if ( result.applePay ) {
  document.getElementById('applepay').style.display = 'block';
  document.getElementById('googlepay').style.display = 'none';
  }
  
  if ( src_pra.checked ) {
  prButton.mount('#payment-request-button');  
  } 
   
  } else {
    jQuery('#PraForm').hide();
    //document.getElementById('payment-request-button').style.display = 'none';
  }
});
//endpayment
}
 
window.onload=ShowHideDiv;

<?php
echo "</script>";

}

function dolimembership( $statut, $type, $delay) {
global $current_user;

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
$data = [
    'login' => $current_user->user_login,
    'company'  => $current_user->billing_company,
    'morphy' => $current_user->billing_type,
    'civility_id' => $current_user->billing_civility,    
    'lastname' => $current_user->user_lastname,
    'firstname' => $current_user->user_firstname,
    'address' => $current_user->billing_address,    
    'zip' => $current_user->billing_zipcode,
    'town' => $current_user->billing_city,
    'country_id' => $current_user->billing_country,
    'email' => $current_user->user_email,
    'phone' => $current_user->billing_phone,
    'birth' => $birth,
    'typeid' => $type,
    'fk_soc' => constant("DOLIBARR"),
    //'array_options' => $extrafields,
		'statut'	=> $statut,
	];
  
if ($action=='POST') {
$mbr = CallAPI("POST", "/adherentsplus", $data, 0);
define('DOLIBARR_MEMBER', $mbr);
$adhesion = CallAPI("GET", "/adherentsplus/".$mbr, null, dolidelay($delay, true));
} else {
$adhesion = CallAPI("PUT", "/adherentsplus/".constant("DOLIBARR_MEMBER"), $data, 0);
}

return $adhesion;
}

function dolimembership_modal( $adherent = null ) {

echo "<div class='modal fade' id='activatemember' tabindex='-1' role='dialog' aria-labelledby='activatememberLabel' aria-hidden='true' data-backdrop='static' data-keyboard='false'><div class='modal-dialog modal-dialog-centered modal-lg' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>";
echo "<h4 class='modal-title' id='myModalLabel'>".__( 'Subscription', 'doliconnect-pro' )." ".$adherent->next_subscription_season."</h4><button id='subscription-close' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>";
if ( $adherent->id > 0 ) {
echo "<h6 id ='subscription-h6' class='text-center'>".sprintf(__('Available from %s to %s', 'doliconnect-pro'), strftime("%d/%m/%Y",$adherent->next_subscription_date_start), strftime("%d/%m/%Y",$adherent->next_subscription_date_end));

if ($nextdebut != null ) {
$daterenew =  date_i18n('d/m/Y', $nextdebut);
} else {
$daterenew =  date_i18n('d/m/Y', current_time( 'timestamp',1));
}

//if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
//echo "<center>".sprintf(__('Renew from %s', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_renew))."</center>";
//}

if ($adherent->datefin == null) {echo "<br />".__( 'An entry fee can be applied to you depending on the type', 'doliconnect-pro' );} 
elseif ( $adherent->next_subscription_valid > current_time( 'timestamp',1) && $adherent->next_subscription_renew < current_time( 'timestamp',1) ) {echo "<br />".sprintf(__('From %s, a welcome fee can be apply', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_valid)); }
echo "</h6>";  
$tx=1;  
} else {
$tx=1;
}
echo "<table class='table table-striped' id ='subscription-table'>";

if ( ! empty($adherent) && $adherent->statut != 0 ) {
echo "<tr><td><div class='row'><div class='col-md-8'><b><i class='fas fa-user-slash'></i> ".__( 'Cancel my subscription', 'doliconnect-pro' );

echo "<small></small></b><br /><small class='text-justify text-muted '>".$postadh->note."</small></div><div class='col-md-4'>";
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='2'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-dark btn-block' type='submit'>".__( 'Resiliate', 'doliconnect-pro' )."</button></form>";
echo "</td></tr>";
}

if ( ($adherent->datefin == null) || (current_time( 'timestamp',1)>$next && $adherent->datefin>current_time( 'timestamp',1)) || ( $adherent->datefin < current_time( 'timestamp',1)) ) {
$typeadhesion = CallAPI("GET", "/adherentsplus/type?sortfield=t.family,t.libelle&sortorder=ASC", null);
//echo $typeadhesion;

if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
$montant1 = $postadh->price;
$montant2 = $tx*$postadh->price; 

if ( $postadh->subscription == '1' || ($postadh->subscription == '0' && ( $postadh->id == $adherent->typeid ) && $adherent->statut == '1') ) {
echo "<tr><td><div class='row'><div class='col-md-8'><b>";
if ($postadh->family =='1') {
echo "<i class='fas fa-users fa-fw'></i> ";
} else {echo "<i class='fas fa-user fa-fw'></i> ";}
echo $postadh->label." <small>";
if ( ( ($postadh->welcome > '0') && ($adherent->datefin == null )) || (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $adherent->next_subscription_valid) && (current_time( 'timestamp',1) > $adherent->datefin) && $adherent->next_subscription_valid != $adherent->datefin ) ) { 
$montantdata=($tx*$postadh->price)+$postadh->welcome;
echo "(";
echo doliprice($montantdata)." ";
echo __( 'then', 'doliconnect-pro' )." ".doliprice($montant1)." ".__( 'yearly', 'doliconnect-pro' ); 
} else {
echo "(".doliprice($montant1);
echo " ".__( 'yearly', 'doliconnect-pro' );
$montantdata=($tx*$postadh->price);
} 
echo ")";
echo "</small></b><br /><small class='text-justify text-muted '>".$postadh->note."</small></div><div class='col-md-4'>";
if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
echo "<button class='btn btn-info btn-block' disabled>".sprintf(__('From %s', 'doliconnect-pro'), date_i18n('d/m/Y', $adherent->next_subscription_renew))."</a>";
} elseif ( $postadh->family =='1' ) {
echo "<a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info btn-block' role='button'>".__( 'Contact us', 'doliconnect-pro' )."</a>";
} elseif ( ( $postadh->automatic_renew != '1' ) && ( $postadh->id == $adherent->typeid ) ) {
echo "<button class='btn btn-secondary btn-block' disabled>".__( 'Not available', 'doliconnect-pro' )."</a>";
} elseif ( ($postadh->automatic == '1') && ($postadh->id == $adherent->typeid) ) {
if ($adherent->statut == '1') {
if ($adherent->datefin == null ){echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro' )."</button></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><center><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}else {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}
}
} elseif ( $adhesionstatut == '0' ) {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";
} else {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";
}

} elseif (($postadh->automatic == '1') && ($postadh->id != $adherent->typeid)) {

if ( $adherent->statut == '1' ) {

if ( $adherent->datefin == null ) {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
} else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) { echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
} else {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";}
}

} elseif ( $adherent->statut == '0' ) {

echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";

} else {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro' )."</button></form>";
}

} elseif ( ($postadh->automatic != '1' ) && ( $postadh->id == $adherent->typeid ) ) {

if ( $adherent->statut == '1' ) {

if ($adherent->datefin == null ) {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro' )."</button></form>";
} else {
if ($adherent->datefin>current_time( 'timestamp',1)) { echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}else {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro' )."</button></form>";}
}

} elseif ( $adherent->statut == '0' ) {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
elseif ( $adherent->statut == '-1' ) {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect-pro' )."</button></form>";
} else {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-success btn-block' type='submit' >".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
}
elseif ( ($postadh->automatic != '1' ) and ( $postadh->id != $adherent->typeid) ) {
if ($adherent->statut == '1') {
if ($adherent->datefin == null ){echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><center><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}else {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";}
}
}
elseif ( $adherent->statut == '0' ) {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
}
else {
echo "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$montantdata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><button class='btn btn-danger btn-block' type='submit'>".__( 'Ask us', 'doliconnect-pro' )."</button></form>";
} 
}
}
echo "</div></div></td></tr>"; 
}}
}
echo "</table>";

echo doliloading('subscription'); 

echo "</div><div id='subscription-footer' class='modal-footer border-0'><small class='text-justify'>".__( 'Note: the admins reserve the right to change your membership (type/status) in relation to your personal situation when you finalize your order. A validation of the membership may be necessary depending on the case.', 'doliconnect-pro' )."</small></div></div></div></div>";

}

function dolicart_shortcode() {
global $wpdb,$current_user;
$current_user =  wp_get_current_user();
$entity = get_current_blog_id();
$time = current_time('timestamp');

doliconnect_enqueues();

if ( constant("DOLICONNECT_CART") > 0 ) {
$orderfo = CallAPI("GET", "/orders/".constant("DOLICONNECT_CART"), null, dolidelay(20 * MINUTE_IN_SECONDS, true));
//echo $orderfo;
}

if ( defined("DOLIBUG") ) {

echo dolibug();

} else {

if ( isset($_GET['validation']) && isset($_GET['order']) & isset($_GET['ref']) ) {

$orderfo = CallAPI("GET", "/orders/".$_GET['order'], null, 0);

echo "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw ";
if ($orderfo->billed==1 && $orderfo->statut>0){
echo "text-success";
}
elseif ($orderfo->statut>-1) {
echo "text-warning";
}
else {
echo "text-danger";
}

echo "' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>"; 

if ((!isset($orderfo->id)) || (constant("DOLIBARR") != $orderfo->socid) ) {
$return = esc_url(doliconnecturl('doliaccount'));
$order = CallAPI("GET", "/orders/".$orderfo->id, null, 0);
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_redirect($return);
exit;
}
echo "<center><h2>Votre commande a bien ete enregistree</h2>Numero de commande : ".$_GET['ref']." <br />Moyen de paiement : $orderfo->mode_reglement<br /><br />";
$TTC = number_format($orderfo->total_ttc, 2, ',', ' ');

if ( $orderfo->statut == '1' && !isset($_GET['error']) ) {
if ( $orderfo->mode_reglement_id == '7 ') 
{
$chq = CallAPI("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, MONTH_IN_SECONDS);

$bank = CallAPI("GET", "/bankaccounts/".$chq->value, null, MONTH_IN_SECONDS);

echo "<div class='alert alert-info' role='alert'><p align='justify'>Merci d'envoyer un cheque d'un montant de <b>$TTC â‚¬</b> libelle a l'ordre de <b>$bank->proprio</b> sous <b>15 jours</b> en rappelant votre rÃeference <b>$ref</b> Ã  l'adresse suivante :</p><p><b>$bank->owner_address</b></p>";
}
elseif ($orderfo->mode_reglement_id == '2') 
{
$vir = CallAPI("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, MONTH_IN_SECONDS);

$bank = CallAPI("GET", "/bankaccounts/".$vir->value, null, MONTH_IN_SECONDS);

echo "<div class='alert alert-info' role='alert'><p align='justify'>Merci d'effectuer un virement d'un montant de <b>$TTC â‚¬</b> sous <b>15 jours</b> en rappelant votre reference <b>$ref</b> sur le compte suivant :</p><p><b>IBAN : $bank->iban</b></p><p><b>SWIFT/BIC : $bank->bic</b></p>";
}
elseif ($orderfo->mode_reglement_id == '6') 
{
echo "<div class='alert alert-success' role='alert'><p>Votre paiement a bien été enregistré<br>Reference:  ".$_GET['charge']."</p>";
}
}
else {
echo "<div class='alert alert-danger' role='alert'><p>Une erreur est survenue lors du paiement.</p>";
}
echo "<br /><a href='".doliconnecturl('doliaccount')."?module=order&id=".$_GET['order']."&ref=".$_GET['ref'];
echo  "' class='btn btn-primary'>".__( 'See my order', 'doliconnect-pro' )."</a></center></div>";

} elseif (isset($_GET['pay']) && constant("DOLICONNECT_CART_ITEM") > 0) {

if ($_POST['source'] == 'validation' && !isset($_GET['info']) && isset($_GET['pay']) && !isset($_GET['validation'])) {

if ($_POST['modepayment']=='src_vir') {
$source="2";
}
elseif ($_POST['modepayment']=='src_chq') {
$source="7";
}
elseif ($_POST['modepayment']=='src_liq') {
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
$addsource = CallAPI("POST", "/doliconnector/".constant("DOLIBARR")."/sources", $src, WEEK_IN_SECONDS);
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
$orderipdate = CallAPI("PUT", "/orders/".$orderfo->id, $rdr, 0);

if ( $orderfo->id > 0 ) {

$successurl = doliconnecturl('dolicart')."?validation&order=$orderfo->id";
$returnurl = doliconnecturl('doliaccount')."?module=order&id=$orderfo->id";

if ( ($_POST['modepayment']!='src_chq' && $_POST['modepayment']!='src_vir' && $_POST['modepayment']!='src_liq' && $_POST['modepayment']!='src_payplug' && $_POST['modepayment']!='src_paypal') && $source ){

$warehouse = CallAPI("GET", "/doliconnector/constante/PAYPLUG_ID_WAREHOUSE", null, MONTH_IN_SECONDS);

$vld = [
    'idwarehouse' => $warehouse->value,
    'notrigger' => 0
	];
$validate = CallAPI("POST", "/orders/".$orderfo->id."/validate", $vld, 0);
// tout va bien
//$emailfrom = get_option('admin_email');
//$subject = "[NOTIFICATION] Demande d'adhÃ©sion";  
//$body = "Vous recevez cet email pour vous informer d'une demande d'adhÃ©sion en ligne Ã  valider de la part de ".$current_user->user_firstname." ".$current_user->user_lastname."<br /><br /><a href='".get_site_option('dolibarr_public_url')."/adherents/subscription.php?rowid=$input->member_id'>Voir la fiche d'adhÃ©rent sur Dolibarr</a>";
//$headers = array('Content-Type: text/html; charset=UTF-8','From: '.get_bloginfo('name').' <'.$emailfrom.'>');                      
//wp_mail($emailfrom, $subject, $body, $headers); 

$src = [
    'source' => "".$source."",
    'url' => "".$successurl.""
	];
$pay = CallAPI("POST", "/doliconnector/".constant("DOLIBARR")."/pay/order/".$orderfo->id, $src, 0);
//echo $pay;

if (isset($pay->error)){
$error=$pa->error;
echo "<center>".$pay->error->message."</center><br >";
} else {
//echo $pay;
$url=$pay->redirect_url.'&charge='.$pay->charge;
$order = CallAPI("GET", "/orders/".$orderfo->id, null, 0);
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_redirect( $url );
exit;
}

} elseif ($_POST['modepayment']=='src_chq' || $_POST['modepayment']=='src_vir'or $_POST['modepayment']=='src_liq'){

$warehouse = CallAPI("GET", "/doliconnector/constante/PAYPLUG_ID_WAREHOUSE", null, MONTH_IN_SECONDS);

$vld = [
    'idwarehouse' => $warehouse->value,
    'notrigger' => 0
	];
$validate = CallAPI("POST", "/orders/".$orderfo->id."/validate", $vld, 0);

$orderfo = CallAPI("GET", "/orders/".$orderfo->id, null);

$successurl2 = $successurl."&ref=".$orderfo->ref;

$order = CallAPI("GET", "/orders/".$orderfo->id, null, 0);
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_redirect($successurl2);
exit;
}
elseif ($_POST['modepayment'] == 'src_payplug')  {

} else {
if ($orderfo->id <=0 || $error || !$source) {
echo "<center><h4>Oups, une erreur est survenue, merci de rÃ©essayer</h4>";
echo "<br /><a href='".esc_url(get_permalink())."' class='btn btn-primary'>Retourner sur la page de paiement</a></center>";
}
}
}                                  
} elseif ( !$orderfo->id > 0 && $orderfo->lines == null ) {
$order = CallAPI("GET", "/orders/".$orderfo->id, null, 0);
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, 0);
wp_redirect(esc_url(get_permalink()));
exit;
}

//header('Refresh: 300; URL='.esc_url(get_permalink()).'');

echo "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

echo "<div class='row'><div class='col-12 col-md-4  d-none d-sm-none d-md-block'>";
doliminicart($orderfo);
echo "<div class='card'><div class='card-header'>".__( 'Billing', 'doliconnect-pro' )." <small>(<a href='".esc_url(get_permalink(get_option('dolicart'))."?info")."' >".__( 'update', 'doliconnect-pro' )."</a>)</small></div><div class='card-body'>";

$thirdparty = CallAPI("GET", "/thirdparties/".constant("DOLIBARR"), null, dolidelay(DAY_IN_SECONDS, $_GET["refresh"]));

echo $thirdparty->name."<br>";
echo $thirdparty->address."<br>".$thirdparty->zip." ".$thirdparty->town.", ".strtoupper($thirdparty->country)."<br>";
echo $current_user->user_email."<br>".$thirdparty->phone;   

echo "</div></div></div><div class='col-12 col-md-8'>";

$listsource = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay( DAY_IN_SECONDS, $_GET["refresh"]));
//echo $listsource;

if ( !empty($orderfo->paymentintent) ) {
dolipaymentmodes($listsource, $orderfo, esc_url(get_permalink())."?pay", esc_url(get_permalink())."?pay");
} else {
doligateway($listsource, 'Total', $orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc, $orderfo->multicurrency_code, esc_url(get_permalink())."?pay", 'full');
echo doliloading('paymentmodes');
}

echo "</div></div>";

} elseif (isset($_GET['info']) && constant("DOLICONNECT_CART_ITEM") > 0){

if ($_POST['info'] == 'validation' && isset($_GET['info']) && !isset($_GET['pay']) && !isset($_GET['validation'])) {

$ID = $current_user->ID;
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($_POST['user_email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => ucfirst(strtolower($_POST['user_firstname']))." ".strtoupper($_POST['user_lastname'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($_POST['user_firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($_POST['user_lastname']))));
wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
update_usermeta( $ID, 'billing_civility', $_POST['billing_civility']);
update_usermeta( $ID, 'billing_type', $_POST['billing_type']);
update_usermeta( $ID, 'billing_company', sanitize_text_field($_POST['billing_company']));
update_usermeta( $ID, 'billing_address', sanitize_textarea_field($_POST['billing_address']));
update_usermeta( $ID, 'billing_zipcode', sanitize_text_field($_POST['billing_zipcode']));
update_usermeta( $ID, 'billing_city', sanitize_text_field($_POST['billing_city']));
update_usermeta( $ID, 'billing_country', $_POST['billing_country'] );
update_usermeta( $ID, 'billing_phone', sanitize_text_field($_POST['billing_phone'])); 
update_usermeta( $ID, 'billing_birth',$_POST['billing_birth']);
do_action('wp_dolibarr_sync',constant("DOLIBARR"));

// UPDATE INFO USER

wp_redirect(esc_url(get_permalink().'?pay'));
exit;                                   
} elseif ( !$orderfo->id > 0 && $orderfo->lines == null ) {
wp_redirect(esc_url(get_permalink()));
exit;
}
//header('Refresh: 300; URL='.esc_url(get_permalink()).'');
$ID = $current_user->ID;

echo "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-success' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar bg-success w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

echo "<div class='row' id='informations-form'><div class='col-12 col-md-4 d-none d-sm-none d-md-block'>";
doliminicart($orderfo);
echo "</div><div class='col-12 col-md-8'>";
echo "<form role='form' class='was-validated' action='".esc_url(get_permalink())."?info' method='post'>";
echo "<script>";
?> 

var form = document.getElementById('informations-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);    
console.log("submit");
form.submit();

});

<?php
echo "</SCRIPT><div class='card'><ul class='list-group list-group-flush'>"; 

echo doliconnectuserform(CallAPI("GET", "/thirdparties/".constant("DOLIBARR"), null, dolidelay(DAY_IN_SECONDS, $_GET["refresh"])), dolidelay(MONTH_IN_SECONDS, $_GET["refresh"], true), 'full');

echo "</ul><div class='card-body'><input type='hidden' name='info' value='validation'><input type='hidden' name='cart' value='validation'><center><button class='btn btn-warning btn-block' type='submit'><b>".__( 'Validate', 'doliconnect-pro' )."</b></button></center></div></div></form>";
echo "</div></div>";

} else {

echo "<table width='100%' style='border: none'><tr style='border: none'><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-shopping-bag fa-fw text-warning' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar progress-bar-striped progress-bar-animated w-100' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-user fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-money-bill-wave fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td><td style='border: none'><div class='progress'>
<div class='progress-bar w-0' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100'></div>
</div></td><td width='50px' style='border: none'><div class='fa-3x'>
<i class='fas fa-check fa-fw text-dark' data-fa-transform='shrink-3.5' data-fa-mask='fas fa-circle' ></i>
</div></td></tr></table><br>";

if ($_POST['cart'] == 'validation' && !isset($_GET['user']) && !isset($_GET['pay']) && !isset($_GET['validation'])) {
wp_redirect(esc_url(get_permalink().'?info'));
exit;                                   
}

if (isset($_GET['purge_basket'])) {
$orderdelete = CallAPI("DELETE", "/orders/".constant("DOLICONNECT_CART"), null);
$dolibarr = CallAPI("GET", "/doliconnector/".$current_user->ID, null, -HOUR_IN_SECONDS);
if (1==1) {

wp_redirect(esc_url(get_permalink()));
exit;
} else {
echo "<div class='alert alert-warning' role='alert'><p><strong>".__( 'Oops!', 'doliconnect-pro' )."</strong> ".__( 'An error is occured. Please contact us.', 'doliconnect-pro' )."</p></div>"; 
}
}
 
if (isset($_POST['product_update'])) {
$result = addtodolibasket($_POST['product_update'], $_POST['product_qty'], $_POST['product_price'], $_POST['product_line']);
//echo $_POST['product_update']."/".$_POST['product_qty']."/".$_POST['product_price']."/".$_POST['product_line'];
if (1==1) {
if (constant("DOLICONNECT_CART") > 0) {
$orderfo = CallAPI("GET", "/orders/".constant("DOLICONNECT_CART"), null, 0);
//echo $orderfo;
}
//wp_redirect(esc_url(get_permalink()));
//exit;
} else {
echo "<div class='alert alert-warning' role='alert'><p><strong>".__( 'Oops!', 'doliconnect-pro' )."</strong> ".__( 'An error is occured. Please contact us.', 'doliconnect-pro' )."</p></div>"; 
}
}

$timeout=$orderfo->date_modification-current_time('timestamp',1)+1200;

echo "<script>";
?>
var tmp=<?php echo ($timeout)*10; ?>;
 
var chrono=setInterval(function (){
     min=Math.floor(tmp/600);
     sec=Math.floor((tmp-min*600)/10);
     dse=tmp-((min*60)+sec)*10;
     tmp--;
     jQuery('#duration').text(min+'mn '+sec+'sec');
},100);
<?php
echo "</script>";
//header('Refresh: 120; URL='.esc_url(get_permalink()).'');
//header('Refresh: '.$timeout.'; URL='.esc_url(get_permalink()).'');

//echo date_i18n('d/m/Y H:i', $orderfo[date_modification]);


if ( constant("DOLICONNECT_CART")>0 && $orderfo->lines != null ) {  //&& $timeout>'0'                                                                                         
echo "<div id='timer' class='text-center'><small>".sprintf( esc_html__('Your basket #%s is reserved for', 'doliconnect-pro'), constant("DOLICONNECT_CART"))." <span class='duration'></span></small></div>";
}

echo "<form role='form' action='".esc_url(get_permalink())."' id='payment-form' method='post'>";

echo "<script>";
?> 

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);    
console.log("submit");
form.submit();

});

<?php
echo '</script>';

echo "<div class='card shadow-sm' id='cart-form'><ul class='list-group list-group-flush'>";

if ($orderfo->lines != null && !empty($orderfo->id) ) {

foreach ($orderfo->lines as $line) {
echo "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end !='')
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

if ( $line->fk_product > 0 ) {
$product = CallAPI("GET", "/products/".$line->fk_product, null, 0);
}

echo '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
<h6 class="mb-1">'.$line->libelle.'</h6>
<p class="mb-1">'.$line->description.'</p>
<small>'.$dates.'</small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line->multicurrency_total_ttc?$line->multicurrency_total_ttc:$line->total_ttc,$orderfo->multicurrency_code).'</h5>';
echo "<form role='form' action='".esc_url(get_permalink())."' id='qty-form' method='post'>";

echo "<input type='hidden' name='product_line' value='$line->id'><input type='hidden' name='product_price' value='$line->subprice'><input type='hidden' name='product_update' value='$line->fk_product'>";
echo "<select class='form-control' name='product_qty' onchange='this.form.submit()'>";
if (($product->stock_reel-$line->qty > '0' && $product->type == '0')) {
if ($product->stock_reel-$line->qty >= '10' ) {
$m2 = 10;
} elseif ($product->stock_reel>$line->qty) {
$m2 = $product->stock_reel;
} else { $m2 = $line->qty; }
} else {
if ($line->qty>1){$m2=$line->qty;}
else {$m2 = 1;}
}
	for($i=0;$i<=$m2;$i++){
		if ($i==$line->qty){
			echo "<option value='$i' selected='selected'>$i</option>";
		}else{
			echo "<option value='$i' >$i</option>";
		}
	}
echo "</select>";
echo "</form>"; 
echo "</div></div></li>";
}
} else {
echo "<li class='list-group-item list-group-item-light'><br><br><br><br><br><center><strong>".__( 'Your basket is empty.', 'doliconnect-pro' )."</strong></center>";
if ( !is_user_logged_in() ) {
echo '<center>'.__( 'If you already have an account,', 'doliconnect-pro' ).' ';

if ( get_option('doliloginmodal') == '1' ) {
       
echo '<a href="#" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect-pro' ).'</a> ';
} else {
echo "<a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' >".__( 'log in', 'doliconnect-pro' ).'</a> ';
}
echo __( 'to see your basket.', 'doliconnect-pro' ).'</center>';
}
echo "<br><br><br><br><br></li>";
} 

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect-pro').": ".doliprice($orderfo->multicurrency_total_ht?$orderfo->multicurrency_total_ht:$orderfo->total_ht,$orderfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total tax', 'doliconnect-pro').": ".doliprice($orderfo->multicurrency_total_tva?$orderfo->multicurrency_total_tva:$orderfo->total_tva,$orderfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total incl. tax', 'doliconnect-pro').": ".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b>";
echo "</li>";
echo "</ul>";

if ( $orderfo->lines != null || (get_option('dolishop') && $orderfo->lines != null ) || get_option('dolishop') ) {
echo "<div class='card-body'>";
echo "<form role='form' action='".esc_url(get_permalink())."' id='cart-action-form' method='post'>";
echo "<script>";
?> 

var form = document.getElementById('cart-action-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0); 
console.log("submit");
form.submit();

});

<?php
echo '</SCRIPT>';
echo "<div class='row' id='button-cart'>";
if (get_option('dolishop')){
echo "<div class='col-12 col-md'><a href='".doliconnecturl('dolishop')."' class='btn btn-outline-info w-100' role='button' aria-pressed='true'><b>".__( 'Continue shopping', 'doliconnect-pro' )."</b></a></div>";
}  

if ( $orderfo->lines != null ){
echo "<div class='col-12 col-md'><a href='".esc_url(get_permalink())."?purge_basket' class='btn btn-outline-secondary w-100' role='button' aria-pressed='true'><b>".__( 'Empty the basket', 'doliconnect-pro' )."</b></a></div>";
}

if ( $orderfo->lines != null ) {
echo "<div class='col-12 col-md'><input type='hidden' name='cart' value='validation'><button type='submit' class='btn btn-warning w-100' role='button' aria-pressed='true'><b>".__( 'Process', 'doliconnect-pro' )."</b></button></div>";
} 
echo "</div>";
 
echo "</form></div>";
}

echo "</div>"; 

}}}
add_shortcode('dolicart', 'dolicart_shortcode');
// ********************************************************
function dolishop_shortcode($atts) {
global $wpdb;

doliconnect_enqueues();

$shop = CallAPI("GET", "/doliconnector/constante/DOLICONNECT_CATSHOP", null, MONTH_IN_SECONDS);
//echo $shop;

if (defined("DOLIBUG")) {
$boutik.= dolibug();
} else {
if ( !$_GET['category'] ) {
$boutik.= "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( $shop->value != null ) {
$resultatsc = CallAPI("GET", "/categories?sortfield=t.rowid&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent='".$shop->value."')", "", MONTH_IN_SECONDS);

if ( !isset($resultatsc ->error) && $resultatsc != null ) {
foreach ($resultatsc as $categorie) {
$boutik.= "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action'>".$categorie->label."<br />".$categorie->description."</a>"; 
}}}

$catoption = CallAPI("GET", "/doliconnector/constante/ADHERENT_MEMBER_CATEGORY", null, MONTH_IN_SECONDS);

if ( !empty($catoption->value) && is_user_logged_in() ) {
$boutik.= "<a href='".esc_url( add_query_arg( 'category', $catoption->value, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action' >Produits/Services lies a l'adhesion</a>";
}

$boutik.= "</ul></div>";
} else {
if ( isset($_GET['product']) ) {
addtodolibasket(esc_attr($_GET['product']), esc_attr($_POST['product_update'][$_GET['product']][qty]), esc_attr($_POST['product_update'][$_GET['product']][price]));
//echo $_POST['product_update'][$_GET['product']][product];
wp_redirect( esc_url( add_query_arg( 'category', $_GET['category'], doliconnecturl('dolishop')) ) );
exit;
}
$boutik.= "<table class='table' width='100%'>";
$resultatso = CallAPI("GET", "/products?sortfield=t.label&sortorder=ASC&category=".$_GET['category']."&sqlfilters=(t.tosell=1)", null, MONTH_IN_SECONDS);
//echo $resultatso;

if ( !isset($resultatso->error) && $resultatso != null ) {
foreach ($resultatso as $product) {
$boutik.= "<tr class='table-light'><td><center><i class='fa fa-plus-circle fa-2x fa-fw'></i></center></td><td><b>$product->label</b> ";
$boutik.= doliproductstock($product);
$boutik.= "<br />$product->description</td><td width='300px'><center>";
$boutik.= dolibuttontocart($product, esc_attr($_GET['category']), 1);
$boutik.= "</center></td></tr>"; 
}}else{
wp_redirect(esc_url(get_permalink()));
exit;
}
$boutik.= "</tbody></table>";
}
}
return $boutik;
}
add_shortcode('dolishop', 'dolishop_shortcode');

}
add_action( 'plugins_loaded', 'doliconnectpro_run', 10, 0 );

// ********************************************************

function doliconnect_privacy() {
global $wpdb;

doliconnect_enqueues();

if ( is_user_logged_in() && get_option('doliconnectbeta') == '2' && ($current_user->$privacy < get_the_modified_date( 'U', get_option( 'wp_page_for_privacy_policy' ))) ) {
echo "<script>";
?>
function DoliconnectShowPrivacyDiv() {
jQuery('#DoliconnectPrivacyModal').modal('show');
}

window.onload=DoliconnectShowPrivacyDiv;
<?php
echo "</SCRIPT>";

echo '<div id="DoliconnectPrivacyModal" class="modal fade bd-example-modal-xl" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-show="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title" id="exampleModalLabel">Confidentialite - V'.get_the_modified_date( $d, get_option( 'wp_page_for_privacy_policy' ) ).'</h5>';
//echo '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
echo '</div><div class="bg-light text-dark" data-spy="scroll" data-target="#navbar-example2" data-offset="0" style="overflow: auto; height:55vh;">';
echo apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' ))); 
echo '</div>    
      <div class="modal-footer">
        <button type="button" class="btn btn-success" >'.__( 'I approve', 'doliconnect-pro' ).'</button>
        <a href="'.wp_logout_url( get_permalink() ).'" type="button" class="btn btn-danger">'.__( 'I refuse', 'doliconnect-pro' ).'</a>
      </div>
    </div>
  </div>
</div>';
}

if ( ( !is_user_logged_in() && !empty(get_option('doliconnectrestrict')) ) || (!is_user_member_of_blog( $current_user->ID, get_current_blog_id()) && !empty(get_option('doliconnectrestrict')) )) {
echo "<script>";
?>
function DoliconnectShowLoginDiv() {
jQuery('#DoliconnectLogin').modal('show');
}

window.onload=DoliconnectShowLoginDiv;
<?php
echo "</SCRIPT>";
}

}
add_action( 'wp_footer', 'doliconnect_privacy' );

function doliconnect_modal() {
global $wpdb,$current_user;
$entity = get_current_blog_id();
$year = strftime("%Y",$time);

// modal for login
if ( !is_user_logged_in() ){

echo "<div class='modal fade' id='DoliconnectLogin' tabindex='-1' role='dialog' aria-labelledby='DoliconnectLoginTitle' aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content border-0'><div class='modal-header border-0'>";

if ( empty(get_option('doliconnectrestrict')) ) {
echo "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Welcome', 'doliconnect-pro' )."</h5><button id='CloseModalLogin' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";
} else {
echo "<h5 class='modal-title' id='DoliconnectLoginTitle'>".__( 'Restrict to users', 'doliconnect-pro' )."</h5>";
}

echo "</div><div class='modal-body'><div id='loginmodal-form'>";
echo "<b>".get_option('doliaccountinfo')."</b>";

if ( ! function_exists('dolikiosk') || ( function_exists('dolikiosk') && empty(dolikiosk())) ) {
echo socialconnect(get_permalink());
}

if (function_exists('secupress_get_module_option') && secupress_get_module_option('move-login_slug-login', $slug, 'users-login' )) {
$login_url=site_url()."/".secupress_get_module_option('move-login_slug-login', $slug, 'users-login' ); 
}else{
$login_url=site_url()."/wp-login.php"; }

if ( function_exists('dolikiosk') && ! empty(dolikiosk()) ) {
$redirect_to=doliconnecturl('doliaccount');
} else {
$redirect_to=get_permalink();
}

echo "<form name='loginmodal-form' action='$login_url' method='post' class='was-validated'>";
echo "<script>";
?>

var form = document.getElementById('loginmodal-form');
form.addEventListener('submit', function(event) { 
jQuery(window).scrollTop(0);
jQuery('#CloseModalLogin').hide(); 
jQuery('#FooterModalLogin').hide();
jQuery('#loginmodal-form').hide(); 
jQuery('#doliloading-login-modal').show(); 
console.log("submit");
formmodallogin.submit();
});

<?php
echo "</script>";
echo "<div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div>
<input class='form-control' id='user_login' type='email' placeholder='".__( 'Email', 'doliconnect-pro' )."' name='log' value='' required>";
echo "</div></div><div class='form-group'>
<div class='input-group mb-2 mr-sm-2'><div class='input-group-prepend'>
<div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div>
<input class='form-control' id='user_pass' type='password' placeholder='".__( 'Password', 'doliconnect-pro' )."' name='pwd' value ='' required>";
echo "</div></div>";

//if ( function_exists('dolikiosk') && empty(dolikiosk()) ) {
if ( get_site_option('doliconnect_mode') == 'one' && function_exists('switch_to_blog') ) {
switch_to_blog(1);
} 
echo "<div><div class='float-left'><small>";
if (((!is_multisite() && get_option( 'users_can_register' )) || (get_option('users_can_register') == '1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) {
echo "<a href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect-pro' )."'>".__( 'Create an account', 'doliconnect-pro' )."</a>";
}
//<input type='checkbox' class='custom-control-input' value='forever' id='remembermemodal' name='rememberme'>";
//echo "<label class='custom-control-label' for='remembermemodal'> ".__( 'Remember me', 'doliconnect-pro' )."</label>";
echo "</div><div class='float-right'><a href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect-pro' )."'>".__( 'Forgot password?', 'doliconnect-pro' )."</a></small></div></div>"; 
if (get_site_option('doliconnect_mode')=='one') {
restore_current_blog();
}
//}

//<small>";
//if (((!is_multisite() && get_option( 'users_can_register' )) || (get_option('users_can_register')=='1' && (get_site_option( 'registration' ) == 'user' || get_site_option( 'registration' ) == 'all')))) 
//{echo "<a href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create an account', 'doliconnect-pro' )."'><i class='fas fa-user-plus'></i> ".__( 'Create an account', 'doliconnect-pro' )."</a> | ";
//}
//echo "<a href='".wp_lostpassword_url(get_permalink())."' role='button' title='".__( 'Forgot password?', 'doliconnect-pro' )."'><i class='fas fa-user-shield'></i> ".__( 'Forgot password?', 'doliconnect-pro' )."</a>";
//echo " | ";
//echo dolihelp('ISSUE');
///echo "</small>

echo "<input type='hidden' value='$redirect_to' name='redirect_to'></div>";
echo doliloading('login-modal');
echo "</div><div id='FooterModalLogin' class='modal-footer'><button id='submit' class='btn btn-block btn-primary' type='submit' name='submit' value='Submit'";
echo "><b>".__( 'Sign in', 'doliconnect-pro' )."</b></button></form></div></div></div></div>";}

// modal for CGU
if (get_option('dolicgvcgu')){
echo "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='exampleModalCenterTitle' aria-hidden='true'><div class='modal-dialog modal-dialog-centered modal-lg' role='document'><div class='modal-content'><div class='modal-header'>
<h5 class='modal-title' id='exampleModalLongTitle'>Conditions Generales d'Utilisation</h5>
<button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
<div class='modal-body'>
en cours d'integration
</div></div></div></div>";}
}
add_action( 'wp_footer', 'doliconnect_modal' );
// ********************************************************
function socialconnect( $url ) {

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
global $current_user,$wpdb;
$currency=strtolower($currency);


echo "<script src='https://js.stripe.com/v3/'></script><script>";
if ( $listsource->code_account != null ) {
?>
var stripe = Stripe('<?php echo $listsource->publishable_key; ?>',{
    stripeAccount: '<?php echo $listsource->code_account; ?>'
    });
<?php
} else {
?>
var stripe = Stripe('<?php echo $listsource->publishable_key; ?>');
<?php
}
?>
var mode = '<?php echo $mode; ?>';
var montant = <?php echo $total*100; ?>;
var monnaie = '<?php echo $currency; ?>';
var ref = '<?php echo $ref; ?>';
var lang = '<?php echo $lang; ?>';
var courriel = '<?php echo $current_user->user_email; ?>';
var comcountrycode = '<?php echo $listsource->com_countrycode; ?>';
var cuscountrycode = '<?php echo $listsource->cus_countrycode; ?>';
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
jQuery('#payment-request-button').hide();            
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
jQuery('#payment-request-button').hide();            
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
jQuery(window).scrollTop(0);
jQuery('#else').hide();
jQuery('#closemodalonlinepay').hide(); 
jQuery('#payment-form').hide();
jQuery('#gateway-form').hide(); 
jQuery('#buttontopay').hide(); 
jQuery('#payment-request-button').hide();            
jQuery('#button-source-payment').hide();
jQuery('#DoliconnectLoadingModal').modal('show');  
jQuery('#doliloading-paymentmodes').show();
console.log("submit");
form.submit();
});

<?php
echo '</SCRIPT>';
echo "<script>";
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
echo "</script>";
$countsrc=count($listsource->sources);
echo "<div id='payment-request-button'><!-- A Stripe Element will be inserted here. --></div>
<div id='else' style='display: none' ><br><div style='display:inline-block;width:46%;float:left'><hr width='90%' /></div><div style='display:inline-block;width: 8%;text-align: center;vertical-align:90%'><small class='text-muted'>".__( 'or', 'doliconnect-pro' )."</small></div><div style='display:inline-block;width:46%;float:right' ><hr width='90%'/></div><br></div>";
echo "<form role='form' action='$redirect' id='gateway-form' method='post' novalidate>";
echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ($mode=='manage' && ($listsource->discount!=0 || $listsource->discount_product!=null)) {
echo "<li id='DctForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_dct' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_dct' ";
//if ($listsource["discount"]>0){echo " checked ";}
echo " ><label class='custom-control-label w-100' for='src_dct'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-piggy-bank fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>";
if ($listsource->discount>=0) {
echo __( 'Credit of', 'doliconnect-pro' );
} else {
echo __( 'Debit of', 'doliconnect-pro' );
}
echo " ".doliprice($listsource->discount)."</h6><small class='text-muted'>".__( 'Automatic use', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
//if (get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
echo '<li class="list-group-item list-group-item-secondary" id="DctAddForm" style="display: none">';
echo 'Prochainement, vous pourrez recharger votre compte!';
echo '<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">$</span>
  </div>
  <input type="num" class="form-control" aria-label="Amount (to the nearest dollar)">
  <div class="input-group-append">
    <span class="input-group-text">.00</span>
  </div>
</div>';
echo '</li>';
//}
} 

//SAVED SOURCES
if ( $listsource->sources != null ) {  
foreach ( $listsource->sources as $src ) {                                                                                                                       
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$src->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='$src->id' ";
if ( date('Y/n') >= $src->expiration && !empty($object) && !empty($src->expiration) ) { echo " disabled "; }
elseif ( $src->default_source == '1' ) { echo " checked "; }
echo " ><label class='custom-control-label w-100' for='$src->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i ';
if ( $src->type == sepa_debit ) {
echo 'class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $src->brand == 'visa' ) { echo 'class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $src->brand == 'mastercard' ) { echo 'class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $src->brand == 'amex' ) { echo 'class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else {echo 'class="fab fa-cc-amex fa-3x fa-fw"';}
}
echo '></i></center>';
echo '</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $src->type == sepa_debit ) {
echo __( 'Account', 'doliconnect-pro' ).' '.$src->reference.'<small> <a href="'.$src->mandate_url.'" title="'.__( 'Mandate', 'doliconnect-pro' ).' '.$src->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
echo __( 'Card', 'doliconnect-pro' ).' '.$src->reference;
}
if ( $src->default_source == '1' ) { echo " <i class='fas fa-star fa-1x fa-fw'></i><input type='hidden' name='defaultsource' value='$src->id'>"; }
echo '</h6>';
echo "<small class='text-muted'>".$src->holder."</small></div>";
echo "<div class='d-none d-sm-block col-2 align-middle text-right'><img src='".plugins_url('doliconnect/images/flag/'.strtolower($src->country).'.png')."' class='img-fluid' alt='$src->country'></div>";
echo "</div></label></div></li>";
} }

if ($countsrc<5 && $listsource->code_client!=null && $listsource->card==1) {      
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='CdDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newcard' ";
if ($listsource->sources==null) {echo " checked";}
echo " ><label class='custom-control-label w-100' for='CdDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-credit-card fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Credit card', 'doliconnect-pro' )."</h6><small class='text-muted'>Visa, MasterCard, Amex...</small></div></div>";
echo "</label></div></li>";

echo '<li class="list-group-item list-group-item-secondary" id="CardForm" style="display: none">';
echo '<input id="card-owner" name="card-owner" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your credit card', 'doliconnect-pro' ).'</div>
<label for="card-element"></label>
<div  class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>
<div id="card-errors" role="alert"></div>';
echo '</li>';
}

//NEW SEPA DIRECT DEBIT
if ( count($listsource->sources) < 5 && $listsource->code_client != null && ( $listsource->sepa_direct == 1 || $listsource->sepa_direct != 1 && $listsource->STRIPE == 0 ) && get_current_blog_id() == 1 ) {    
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='BkDbt' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_newbank' ";
//if ($listsource["sources"]==null) {echo " checked";}
echo " ><label class='custom-control-label w-100' for='BkDbt'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-university fa-3x fa-fw'></i></center></div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank levy', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Via SEPA Direct Debit', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
echo '<li class="list-group-item list-group-item-secondary" id="BankForm" style="display: none">';
echo "<p class='text-justify'>";
$blogname=get_bloginfo('name');
echo '<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect-pro' ), $blogname).'</small>';
echo "</p>";
echo '<input id="iban-owner" name="iban-owner" value="" type="text" onchange="ShowHideDiv()" class="form-control" placeholder="'.__( 'Owner', 'doliconnect-pro' ).'" autocomplete="off">
<div class="invalid-feedback" role="alert">'.__( 'As on your bank account', 'doliconnect-pro' ).'</div>
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- A Stripe Element will be inserted here. --></div>';
//echo '<div id="bank-name"></div>'';
echo '<div id="iban-errors" role="alert"></div>';
echo '</li>';
}

if ($mode != manage) {
if ($listsource->PAYPAL!=null && get_option('doliconnectbeta')=='1' && current_user_can( 'administrator' )){
echo "<li id='PaypalForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_paypal' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_paypal' ";
echo " ><label class='custom-control-label w-100' for='src_paypal'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fab fa-cc-paypal fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>PayPal</h6><small class='text-muted'>".__( 'Pay with your Paypal account', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

if ( $listsource->RIB != null ) {
echo "<li id='VirForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_vir' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_vir' ";
if ( $listsource->sources == null && $listsource->card != 1 ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Bank Transfer', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

if ( $listsource->CHQ != null ) {
echo "<li id='ChqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_chq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_chq' ";
if ( $listsource->sources == null && $listsource->card != 1 && $listsource->RIB == null ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
} 

if ( ! empty(dolikiosk()) ) {
echo "<li id='LiqForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='src_liq' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='modepayment' value='src_liq' ";
if ( $listsource->sources == null && $listsource->card != 1 && $listsource->CHQ == null && $listsource->RIB == null ) { echo " checked"; }
echo " ><label class='custom-control-label w-100' for='src_liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect-pro' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect-pro' )."</small>";
echo '</div></div></label></div></li>';
}

}
echo "<li class='list-group-item list-group-item-action flex-column align-items-start' id='SaveFormButton' style='display: none'>";
if ($mode != manage) {echo '<div class="custom-control custom-checkbox"><input id="savethesource" class="custom-control-input form-control-sm" type="checkbox" name="savethesource" value="1" ><label class="custom-control-label w-100" for="savethesource"><small class="form-text text-muted"> '.__( 'Save this payment method', 'doliconnect-pro' ).'</small></label></div>';}
else {echo '<div class="custom-control custom-checkbox"><input id="savethesource" type="hidden" name="savethesource" value="1"><input id="setasdefault" class="custom-control-input form-control-sm" type="checkbox" name="setasdefault" value="1" checked><label class="custom-control-label w-100" for="setasdefault"><small class="form-text text-muted"> '.__( 'Set as default payment mode', 'doliconnect-pro' ).'</small></label></div>';}
echo "</li>";
echo "</ul><div class='card-body'>";

if ($mode=='manage'){
echo "<div id='DiscountFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit' disabled><b>".__( 'Recharge', 'doliconnect-pro' )."</b></button></div>";
echo "<div id='CardFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit'><b>".__( 'Add credit card', 'doliconnect-pro' )."</b></button></div>";
echo "<div id='BankFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-lg btn-block' type='submit'><b>".__( 'Add bank account', 'doliconnect-pro' )."</b></button></div>";
if ($listsource->code_client!=null){
echo "<div id='dvDelete'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-danger btn-lg btn-block' type='submit'><b>".__( 'Delete', 'doliconnect-pro' )."</b></button></div>";
} elseif ($listsource->code_client==null && $listsource->CHQ==null && $listsource->RIB==null) {
echo "<center>".__( 'No gateway', 'doliconnect-pro' )."</center>";
}
}else{
echo "<input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><div id='CardFormButton' style='display: none'></div><div id='BankFormButton' style='display: none'></div><div id='dvDelete' style='display: none'></div><button  id='buttontopay' class='btn btn-danger btn-lg btn-block' type='submit'><b>".__( 'Pay', 'doliconnect-pro' )." ".doliprice($total,$currency)."</b></button>";
}
echo "</div></div>";
if ($mode=='manage'){
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
}
echo "</form>";
}

function dolibuttontocart($product, $category=0, $add=0, $time=0) {

$button = "<div class='jumbotron'>";

if (constant("DOLICONNECT_CART") > 0) {
$orderfo = CallAPI("GET", "/orders/".constant("DOLICONNECT_CART"), null, 0);
//$button .=$orderfo;
}

if ( $orderfo->lines != null ) {
foreach ($orderfo->lines as $line) {
if  ($line->fk_product == $product->id) {
//$button = var_dump($line);
$qty=$line->qty;
$ln=$line->id;
}
}}

$button .="<form id='product-add-form-$product->id' role='form' action='".doliconnecturl('dolishop')."?category=".$category."&product=".$product->id."'  method='post'>";
$button .="<input type='hidden' name='product_update' value='$product->id'><input type='hidden' name='product_update[".$product->id."][product]' value='$product->id'>";
$button .="<script type='text/javascript' language='javascript'>";

$button .="</script>";

$currency=$orderfo->multicurrency_code;

if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {$duration =__( 'for', 'doliconnect-pro' ).' '.$product->duration_value.' ';
if ( $product->duration_value > 1 ) {
if ( $product->duration_unit == y ) { $duration .=__( 'years', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == m )  {$duration .=__( 'months', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == d )  {$duration .=__( 'days', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == h )  {$duration .=__( 'hours', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == i )  {$duration .=__( 'minutes', 'doliconnect-pro' ); }
} else {
if ( $product->duration_unit == y ) {$duration .=__( 'year', 'doliconnect-pro' );}
elseif ( $product->duration_unit == m )  { $duration .=__( 'month', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == d )  { $duration .=__( 'day', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == h )  { $duration .=__( 'hour', 'doliconnect-pro' ); }
elseif ( $product->duration_unit == i )  { $duration .=__( 'minute', 'doliconnect-pro' ); }
}

if ( $product->duration_unit == i ) {
$altdurvalue=60/$product->duration_value; 
}

}

if ( !empty($product->multiprices_ttc) ) {
$lvl=constant("PRICE_LEVEL");
$count=1;
//$button .=$lvl;
foreach ( $product->multiprices_ttc as $level => $price ) {
if ( (constant("PRICE_LEVEL") == 0 && $level == 1 ) || constant("PRICE_LEVEL") == $level ) {
$button .='<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $price, $currency);
if ( empty($time) ) { $button .=' '.$duration; }
$button .='</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$price, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 
$button .='<small class="float-right">'.__( 'You benefit from the rate', 'doliconnect-pro' ).' '.doliconst(PRODUIT_MULTIPRICES_LABEL.$level).'</small>';
}
$count++; 
}
} else {
$button .='<h5 class="mb-1 text-right">'.__( 'Price', 'doliconnect-pro' ).': '.doliprice( $product->price_ttc, $currency);
if ( empty($time) ) { $button .=' '.$duration; } 
$button .='</h5>';
if ( !empty($altdurvalue) ) { $button .= "<h6 class='mb-1 text-right'>soit ".doliprice( $altdurvalue*$product->price_ttc, $currency)." par ".__( 'hour', 'doliconnect-pro' )."</h6>"; } 

}

if (constant("PRICE_LEVEL") > 0){
$level=constant("PRICE_LEVEL");
$price_min_ttc=$product->multiprices_min_ttc->$level;
$price_ttc=$product->multiprices_ttc->$level;
}
else {
$price_min_ttc=$product->price_min_ttc;
$price_ttc=$product->price_ttc;
}
//$button .=doliprice($price_ttc);
if ( is_user_logged_in() && $add==1 ) {
$button .="<div class='input-group'><select class='form-control' name='product_update[".$product->id."][qty]' >";
if ( ($product->stock_reel-$line->qty > '0' && $product->type == '0') ) {
if ( $product->stock_reel-$line->qty >= '10' ) {
$m2 = 10;
} elseif ( $product->stock_reel>$line->qty ) {
$m2 = $product->stock_reel;
} else { $m2 = $line->qty; }
} else {
if ( $line->qty > 1 ){ $m2=$line->qty; }
else {$m2 = 1;}
}
for ( $i=0;$i<=$m2;$i++ ) {
		if ($i==$qty){
$button .="<OPTION value='$i' selected='selected'>$i</OPTION>";
		} else {
$button .="<OPTION value='$i' >$i</OPTION>";
		}
	}
$button .="</SELECT><DIV class='input-group-append'><BUTTON class='btn btn-outline-secondary' type='submit'>";
if ( $qty > 0 ) {
$button .="".__( 'Update', 'doliconnect-pro' )."";
} else {
$button .="".__( 'Add', 'doliconnect-pro' )."";
}
$button .="</button></div></div>";
if ( $qty > 0 ) {
$button .="<br /><div class='input-group'><a class='btn btn-block btn-warning' href='".doliconnecturl('dolicart')."' role='button' title='".__( 'Go to cart', 'doliconnect-pro' )."'>".__( 'Go to cart', 'doliconnect-pro' )."</a></div>";
}
} elseif ( $add == 1 ) {
$button .="<div class='input-group'><a class='btn btn-block btn-outline-secondary' href='".wp_login_url( )."&redirect_to=".doliconnecturl('dolishop')."?category=".$_GET[category]."' role='button' title='".__( 'Login', 'doliconnect-pro' )."'>".__( 'Login', 'doliconnect-pro' )."</a></div>";
} else {
$button .="<div class='input-group'><a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Login', 'doliconnect-pro' )."'>".__( 'Contact us', 'doliconnect-pro' )."</a></div>";
}
$button .="<input type='hidden' name='product_update[".$product->id."][price]' value='$price_ttc'></form>";
$button .='<div id="product-add-loading-'.$produc->id.'" style="display:none">'.doliprice($price_ttc).'<button class="btn btn-secondary btn-block" disabled><i class="fas fa-spinner fa-pulse fa-1x fa-fw"></i> '.__( 'Loading', 'doliconnect-pro' ).'</button></div>';
$button .="</div>";
return $button;
}

 
?>
