<?php
/**
 * Plugin Name: Doliconnect PRO
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Premium Enhancement of Doliconnect
 * Version: 4.5.0
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
 * @copyright Copyright (c) 2017-2020, ptibogxiv.net
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
 
// Adding language files
function doliconnectpro_textdomain() {
  load_plugin_textdomain( 'doliconnect-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'doliconnectpro_textdomain' );

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


function dolimembership_modal() {
if ( !empty(doliconst('MAIN_MODULE_ADHERENTSPLUS')) && (is_user_logged_in() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) ) {
global $current_user;
doliconnect_enqueues();

$delay = dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$request = "/adherentsplus/".doliconnector($current_user, 'fk_member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) {
$adherent = callDoliApi("GET", $request, null, $delay);
}

print "<div class='modal fade' id='activatemember' tabindex='-1' aria-labelledby='activatememberLabel' aria-hidden='true' data-bs-keyboard='false'>
<div class='modal-dialog modal-lg modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable'><div class='modal-content'><div class='modal-header'>";
if ( !isset($adherent->datefin) || ( $adherent->datefin>current_time( 'timestamp',1)) || ( $adherent->datefin < current_time( 'timestamp',1)) ) {
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$current_user->billing_type."')", null, $delay);
//print $typeadhesion;
print '<h4 class="modal-title" id="myModalLabel">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</h4><button id="subscription-close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>';

print '<div class="modal-body">';
/**
print '<ul class="list-group list-group-flush">
  <li class="list-group-item d-flex justify-content-between align-items-center">
    <b>Cras justo odio</b><br><small class="text-justify text-muted">test test</small>
    <div class="d-grid gap-2 col-4"><button class="btn btn-primary" type="button">Button</button></div>
  </li>
  <li class="list-group-item list-group-item-light list-group-item-action">
    <div class="d-flex w-100 justify-content-between">
      <b>Hebdomadaire - 1 semaine</b>
      <div class="d-grid gap-2 col-4"><button class="btn btn-primary btn-sm" type="button">Button</button></div>
    </div>
    <p class="mb-1">8,29 E puis 15,00 E</p>
    <small>A partir du 21/11/2020 jusquau 22/11/2020</small>
  </li>
</ul>';
*/
print "<table class='table table-striped' id ='subscription-table'>";

if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
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
if ($postadh->price_prorata != $postadh->price ) { 
print "(";
print doliprice($postadh->price_prorata)." ";
print __( 'then', 'doliconnect-pro')." ".doliprice($postadh->price);
} else {
print "(".doliprice($postadh->price_prorata);
} 
print ")"; } else { print "<span class='badge badge-pill badge-primary'>".__( 'Free', 'doliconnect-pro')."</span>"; }
print "</small></b>";
if (!empty(doliproduct($postadh, 'note'))) print "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small>";
if (!empty(number_format($postadh->federal))) print "<br><small class='text-justify text-muted '>".__( 'Including a federal part of', 'doliconnect-pro')." ".doliprice($postadh->federal)."</small>";
print "<br><small class='text-justify text-muted '>".__( 'From', 'doliconnect-pro')." ".wp_date('d/m/Y', $postadh->date_begin)." ".__( 'until', 'doliconnect-pro')." ".wp_date('d/m/Y', $postadh->date_end)."</small>";
print "</div><div class='col-md-4'>";
if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
print "<button class='btn btn-info btn-block' disabled>".sprintf(__('From %s', 'doliconnect-pro'), wp_date('d/m/Y', $adherent->next_subscription_renew))."</a>";
} elseif ( $postadh->family == '1' ) {
print "<div class='d-grid gap-2'><a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info' role='button'>".__( 'Contact us', 'doliconnect-pro')."</a></div>";
} 
elseif ( ( $postadh->statut == '0' && $postadh->id == $adherent->typeid ) ) { 
print "<button class='btn btn-secondary btn-block' disabled>".__( 'Non-renewable', 'doliconnect-pro')."</a>";
} 
elseif ( ( $postadh->automatic_renew != '1' && $postadh->id == $adherent->typeid ) ) { //to do add security for avoid loop  in revali
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";
} 
elseif ( ($postadh->automatic == '1' ) && ($postadh->id == $adherent->typeid) ) {
if ( $adherent->statut == '1' ) {
if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro')."</button></div></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";}
}
} elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";
}

} elseif (($postadh->automatic == '1') && ($postadh->id != $adherent->typeid)) {

if ( $adherent->statut == '1' ) {

if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro')."</button></div></form>";
} else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'<div class='d-grid gap-2'>><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro')."</button></div></form>";
} else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro')."</button></div></form>";}
}

} elseif ( $adherent->statut == '0' ) {

print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro')."</button></div></form>";

} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Subscribe', 'doliconnect-pro')."</button></div></form>";
}

} elseif ( ($postadh->automatic != '1' ) && ( $postadh->id == $adherent->typeid ) ) {

if ( $adherent->statut == '1' ) {

if ($adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect-pro')."</button></div></form>";
} else {
if ($adherent->datefin>current_time( 'timestamp',1)) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Renew', 'doliconnect-pro')."</button></div></form>";}
}

} elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";
}
elseif ( $adherent->statut == '-1' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect-pro')."</button></div></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";
}
}
elseif ( ($postadh->automatic != '1' ) and ( $postadh->id != $adherent->typeid) ) {
if ($adherent->statut == '1') {
if ($adherent->datefin == null ){print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";}
}
}
elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";
}
else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$adherent->next_subscription_date_start."'><input type='hidden' name='timestamp_end' value='".$adherent->next_subscription_date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect-pro')."</button></div></form>";
} 
}
}
print "</div></div></td></tr>"; 
}
} else { 
print "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect-pro')."</center></li>";
}

}
print "</table>";

