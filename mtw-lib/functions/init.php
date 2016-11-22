<?php


function check_isset_option()
{
	global $mtw_option;
	$mtw_option = get_option( 'mtw_option' );
	global $muse_projects;
	$muse_projects = ttr_get_muse_projects();

	
	global $wp_filesystem;
	WP_Filesystem();

	if( !$mtw_option )
	{
		$default_theme = array_keys( $muse_projects )[0];
		$mtw_option = array(
			'mtw_production_mode' => 'checked',
			'mtw_default_project' => $default_theme,
			);
		update_option( 'mtw_option', $mtw_option );
	}

	foreach ($muse_projects as $key => $value) 
	{
		$path_museconfig = TTR_MW_TEMPLATES_PATH . $key . '/scripts/museconfig.js';
		
		$update_script = false;

		$museconfig = $wp_filesystem->get_contents( $path_museconfig );

		$project_url = TTR_MW_TEMPLATES_URL . $key;

		$pos1 = strpos($museconfig, "paths:");
		$justPaths = substr( $museconfig , $pos1 + 6 );
		$pos2 = strpos( $justPaths, "}");
		$justPaths = substr( $justPaths , 0, $pos2 + 1 );
		$museconfig_paths_array = json_decode( $justPaths, true );


		foreach ( $museconfig_paths_array as $key => $path ) 
		{
			if( strpos($path, "http") === 0 && strpos($path, $project_url) === false )
			{
				$museconfig_paths_array[$key] = preg_replace("#http(.*)/scripts/#", $project_url."/scripts/", $path);
				$update_script = true;
			}
			elseif ( strpos($path, "http") === false )
			{
				$museconfig_paths_array[$key] = $project_url ."/". $path;
				$update_script = true;
			}
			
		}

		if( $update_script == true )
		{
			
			$replace_paths = stripslashes( json_encode( $museconfig_paths_array ) ) ;	

			$museconfig = substr( $museconfig , 0, $pos1 + 6 ) . $replace_paths . substr( $museconfig , $pos1 + 6 + $pos2 + 1 );
			$wp_filesystem->put_contents($path_museconfig, $museconfig);

			//exit();		
		}

	}
}
add_action( 'wp_loaded', 'check_isset_option', 1 );


function mtw_load_script_vars()
{
	global $projectName;
	?>
	<script type="text/javascript">
	var mtw_theme_url = '<?php echo TTR_MW_TEMPLATES_URL . $projectName . '/'; ?>';
	</script>
	<?php
}
add_action( 'wp_head', 'mtw_load_script_vars' );

?>