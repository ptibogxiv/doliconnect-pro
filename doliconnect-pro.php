<?php
/**
 * Plugin Name: Doliconnect PRO
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Premium Enhancement of Doliconnect
 * Version: 3.10.3
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
print "</small></b><br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small></div><div class='col-md-4'>";
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

?>
