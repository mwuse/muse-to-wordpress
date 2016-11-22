<?php
function TTR_MW_load_admin_head() {
	wp_register_style( 'muse-wordpress-settings', TTR_MW_PLUGIN_URL.'style.css' );
	wp_enqueue_style( 'muse-wordpress-settings' );
}
?>