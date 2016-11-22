<?php

function ttr_get_folder_by_template($template){
	$museTheme = 'plugins/muse-wordpress/templates';
	$var1 = explode(TTR_MW_PLUGIN_DIR, $template);
	$var2 = explode('/', $var1[1]);
	return $var2[1];
}

function ttr_get_page_by_template($template){

	$pages = get_pages(array(
		'meta_key' => '_wp_page_template',
		'meta_value' => $template,
		'post_status' => array(
			'publish',
			'pending',
			'draft',
			'auto-draft',
			'future',
			'private',
			'inherit',
			'trash'
			)
	));

	$return = array();

	foreach ( $pages as $key => $page )
	{
		$return[$key]['ID'] = $page->ID;
		$return[$key]['title'] = $page->post_title;
	}
	return array_values( $return );
}


?>