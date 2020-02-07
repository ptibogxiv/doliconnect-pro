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

	body {
		height:100%;
		overflow: auto;
		-webkit-overflow-scrolling: touch;
	}
	
	body{
		height:100%;
		background: #ffffff url(https://images.pexels.com/photos/616404/pexels-photo-616404.jpeg?ixlib=rb-0.3.5&q=80&fm=jpg&crop=entropy&w=1080&fit=max&s=be8f13a3ec5d152f60ede73809372c97); no-repeat center bottom fixed;
		-webkit-background-size: cover;
		-moz-background-size: cover;
		-o-background-size: cover;
		background-size: cover;
	}

	#cspio-page{
		background-color: rgba(0,0,0,0);
	}
	
	.flexbox #cspio-page{
		align-items: center;
		justify-content: center;
	}

	::-webkit-input-placeholder {
		font-family:Helvetica, Arial, sans-serif;
		font-weight: 400;
		font-style: ;
	}

	::-moz-placeholder {
		font-family:Helvetica, Arial, sans-serif;
		font-weight: 400;
		font-style: ;
	} 

	:-ms-input-placeholder {
		font-family:Helvetica, Arial, sans-serif;
		font-weight: 400;
		font-style: ;
	} 

	:-moz-placeholder {
		font-family:Helvetica, Arial, sans-serif;
		font-weight: 400;
		font-style: ;
	}


	</style>
</head>
<body>

	<div class="container mh-100">
  <div class="row" style="height:20vh;"></div>
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
