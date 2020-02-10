<!DOCTYPE html> 
<html class="no-js">
<html <?php language_attributes(); ?>>
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
	<div class="container">
  <div class="row" style="height:20vh;"><div class="col-12">

  </div></div>
  <div class="row"><div class="col-12">
<?php if(have_posts() && ( (is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount') ) || (is_page(doliconnectid('dolicontact')) && !empty(doliconnectid('dolicontact') ) ))) ): while(have_posts() ) : the_post(); ?>
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
<?php //get_footer(); ?>
<?php wp_footer(); ?>
</html>
