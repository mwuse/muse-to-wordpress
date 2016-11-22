<?php


global $mtw_items_called;
$mtw_items_called = array();

function mtw_import_item( $target_dom, $target_container, $item_path )
{
	global $mtw_item_links;
	global $mtw_items_called;
	global $wp_filesystem;
	WP_Filesystem();

	$mtw_item_links = array();
	

	$page = new MusePage;
	$page->init( str_replace( TTR_MW_TEMPLATES_PATH , "", $item_path ) );
	$itemDom = $page->DOMDocument;

	repaire_link_script($itemDom);



	$container_class = str_replace( ".", "-", $page->base_name );
	$temp_ct_class = $target_container->getAttribute("class");
	$target_container->setAttribute( 'class', $temp_ct_class . " " . $container_class );


	foreach ($itemDom->getElementsByTagName('link') as $value) {
		$uniqueKey = $value->getAttribute('href');
		$mtw_item_links[$uniqueKey] = $value;
	}
	foreach ($itemDom->getElementsByTagName('style') as $value) {
		$uniqueKey = $value->nodeValue;
		$mtw_item_links[$uniqueKey] = $value;
	}
	foreach ($itemDom->getElementsByTagName('script') as $value) {
		$uniqueKey = $value->getAttribute('href');
		if( !$uniqueKey )
		{
			$uniqueKey = $value->nodeValue;
		}
		$mtw_item_links[$uniqueKey] = $value;
	}

	// id to class
	$allItems = $itemDom->getElementsByTagName('*');
	foreach ($allItems as $item) {
		$id = $item->getAttribute('id');
		if( $id )
		{
			$class =  $item->getAttribute('class');

			if( $id == 'page' )
			{
				$item->removeAttribute('id');
				$id = 'single-item';
				$item->setAttribute( 'class' , $class . ' ' . $id );
			}
			elseif ( $id == 'page_position_content' ) 
			{
				$item->removeAttribute('id');	
			}
		}
	}

	$import = new DOMDocument();
	$import->loadHTML( do_shortcode( $itemDom->saveHTML() ) );
	
	$nodeToImport = dom_getElementsByClass( $import , "single-item" )->item(0);	
	
	$nodeImported = $target_dom->importNode($nodeToImport, true);

	$nodeImported->removeAttribute('id');

	$target_container->appendChild( $nodeImported );




	// exclude script link if in primary dom

	if( !in_array($container_class, $mtw_items_called) )
	{
		$mtw_items_called[] = $container_class;
		global $mtw_head;
		global $muse_footer;
		$doms = array( $target_dom, $mtw_head, $muse_footer );


		foreach ($doms as $value) {
			
			$scripts =  $value->getElementsByTagName('script') ;
			$links =  $value->getElementsByTagName('link') ;

			foreach ($mtw_item_links as $key => $value2) {
				
				
				$delete = false ;

				if($value2->tagName == 'link')
				{
					foreach ($links as $link) {
						if( mtw_exclude_get( $link->getAttribute('href') ) ==  mtw_exclude_get( $value2->getAttribute('href') ) )
						{
							$delete = true;
						}
					}
				}

				if($value2->tagName == 'script' && $value2->getAttribute('src') )
				{
					foreach ($scripts as $script) {
						if( mtw_exclude_get( $script->getAttribute('src') ) == mtw_exclude_get( $value2->getAttribute('src') ) )
						{
							$delete = true;
						}
					}
				}				

				if($value2->tagName == 'script' && !empty( $value2->nodeValue ) && $scripts->length > 0 )
				{
					foreach ($scripts as $script) {
						if( $script->textcontent == $value2->textcontent )
						{
							$delete = true;
						}
					}
				}

				if( $delete ){
					unset($mtw_item_links[$key]);
				}
				unset($finded);
			}
		}

		$mtw_item_links = array_values( $mtw_item_links );


		
		$cssContent = "";
		foreach ($mtw_item_links as $key => $value) {
			if( $value->tagName == "link" )
			{
				$cssContent.= $wp_filesystem->get_contents( mtw_exclude_get( str_replace( TTR_MW_TEMPLATES_URL, TTR_MW_TEMPLATES_PATH, $value->getAttribute('href') ) ) );
			}
		}
		
		// join unique css
		$parent_CSS_class = $container_class;


		$pattern = array(
			'#\.html|body#',
			'#\#page_position_content#',
			'#\#page#',
			'#\#muse_css_mq,#'
			);
		$replacement = array(
			'.'.$parent_CSS_class . ' .position_content',
			'.'.$parent_CSS_class . ' .position_content',
			'.'.$parent_CSS_class . ' .single-item',
			''
			);

		

		$cssContent = preg_replace( $pattern, $replacement, $cssContent );
		

		$cssDOM = new DOMDocument();
		$cssDOM->loadHTML( '<div class="item-style" ><style type="text/css">' . $cssContent . '</style></div>' );


		$styleDom = $cssDOM->getElementsByTagName('div')->item(0);
		$cssImported = $target_dom->importNode($styleDom, true);


		$target_style = $target_dom->getElementsByTagName("body")->item(0)->childNodes->item(0);


		$target_style->parentNode->insertBefore($cssImported, $target_style);
		
	}
}

?>