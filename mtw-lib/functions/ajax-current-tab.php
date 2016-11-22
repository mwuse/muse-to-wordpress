<?php

add_action( 'wp_ajax_mtw_get_current_tab', 'mtw_get_current_tab' );

function mtw_get_current_tab() {
	if( !session_id() ) {
		session_start();
	}
	$return[ 'current_tab' ] = $_SESSION[ 'current_tab' ][ $_POST[ 'data' ][ 'id' ] ];
	$return[ 'post' ] = $_POST;
	$return[ 'message' ] = "Get session";
	die( json_encode( $return ) );
}

add_action( 'wp_ajax_mtw_update_current_tab', 'mtw_update_current_tab' );

function mtw_update_current_tab() {
	if( !session_id() ) {
        session_start();
    }
    $_SESSION[ 'current_tab' ][ $_POST[ 'data' ][ 'id' ] ] = $_POST[ 'data' ][ 'count' ];
    $return[ 'session' ] = $_SESSION;
    $return[ 'message' ] = "Session updated";
	die( json_encode( $return ) );
}

?>