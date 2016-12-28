<?php

libxml_use_internal_errors( true );
libxml_clear_errors();

define("TTR_MW_PLUGIN_DIR", get_template_directory() . '/mtw-lib/' );
define("TTR_MW_PLUGIN_URL", get_template_directory_uri() . '/mtw-lib/' );

define("TTR_MW_TEMPLATES_PATH", ABSPATH . 'mtw-themes/' );
define("TTR_MW_TEMPLATES_URL", site_url() . '/mtw-themes/' );


global $museUrl;
global $html;
global $tempDOMDocument;
global $folderName;
global $projectName;
global $deviceType;
global $mtwQuery;
global $load_header;
global $load_footer;
global $do_shortcode;
global $mtw_page;

require_once TTR_MW_PLUGIN_DIR . "Mobile-Detect-2.8.15/Mobile_Detect.php";



$detect = new Mobile_Detect;
$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');


foreach ( glob( TTR_MW_PLUGIN_DIR . "functions/*.php" ) as $file ) { 
	if( substr( basename($file) , 0, 1 ) != "#" )
	{
		include_once $file; 
	}
}
foreach ( glob( TTR_MW_PLUGIN_DIR . "class/*.php" ) as $file ) { 
	if( substr( basename($file) , 0, 1 ) != "#" )
	{
		include_once $file; 
	}
}
foreach ( glob( TTR_MW_PLUGIN_DIR . "settings/*.php" ) as $file ) { 
	if( substr( basename($file) , 0, 1 ) != "#" )
	{
		include_once $file; 
	}
}
foreach ( glob( TTR_MW_PLUGIN_DIR . "extend/*.php" ) as $file ) { 
	if( substr( basename($file) , 0, 1 ) != "#" )
	{
		include_once $file; 
	}
}



$html = new DOMDocument;
$tempDOMDocument = new DOMDocument;
$detect = new Mobile_Detect;
$do_shortcode = true;
$load_header = true;
$load_footer = true;


/* Actions */
add_action( 'admin_enqueue_scripts', 'TTR_MW_load_admin_head' );
add_action( 'admin_menu', 'register_ttr_page_linker' );

//add_action( 'admin_menu', 'register_option_pll' );
//add_action( 'admin_menu', 'register_template_page_translation' );

/* Filters */
add_filter( 'template_include', 'ttr_template_filter', 99 );
add_filter( 'mtw_query_filter', 'category__in_by_slug', 10, 3 );



function mtw_enqueue_front_style()
{
 	wp_enqueue_style( "mtw-front-style", TTR_MW_PLUGIN_URL . 'front-style.css' );
 	wp_enqueue_script('jquery');
}
add_action( 'wp_enqueue_scripts' , 'mtw_enqueue_front_style' );

function mtw_post_list_thumbnail($item, $post)
{
	$value = $item->childNodes->item(1)->nodeValue;
	preg_match_all("#background-image[ ]*[:][ ]*url\([ ]*[\'|\"](.*)[\'|\"][ ]*\)[ ]*;#", $value, $matches);
	$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), "thumbnail" );
	if(!$thumb)
	{
		$thumb = array();
		$thumb[0] = TTR_MW_PLUGIN_URL."images/no-image.png";
	}
	$value = str_replace($matches[1][0], $thumb[0], $value);
	$item->childNodes->item(1)->nodeValue = $value;
	//$item->childNodes->item(2)->nodeValue = "";
}
add_filter('mtw_post_list_thumbnail', 'mtw_post_list_thumbnail',10,3);


function mtw_post_list_nav($item, $post)
{
	global $mtwQuery;
	global $tempDOMDocument;
	global $html;
	$pageQuery = new WP_Query($mtwQuery);

	/*echo '<pre>';
	print_r($pageQuery->max_num_pages);
	exit();*/

	$mtwQuery['paged'] = max( 1, get_query_var('paged') );

	$big = "99";

	$mid_size = 6 - max( 1, get_query_var('paged') );
	if($mid_size < 1) $mid_size = 1;


	$pagination = paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => $pageQuery->max_num_pages,
		'mid_size' => $mid_size,
		'end_size' => 3
	) );
	if($pagination)
	{
		$item->nodeValue = "";
		$tempDOMDocument->loadHTML( $pagination );
		$childNodes = $html->importNode( $tempDOMDocument->getElementsByTagName('body')->item(0) , true);
		$item->appendChild( $childNodes );
	}else{
		$item->nodeValue = "no more result";
	}
}

add_filter('mtw_post_list_nav', 'mtw_post_list_nav',10,3);


//create custom dir in wp-content
//liste automatique tout les .css, .php & .js du dossier

function create_mtw_custom_code_dir()
{

	global $wp_filesystem;
	WP_Filesystem();

	$mtw_theme_path = ABSPATH . 'mtw-themes';
	
	if ( !file_exists($mtw_theme_path) ) 
	{
	
	$wp_filesystem->mkdir( $mtw_theme_path , 0777, true);

	$txt = "This forlder is for your html muse export \n
Create a folder by project \n
- mtw-themes/myProject1/index.html \n
- mtw-themes/myProject2/index.html \n";
	$wp_filesystem->put_contents($mtw_theme_path."/readme.txt", $txt);
	
	}
	
	

	$mtw_content_path = ABSPATH . 'mtw-codes';
	
	if ( !file_exists($mtw_content_path) ) 
	{
	
	$wp_filesystem->mkdir( $mtw_content_path , 0777, true);
	
	//$readmeCodes = fopen($mtw_content_path."/readme.txt", "w") or die("Unable to open file!");
	$txt = "This forlder is for your custom code .css .php .js \n
For exemples \n
- mtw-themes/mystyle.css \n
- mtw-themes/myeffect.js \n
- mtw-themes/mycode.php \n
\n
\n
.js\n 
your files is linked in the footer\n
you can use jquery\n
use: jQuery( document ).ready( function($) {  } );\n
\n
.php\n
you can use all wordpress function
included in a init action of Wordpress
";
	$wp_filesystem->put_contents($mtw_content_path."/readme.txt", $txt, FS_CHMOD_FILE);


	}

	$ver = '2.6';
	
	foreach ( glob( $mtw_content_path . "/*.php" ) as $file ) 
	{ 
		include_once $file; 
	} 

	foreach ( glob( $mtw_content_path . "/*.css" ) as $file ) 
	{ 
		$css_src = str_replace(ABSPATH, site_url().'/', $file);
		//print_r( $css_src ); 
		wp_enqueue_style( basename($file), $css_src, array(), $ver );
	} 

	foreach ( glob( $mtw_content_path . "/*.js" ) as $file ) 
	{ 
		$js_src = str_replace(ABSPATH, site_url().'/', $file);
		wp_enqueue_script( basename($file), $js_src, array('jquery'), $ver, true );
	} 
	
}

add_action('init' , 'create_mtw_custom_code_dir' );





?>