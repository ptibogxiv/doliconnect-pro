<?php header('HTTP/1.1 503 Service Temporarily Unavailable'); header('Status: 503 Service Temporarily Unavailable'); header('Retry-After: 600'); ?>
<!DOCTYPE html> 
<html class="no-js">
<html lang="fr">
<head>
  <title><?php bloginfo('name'); ?></title>
  <meta name="description" content="">
  <meta charset="utf-8">
  <meta name="theme-color" content="#<? echo get_background_color(); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="language" content="French">
  <link rel="icon" href="<?php echo get_site_icon_url(); ?>" type="image/x-icon">
  <link rel="shortcut icon" href="<?php echo get_site_icon_url(); ?>" type="image/x-icon">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	
	<!-- Calculated Styles -->
	<style type="text/css">
	
	html {
		height: 100%;
		overflow: hidden;
	}
	
	.flexbox #cspio-page{
		align-items: center;
		justify-content: center;
	}

	</style>
</head>
<body <?php echo body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
} else {
    do_action( 'wp_body_open' );
}
?><?php
if (is_multisite() && !empty(get_theme_mod( 'ptibogxivtheme_networkbar_color'))) { ?>
<div class="text-dark bg-<?php echo "dark"; //echo esc_attr(get_theme_mod( 'ptibogxivtheme_networkbar_color' )); ?>">
<div class="<?php echo esc_attr(get_theme_mod('ptibogxivtheme_container_type')); ?> d-none d-md-block"><ul class="nav nav-pills">
<li class="nav-item"><small> <?php   
echo '<div class="nav-link text-white disabled"><i class="fas fa-globe fa-fw"></i>';
echo esc_attr( get_network()->site_name ); 
?></div></small></li><?php
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
</ul>  
<?php if ( function_exists('pll_the_languages') && function_exists('doliconnect_langs') ) {      
echo '<a href="#" class="text-decoration-none bg-light text-dark float-right" data-toggle="modal" data-target="#DoliconnectSelectLang" data-dismiss="modal" title="'.__('Choose language', 'doliconnect').'"><span class="flag-icon flag-icon-'.strtolower(substr(pll_current_language('slug'), -2)).'"></span> '.pll_current_language('name').'</a>';
} ?>
</div></div><?php } ?>
	<div class="container vh-100">
  <div class="row" style="height:20vh;"><div class="col-12">

  </div></div>
  <div class="row"><div class="col-12">
<?php if(have_posts() && (is_page(doliconnectid('doliaccount')) || is_page(doliconnectid('dolicontact'))) ): while(have_posts()): the_post(); ?>
  <article role="article" id="post_<?php the_ID()?>" <?php post_class()?>> 
    <?php the_content()?>
  </article>
<?php endwhile; else: ?>
<?php
$queried_post = get_post(doliconnectid('doliaccount'));
$content = $queried_post->post_content;
$content = apply_filters('the_content', $content, true);
$content = str_replace(']]>', ']]&gt;', $content);
echo $content;
?>
<?php endif; ?>					    									    			    			    							    			     			    		
	</div></div>
  </div>

</body>
<?php wp_footer(); ?>
</html>
