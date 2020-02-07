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
?>
	<div class="container vh-100">
  <div class="row" style="height:20vh;"><div class="col-12">
  <?php if ( function_exists('pll_the_languages') && function_exists('doliconnect_langs') ) {      
echo '<a href="#" class="text-decoration-none float-right" data-toggle="modal" data-target="#DoliconnectSelectLang" data-dismiss="modal" title="'.__('Choose language', 'doliconnect').'"><span class="flag-icon flag-icon-'.strtolower(substr(pll_current_language('slug'), -2)).'"></span> '.pll_current_language('name').'</a>';
} ?>
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
