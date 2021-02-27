<?php
/**
 * Plugin Name: Doliconnect PRO
 * Plugin URI: https://www.ptibogxiv.net
 * Description: Premium Enhancement of Doliconnect
 * Version: 5.0.0
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
print '<button type="button" class="btn btn-block btn-link text-decoration-none text-white float-end" data-bs-toggle="modal" data-bs-target="#DoliconnectSelectLang" data-bs-dismiss="modal" title="'.__('Choose language', 'doliconnect-pro').'"><span class="flag-icon flag-icon-'.strtolower(substr(pll_current_language('slug'), -2)).'"></span></button>';
} ?>
</div>
</div></nav>
<?php } 
}
?>