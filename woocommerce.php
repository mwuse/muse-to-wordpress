<?php

ob_start();

?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			if ( have_posts() ) :

			woocommerce_content(); 

			endif; 
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php

global $woocommerce_content;
$woocommerce_content = ob_get_clean();

global $is_woocommerce;
$is_woocommerce = true;


ttr_template_filter( $template, true ) ;

global $museUrl;

global $wp_query;

if( !is_null($museUrl) )
{
	require( TTR_MW_PLUGIN_DIR . 'default-template-5.php' );
}
else
{
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<?php wp_title(); ?>
		<?php wp_head(); ?>
	</head>
	<body>
	<?php echo $woocommerce_content; ?>
	<?php wp_footer(); ?>
	</body>
	</html>
	<?php
}



