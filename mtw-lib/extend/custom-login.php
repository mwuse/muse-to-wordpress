<?php


function mtw_exclude_wp_login( $excludes )
{
	$excludes[] = "wp-login";
	return $excludes;
}

//add_filter( "mtw_exclude_from_sync_page", "mtw_exclude_wp_login" );

function head_custom_login( $head )
{
	global $wp_filesystem;
	WP_Filesystem();
	
	$login_min_css =  $wp_filesystem->get_contents("http://192.168.1.30/tastzik.beta/wp-admin/css/login.min.css?ver=4.5.2");

	$login_min_css = preg_replace("#\.login#", ".mtw-login", $login_min_css);

	$login_min_css = preg_replace("#\,#", ", .mtw-login-container ", $login_min_css);

	$login_min_css = preg_replace("#\}#", "} .mtw-login-container ", $login_min_css);

	ob_start();

	?>
	<link rel="stylesheet" id="buttons-css" href="http://192.168.1.30/tastzik.beta/wp-includes/css/buttons.min.css?ver=4.5.2" type="text/css" media="all">
	<!--<link rel="stylesheet" id="login-css" href="http://192.168.1.30/tastzik.beta/wp-admin/css/login.min.css?ver=4.5.2" type="text/css" media="all">-->




	<style type="text/css">

	<?php echo $login_min_css ?>
	/*body, html {
	    background: none;
	}*/
	.login form, .login #nav{
		padding: 0;
		background: none;
		box-shadow: none;
	}
	
	</style>
	<?php

	return ob_get_clean() . $head;
}

//add_filter( 'head_html_filter', 'head_custom_login' );

function mtw_custom_login_callback($buffer) 
{
	libxml_use_internal_errors( true );

	$wp_login_dom = new DOMDocument;
	$wp_login_dom->loadHTML( $buffer );

	$wp_login_body_class = $wp_login_dom->getElementsByTagname('body')->item(0)->getAttribute("class");

	$domNodeList = $wp_login_dom->getElementsByTagname('h1'); 
	foreach ( $domNodeList as $domElement ) 
	{
	  $domElement->parentNode->removeChild($domElement);
	}

	global $mtw_login_html;
	$mtw_dom = new DOMDocument;
	$mtw_dom->loadHTML( $mtw_login_html );
	
	$mtw_dom_body = $mtw_dom->getElementsByTagname('body')->item(0);
	$mtw_dom_body_class = $mtw_dom_body->getAttribute("class");
	$mtw_dom_body->setAttribute( "class", $mtw_dom_body_class . " " . $wp_login_body_class );

	$div = $wp_login_dom->getElementsByTagname('div')->item(0); 
	$new_node = $mtw_dom->importNode($div, true);

	$wp_login_form = dom_getElementsByClass($mtw_dom, 'mtw-login')->item(0);
	$wp_login_form->nodeValue = "";
	
	$wp_login_form->appendChild($new_node);
	
	//return $wp_login_body;

	return $mtw_dom->saveHTML();
}

function mtw_custom_login_buffer_start() { ob_start("mtw_custom_login_callback"); }

function mtw_custom_login_buffer_end() { ob_end_flush(); }


function mtw_custom_login_init()
{
	global $mtw_option;
	$mtw_option = get_option( 'mtw_option' );

	$current_project = ttr_get_muse_html_array( $mtw_option['mtw_default_project'] );

	

	$current_file_url =  preg_replace("#\?.*#", "", basename( $_SERVER['REQUEST_URI'] ) );

	if( $current_file_url == "wp-login.php" && $current_project[ $mtw_option['mtw_default_project'] . '/wp-login.html' ] )
	{
		
		$detect = new Mobile_Detect;
		$museUrl = $mtw_option['mtw_default_project'] . '/wp-login.html';
		global $mtw_login_html;
		ob_start();
		require_once( TTR_MW_PLUGIN_DIR . "default-template-5.php" );
		$mtw_login_html = ob_get_clean();


		add_action('wp_loaded', 'mtw_custom_login_buffer_start');
		add_action('shutdown', 'mtw_custom_login_buffer_end');
	}

}

//add_action( "init", "mtw_custom_login_init" );
?>