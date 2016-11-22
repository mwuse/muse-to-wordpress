<?php

function mtw_sync_muse_page()
{
	global $muse_projects;
	global $mtw_option;

	$preg_excludes = array(
		"[$]",
		"archive-",
		"single",
		"singular",
		"page",
		"category",
		"tag",
		"taxonomy",
		"author",
		"date",
		"search",
		"404",
		"attachment",
		"embed",
		"item",
		"fancy"
		);

	$preg_excludes = apply_filters( "mtw_exclude_from_sync_page", $preg_excludes );

	if( !is_admin() || !isset( $mtw_option['mtw_auto_page'] ) )
	{
		return;
	}

	/* 
	create or update pages
	*/
	$new_pages = array();
	$update_pages = array();
	$archives = array();
	$update_links = false;


	foreach ( array_keys( $muse_projects ) as $project_name ) 
	{
		
		$xml_file = TTR_MW_TEMPLATES_PATH . $project_name . '/' . 'muse_manifest.xml' ;

		if ( file_exists( $xml_file ) ) 
		{
			$xml = simplexml_load_file( $xml_file );

			foreach ( $xml->file as $value ) 
			{
				$page = array();

				$page['project'] = $project_name;
				$page['file'] = (string)$value->attributes()->name;
				$page['template'] = $page['project'] . '/' . $page['file'];
				$page['mt'] = (string)$value->attributes()->mt;

				$mtw_page = new MusePage();
				$mtw_page->init( $page['template'] , false );

				$page['title'] = $mtw_page->DOMDocument->getElementsByTagName('title')->item(0)->nodeValue;


				//check hierarchy
				$exclude = false;
				foreach ( $preg_excludes as $value ) 
				{
					preg_match("#^".$value."#", $page['file'], $matches);
					if ( $matches ) 
					{
						if( $matches[0] == "archive-" )
						{

							$archives[] = $page;
						}
						$exclude = true;
					}
				}
				if ( $exclude ) 
				{
					continue; //check next loop
				}//end check hierarchy

				$wp_page = array(
					'post_title'=> $page['title'],
					'post_name'=> sanitize_title( $page['title'] ),
					'post_content'=>"",
					'post_type'=>'page',
					'post_status'=>"publish",
					'page_template'=> $page['template']
					);

				$isset_page = ttr_get_page_by_template( $page['template'] );

				if ( !$isset_page )
				{
					$page_id = wp_insert_post( $wp_page, $error_insert );
					
					if( $page_id != 0 )
					{
						$new_pages[] = $page;
						update_post_meta( $page_id, 'muse_mt', $page['mt'] );
						update_post_meta( $page_id, 'mtw_auto_created', true );
						$update_links = true;
					}
					
				}
				else
				{
					$old_mt = get_post_meta( $isset_page[0]['ID'], 'muse_mt', true );

					if( $old_mt != $page['mt'] )
					{
						$wp_page['ID'] = $isset_page[0]['ID'];						
						wp_update_post( $wp_page, $wp_error );

						//update link
						update_post_meta( $isset_page[0]['ID'], 'muse_mt', $page['mt'] );
						$update_links = true;
					}					
				}
			}	
		}
	}

	/*
	Default home
	*/
	$home = ttr_get_page_by_template( $mtw_option['mtw_default_project'] . '/' . 'index.html' )[0];
	if ( $home )
	{
		update_option( 'page_on_front', $home['ID'] );
		update_option( 'show_on_front', 'page' );
	}

	$blog = ttr_get_page_by_template( $mtw_option['mtw_default_project'] . '/' . 'archive.html' )[0];

	if ( $blog )
	{
		update_option( 'page_for_posts', $blog['ID'] );
	}
	elseif ($home) 
	{
		update_option( 'page_for_posts', $home['ID'] );
		update_option( 'show_on_front', 'post' );
	}
	

	/* 
	Declare archive
	*/
	foreach ($archives as $key => $archive ) 
	{
		preg_match("#archive-(.*).html#", $archive['file'], $matches);
		if ( $matches ) 
		{
			$archives[$key]['post_type'] = $matches[1];
		}
	}

	if( $archives )
	{
		update_option( "mtw_archives_auto", $archives );
	}

	$args_delete_pages = array(
		'post_type' => 'page',
		'posts_per_page' => -1,
		'meta_key' => 'mtw_auto_created',
		'meta_value' => true,
		);

	$query_delete_pages = new WP_Query($args_delete_pages);

	foreach ($query_delete_pages->posts as $key => $page) 
	{
		if( !file_exists( TTR_MW_TEMPLATES_PATH . get_post_meta( $page->ID, '_wp_page_template', true ) ) )
		{
			wp_delete_post(  $page->ID , true );
		}
	}

	if( $update_links == true )
	{
		wp_redirect( admin_url() . "admin.php?page=mtw-projects&update_all_logic_links=1" );
	}
	
}

add_action( 'wp_loaded', 'mtw_sync_muse_page', 2 );
?>