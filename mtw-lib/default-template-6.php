<?php

global $mtw_head;
global $wp_head;
global $body_loaded;
global $muse_footer;
global $mtw_page;

/*$load_header = false;
$load_footer = false;*/

$page = new MusePage;
$page->init( str_replace( TTR_MW_TEMPLATES_PATH , "", $museUrl ) );
$mtw_page = $page;



$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');


$file = str_replace( TTR_MW_PLUGIN_DIR , TTR_MW_PLUGIN_URL , $museUrl );


//$folderName = ttr_get_folder_by_template( $museUrl );
$projectName = $folderName;

//MUSE CONTENT
$html = $page->DOMDocument;


$html_class = $html->getElementsByTagName('html')->item(0)->getAttribute('class');


?>
<!DOCTYPE html>
<html class="<?php echo $html_class; ?>" lang="<?php echo get_locale(); ?>" >
<head>
	
	<?php

	//Responsive with mobile detect library 
	//repaire_responsive($html);

	//Change url for script and link tag
	repaire_link_script($html);

	//MTW query
	mtw_post_list_query($html);

	//MTW ophan query
	mtw_orphan_query($html);
	?>

	<?php
	$mtw_head = new DOMDocument;
	$mtw_head->loadHTML('<head></head>');
	merge_child_nodes($mtw_head, "head", $html, "head", 0 );

	do_action( "DOMDocument_mtw_head_load", $mtw_head );

	$str_mtw_head = preg_replace(array("/^\<\!DOCTYPE.*?<html><head>/si",
                                      "!</head></html>$!si"),
                                "",
                                restore_html_dom_bug( $mtw_head->saveHTML() ) );
	
	echo apply_filters( 'head_html_filter', $str_mtw_head);
	
	
	//get wp_head in a var
	ob_start(); wp_head(); $wp_head = ob_get_clean();

	//Exclude_html_dom_bug on wp_head html
	$wp_head = exclude_html_dom_bug($wp_head);

	//start DOMDocument filters on wp head HTML
	$head = new DOMDocument;
	$head->loadHTML( $wp_head );

	//exclude all css and javascript from asigned template
	exclude_template_link_and_script($head);
	
	$wp_head = preg_replace(array("/^\<\!DOCTYPE.*?<html><head>/si",
                                      "!</head></html>$!si"),
                                "",
                                restore_html_dom_bug( $head->saveHTML() ) );

	if( $load_header )
	{
	echo $wp_head;
	}
	
	?>
	<script type="text/javascript">
	jQuery.noConflict();
	var $ = jQuery;
	</script>
	<?php

	?>

</head>
<body <?php body_class(); ?> >

	<?php
	$mtw_body = new DOMDocument;
	$mtw_body->loadHTML('<body></body>');
	merge_child_nodes($mtw_body, "body", $html, "body", 0 );

	$to_deletes = array();

	$muse_footer = new DOMDocument();
	$muse_footer->loadHTML('<body></body>');
	$muse_footer_body = $muse_footer->getElementsByTagName('body')->item(0);

	//move muse footer
	$sript_for_footer = $mtw_body->getElementsByTagName('script');
	

	foreach ($sript_for_footer as $key => $script) {
		$to_deletes[] = $script;
	}
	foreach ($to_deletes as $key => $to_delete) 
	{
		$nodeImported = $muse_footer->importNode($to_delete, true);
		$muse_footer_body->appendChild($nodeImported);

		//$to_delete->parentNode->removeChild($to_delete);
	}


	do_action( "DOMDocument_body_load", $mtw_body );

	$str_mtw_body = preg_replace(array("/^\<\!DOCTYPE.*?<html><body>/si",
                                      "!</body></html>$!si"),
                                "",
                                restore_html_dom_bug( $mtw_body->saveHTML() ) );
	
	if( $do_shortcode )
	{
		$str_mtw_body = do_shortcode( $str_mtw_body );
	}

	$str_mtw_body = exclude_html_dom_bug( $str_mtw_body );

	$body_loaded = new DOMDocument;
	$body_loaded->loadHTML( '<meta http-equiv="content-type" content="text/html; charset=' . get_bloginfo( 'charset' ) . '">' . $str_mtw_body );

	do_action( "DOMDocument_body_loaded", $body_loaded );

	$str_mtw_body = preg_replace(array(
									"/^\<\!DOCTYPE.*?<body>/si",
                                    "!</body></html>$!si"),
                                	"",
                                restore_html_dom_bug( $body_loaded->saveHTML() ) );

	
	echo apply_filters( 'body_html_filter', $str_mtw_body);
	
	if( $load_footer )
	{
		wp_footer();
	}

	?>
	<script type="text/javascript">
	jQuery.noConflict();
	var $ = jQuery;
	</script>
	<?php
	
	/*echo preg_replace(array(
					"/^\<\!DOCTYPE.*?<body>/si",
                    "!</body></html>$!si"),
                	"",
                 $muse_footer->saveHTML() );*/
    
	?>
</body>
</html>
