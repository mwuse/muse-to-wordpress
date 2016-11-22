<!DOCTYPE html>
<html class="html">
<head>
	<?php	
	ob_start();
	wp_head(); 
	$wp_head = ob_get_clean();

	//exclude style from active themeplate
	$themeUrl = addslashes( get_template_directory_uri() );
	preg_match_all("#&lt;link.*$themeUrl.*\/&gt;#", htmlentities($wp_head), $themelinks);


	foreach ($themelinks[0] as $key => $themelink) {

		$wp_head = str_replace( html_entity_decode($themelink) , '', $wp_head );
	}

	echo $wp_head;



	$page = new MusePage;
	$page->init(str_replace(TTR_MW_TEMPLATES_PATH, "", $museUrl));


	$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');

	$file = str_replace( TTR_MW_PLUGIN_DIR , TTR_MW_PLUGIN_URL , $museUrl );
	$folderName = ttr_get_folder_by_template( $museUrl );
	$projectName = $folderName;

	//MUSE CONTENT
	$html = $page->DOMDocument;


	//Responsive with mobile detect library 
	repaire_responsive($html);

	//Change url for script and link tag
	repaire_link_script($html);

	//MTW query
	mtw_post_list_query($html);

	//MTW ophan query
	mtw_orphan_query($html);

	$content = restore_html_dom_bug( $html->saveHTML() );

	preg_match_all('#(&lt;head(\s*[a-zA-Z]*=(&quot;|\')[a-zA-Z ]*(&quot;|\'))*&gt;)(.*)&lt;\/head&gt;#is', htmlentities($content), $head);

	echo html_entity_decode( $head[5][0] );

	preg_match_all('#(&lt;body(\s*[a-zA-Z]*=(&quot;|\')[a-zA-Z ]*(&quot;|\'))*&gt;)(.*)&lt;\/body&gt;#is', htmlentities($content), $body);

	?>
</head>
<body <?php body_class(); ?> >



<?php
echo html_entity_decode( $body[5][0] ) ;
?>

<?php wp_footer(); ?>
</body>
</html>