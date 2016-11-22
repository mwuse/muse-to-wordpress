<?php

//WP HEADER 

//get wp_head in a var
ob_start(); wp_head(); $wp_head = ob_get_clean();

//Exclude_html_dom_bug on wp_head html
$wp_head = exclude_html_dom_bug($wp_head);

//start DOMDocument filters on wp head HTML
$head = new DOMDocument;
$head->loadHTML( $wp_head );

//exclude all css and javascript from asigned template
exclude_template_link_and_script($head);


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

//Inner url replace
//mtw_inner_url_replace($html);




//insert wp_head content in muse html
merge_child_nodes($html, "head", $head, "head", 0 );


//WP FOOTER

//get wp_head in a var
ob_start(); wp_footer(); $wp_footer = ob_get_clean();

//Exclude_html_dom_bug on wp_head html
$wp_footer = exclude_html_dom_bug($wp_footer);

//start DOMDocument filters on wp head HTML
$footer = new DOMDocument;
$footer->loadHTML( $wp_footer );

//exclude all css and javascript from asigned template
exclude_template_link_and_script($footer);

//insert wp_head content in muse html
merge_child_nodes($html, "body", $footer, "head", 0 );


//Restore muse html content 
$content = restore_html_dom_bug( $html->saveHTML() );

echo $content;
?>