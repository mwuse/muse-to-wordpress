<?php

function mtw_logic_template_redirect($html, $fileurl)
{
	global $post;
	global $redirect_file;

	$post_id = get_the_id();
	$folder = explode("/", $fileurl);
	$folder = $folder[0];

	$metas = $html->getElementsByTagName( 'meta' );
	
	$logic = array();
	$count = 0;
	$to_delete = array();
	

	foreach ($metas as $meta) {
		if( $meta->getAttribute('type') == 'logic' )
		{

			$logic[$count]['subject'] = $meta->getAttribute('subject');
			$logic[$count]['key'] = $meta->getAttribute('key');
			$logic[$count]['value'] = $meta->getAttribute('value');
			$logic[$count]['page'] = $meta->getAttribute('page');

			$count++;

			$to_delete[] = $meta;			
		}
	}

	foreach ($to_delete as $delete) {
		$delete->parentNode->removeChild( $delete );
	}



	$redirect = false;
	foreach ($logic as $value) {

		if( $value['subject'] == 'meta')
		{
			if( get_post_meta( $post_id, $value['key'] , true ) == $value['value'] )
			{
				$redirect = $value['page'];
			}
		}
	}


	global $force_redirect;

	if( $force_redirect )
	{
		$redirect = $force_redirect;
	}


	
	if( $redirect )
	{	
		/*echo '<pre>';
		print_r( $redirect );
		exit();*/
		$redirect_file = $redirect;
		$html->loadHTMLFile( TTR_MW_TEMPLATES_PATH . $folder . '/' . $redirect );	
	}
	
	
}

add_action('DOMDocument_loaded', 'mtw_logic_template_redirect', 10, 2 );
?>