print doliloading('subscription'); 

print "</div><div id='subscription-footer' class='modal-footer'><small class='text-justify'>".__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect-pro')."</small></div></div></div></div>";

}}
add_action( 'wp_footer', 'dolimembership_modal');


// ********************************************************

function doliconnect_privacy($arg) {
global $current_user;

if ( is_user_logged_in() && get_option('doliconnectbeta') == '1' && ( '999999999999999999999' < get_the_modified_date( 'U', get_option( 'wp_page_for_privacy_policy' ))) ) { //current_time( 'timestamp', 1) 

doliconnect_enqueues();

print "<script>";
?>
function DoliconnectShowPrivacyDiv() {
jQuery('#DoliconnectPrivacyModal').modal('show');
}

window.onload=DoliconnectShowPrivacyDiv;
<?php
print "</script>";

print '<div id="DoliconnectPrivacyModal" class="modal fade bd-example-modal-xl" tabindex="-1" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-show="true" data-bs-backdrop="static" data-bs-keyboard="false">
<div class="modal-dialog modal-fullscreen modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
<div class="modal-header"><h5 class="modal-title" id="exampleModalLabel">Confidentialite - Version du '.get_the_modified_date( get_option( 'date_format' ), get_option( 'wp_page_for_privacy_policy' ) ).'</h5>';
print '<button type="button" class="btn-close" disabled aria-label="Close"></button>';
print '</div><div class="modal-body" data-bs-spy="scroll">';
print apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' ))); 
print '</div>    
      <div class="modal-footer">
        <button type="button" class="btn btn-success" >'.__( 'I approve', 'doliconnect-pro').'</button>
        <a href="'.wp_logout_url( get_permalink() ).'" type="button" class="btn btn-danger">'.__( 'I refuse', 'doliconnect-pro').'</a>
      </div>
    </div>
  </div>
</div>';
}

}
add_action( 'wp_footer', 'doliconnect_privacy', 10, 1);

// ********************************************************

add_action( 'wp_body_open', 'doliconnect_networkbar' );
 
function doliconnect_networkbar() {
if (is_multisite() && !empty(get_theme_mod( 'ptibogxivtheme_networkbar_color'))) {
//echo esc_attr(get_theme_mod( 'ptibogxivtheme_networkbar_color' )); ?>
<nav class="navbar navbar-expand-md navbar-dark bg-dark"><div class="container">
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <span class="navbar-brand mb-0 h1"><i class="fas fa-globe fa-fw"></i> <?php print __( 'Our websites', 'doliconnect-pro'); ?></span>

  <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
<ul class="nav nav-pills flex-column flex-sm-row"><?php
$defaults = array(
//'site__in'=>(1),
'public'=>'1'
	);
$subsites = get_sites($defaults);
foreach( $subsites as $subsite ) {
  $subsite_id = get_object_vars($subsite)["blog_id"];
  $subsite_name = get_blog_details($subsite_id)->blogname;
  $subsite_url = get_blog_details($subsite_id)->siteurl; ?>
<li class="nav-item"><small><a class="nav-link text-white <?php if ( get_current_blog_id()==$subsite_id ) { echo "active"; } ?>" href="<?php echo $subsite_url; ?>"><?php echo $subsite_name; ?></a></small></li>
<?php } ?>
</ul></div><div class="col-2 col-md-3">  
<?php if ( function_exists('pll_the_languages') && function_exists('doliconnect_langs') ) {      
print '<button type="button" class="btn btn-block btn-link text-decoration-none text-white text-right" data-bs-toggle="modal" data-bs-target="#DoliconnectSelectLang" data-bs-dismiss="modal" title="'.__('Choose language', 'doliconnect-pro').'"><span class="flag-icon flag-icon-'.strtolower(substr(pll_current_language('slug'), -2)).'"></span></button>';
} ?>
</div>
</div></nav>
<?php } 
}
?>