<?php

/* No Adobe Muse template */
function no_mtw_template() 
{
    ?>
    <div class="error notice">
        <p><?php _e( 'You don\'t have any Adobe Muse site in your <b>mtw-theme</b> folder. <br/>Find this folder on the root of your Wordpress website and export your Adobe Muse HTML in a new folder by project.', 'muse-to-wordpress' ); ?></p>
        <p><a href="?mtw-unzip-template=XD.zip"><?php _e( 'Load our free example.', 'muse-to-wordpress' ); ?></a></p>
    </div>
    <?php
}

function mtw_load_xd_template()
{
	global $wp_filesystem;
	WP_Filesystem();

	if( isset( $_GET['mtw-unzip-template'] ) && wp_mkdir_p( TTR_MW_PLUGIN_DIR . 'zip-template' ) )
	{
		if( $_GET['mtw-unzip-template'] == "XD.zip" )
		{
			$copy_link = "http://muse-to-wordpress.net/template_html/XD.zip";
			$content = $wp_filesystem->get_contents( $copy_link );
			$wp_filesystem->put_contents( TTR_MW_PLUGIN_DIR . 'zip-template/XD.zip', $content);
		}
		
		$file = TTR_MW_PLUGIN_DIR . 'zip-template/'.$_GET['mtw-unzip-template'];
		if( file_exists( $file ) )
		{
			$zip = new ZipArchive;
			if ($zip->open($file) === TRUE) {
			    $zip->extractTo( TTR_MW_TEMPLATES_PATH );
			    $zip->close();


			    $wp_filesystem->get_contents( admin_url() );
			    sleep(1);

			    wp_redirect( admin_url( "admin.php?page=muse-to-wordpress-setting" ) );
			}
		}
	}

}

add_action( 'admin_init' , 'mtw_load_xd_template' );


function mtw_xd_template_instruction()
{
	?>
	<div class="updated notice is-dismissible mtw-notice" id="mtw-xd-template-instruction-notice">
	    <p><?php _e( 'You can use <b>Synchronize Muse and Wordpress Pages</b> for create Wordpress pages automatically', 'muse-to-wordpress' ); ?></p>
	    <p><a href="http://musetowordpress.com/shop/xd/"><?php _e( 'Download XD Adobe Muse files to modify this or create our first template.', 'muse-to-wordpress' ); ?></a></p>
	</div>
	<?php
}


function mtw_notice_dismiss()
{
	?>
	<script type="text/javascript">
	jQuery(document).on( 'click', '.mtw-notice .notice-dismiss', function() {

		noticeID = jQuery(this).parents('.mtw-notice').attr('id');

	    jQuery.post(
		    ajaxurl, 
		    {
		        'action': 'mtw_notice_dismiss_by_id',
		        'data':   noticeID
		    }, 
		    function(response){
		        
		    }
		);

	});
	</script>
	<?php
}
add_action( "admin_footer", "mtw_notice_dismiss" );




function mtw_notice_dismiss_by_id()
{
	$id = $_POST['data'];
	update_option( $id, 1 );
	die( $id );
}

add_action( 'wp_ajax_mtw_notice_dismiss_by_id', 'mtw_notice_dismiss_by_id' );
add_action( 'wp_ajax_nopriv_mtw_notice_dismiss_by_id', 'mtw_notice_dismiss_by_id' );



function muse_to_wordpress_xd_register_required_plugins()
{
   if( is_admin() )
   {

    $mtw_option = get_option( 'mtw_option' );
    $plugins = array();


	global $wp_filesystem;
	WP_Filesystem();

	$mtw_require = $wp_filesystem->get_contents("http://muse-to-wordpress.net/plugins/require.php");
	if( $mtw_require )
	{
		$mtw_require_array = json_decode( $mtw_require, true );
		$plugins = array_merge( $plugins , $mtw_require_array );
	}

	$MTW_plugins_requier = get_option( "MTW_plugins_requier", array() );

	foreach ($MTW_plugins_requier as $key => $MTW_plugin_requier) 
	{
		if( !empty($MTW_plugin_requier) )
		{
			$plugins = array_merge( $plugins, $MTW_plugin_requier);
		}
	}


      $config = array(
         'id'           => 'muse-to-wordpress',     // Unique ID for hashing notices for multiple instances of TGMPA.
         'default_path' => '',                      // Default absolute path to bundled plugins.
         'menu'         => 'mtw-install-plugins', // Menu slug.
         'has_notices'  => true,                    // Show admin notices or not.
         'dismissable'  => false,                    // If false, a user cannot dismiss the nag message.
         'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
         'is_automatic' => true,                   // Automatically activate plugins after installation or not.
         'message'      => ''
      );

      tgmpa( $plugins, $config );
   }
}

add_action( 'tgmpa_register', 'muse_to_wordpress_xd_register_required_plugins' );

function mtw_check_plugins_required( $mtw_page )
{	
	
	$finder = new DomXPath($mtw_page->DOMDocument);
	$name = "plugin_requier";
	$results = $finder->query("//meta[@name='plugin_require']");

	$plugin_requier_options[$mtw_page->file_url] = array();

	foreach ($results as $key => $el) 
	{
		$el_args = $el->getAttribute("args");
		$plugin_requier_options[$mtw_page->file_url][] = json_decode( "[".$el_args."]", true )[0];
	}

	$MTW_plugins_requier = get_option( "MTW_plugins_requier", array() );
	$MTW_plugins_requier = array_merge( $MTW_plugins_requier, $plugin_requier_options );
	update_option( "MTW_plugins_requier", $MTW_plugins_requier );

}
add_action( 'DOMDocument_change', 'mtw_check_plugins_required' );

?>