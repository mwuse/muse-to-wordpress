<?php

function remove_muse_preview_elements($dom, $parent)
{
	$muse_previews = dom_getElementsByClass( $dom , "muse-preview" );
	foreach ($muse_previews as $key => $muse_preview) {
		$muse_preview->parentNode->removeChild($muse_preview);
	}
	

}
add_action( 'DOMDocument_loaded', 'remove_muse_preview_elements', 1, 2 );

?>