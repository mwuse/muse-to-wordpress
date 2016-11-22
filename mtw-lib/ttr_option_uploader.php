<?php

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

if(is_user_logged_in() && current_user_can("administrator")){
// echo'<pre>';
// print_r($_POST);
// echo'</pre>';
// exit();
	$project = $_POST["project"];
	unset($_POST["project"]);

	$result=update_option( "muse_linker_replace_".strtolower($project), json_encode($_POST['links']), "no" );

	header("Location: ".$_SERVER['HTTP_REFERER']);
	exit();
}
else
echo 'Nice try, this page is protected';
?>