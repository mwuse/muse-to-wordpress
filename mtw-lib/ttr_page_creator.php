<?php

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );
if(is_user_logged_in() && current_user_can("administrator")){
	$result=wp_insert_post(array(
		'post_title'=> $_POST["page"],
		'post_name'=> sanitize_title( $_POST["page"] ),
		'post_content'=>"",
		'post_type'=>'page',
		'post_status'=>"publish",
		'page_template'=>$_POST["template"]
		));

	header("Location: ".$_SERVER['HTTP_REFERER']);
	exit();
}
else
echo 'Nice try, this page is protected';
?>