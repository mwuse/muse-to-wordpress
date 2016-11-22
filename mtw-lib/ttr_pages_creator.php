<?php 
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if(is_user_logged_in() && current_user_can("administrator")){

	$links= array();
	$countExisting=0;
	$countAll=0;

	foreach (ttr_get_muse_html_array($_POST["pages"]) as $pageKey => $page) {

		$countAll++;
				
		$fileUrl = TTR_MW_PLUGIN_URL.'templates/'.$pageKey;
		libxml_use_internal_errors(true);
		$html->loadHTMLFile($fileUrl);
		$title=$html->getElementsByTagName('title')->item(0)->nodeValue;

		if(get_page_by_title($title)===NULL){

			$id=wp_insert_post(array(
				'post_title'=>$title,
				//'post_name'=>"",
				'post_content'=>"",
				'post_type'=>'page',
				'post_status'=>"publish",
				'page_template'=>$pageKey
				));
			$exploded=explode('/', $pageKey);
			$links [ utf8_uri_encode( $exploded [ 1 ] ) ] = $id;
			if($exploded[1]==="index.html"){
				update_option( 'page_on_front', $id );
		    	update_option( 'show_on_front', 'page' );
			}
		}//end if(get_page_by_title($title)!==NULL)
		else
			$countExisting++;
	}//end foreach
	// echo'<pre>';
	// print_r($links);
	// exit();
	$url=$_SERVER['HTTP_REFERER'];

	if($countExisting===$countAll){
		echo "<script type='text/javascript'>";
		echo "alert('all pages were allready created');";
		//redirect to the previous page
		echo "window.location.href = '$url';";
		echo "</script>";
	}

	else{
		$message=$countAll-$countExisting." page(s) created, ".$countExisting." were already created.";
		echo "<script type='text/javascript'>";
		echo "alert('$message');";
		echo "window.location.href = '$url';";
		echo "</script>";
		//update_option( "muse_linker_replace_".strtolower($_POST["pages"]), json_encode($links), "no" );
	}

	exit();
}//end if is logged and admin
else
echo 'Nice try, this page is protected';
?>