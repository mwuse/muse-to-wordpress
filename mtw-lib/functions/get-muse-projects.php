<?php
function ttr_dirToArray($dir) { 
   
   $result = array();

   if( is_dir($dir) )
   {

      $cdir = scandir($dir);

      foreach ($cdir as $key => $value)
      { 
         if (!in_array($value,array(".","..","readme.txt")))
         {   
            if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
            {
               $result[$value] = ttr_dirToArray($dir . DIRECTORY_SEPARATOR . $value);
            }
            else
            {
               $result[] = $value;
            }
         }
      } 
      return $result;
   }
   else
   {
      return false;
   }
}

function ttr_get_muse_projects(){	
   $mtw_theme_dir_no_end_slash = substr(TTR_MW_TEMPLATES_PATH, 0, -1) ;
   $museProjects = ttr_dirToArray($mtw_theme_dir_no_end_slash);

   $mtw_option =  get_option('mtw_option');
   
   if( is_array( $mtw_option ) && isset( $mtw_option['mtw_default_project'] ) )
   {
      $default_key = $mtw_option['mtw_default_project'];

      $default_project[$default_key] = $museProjects[$default_key];
      unset($museProjects[$default_key]);

      $museProjects = array_merge( $default_project, $museProjects );

   }

	return $museProjects;
}



function ttr_get_muse_html_array($project = null){

	$result = array();
	$museProjects = ttr_get_muse_projects();

   if( $museProjects )
   {
   	foreach ($museProjects as $key1 => $museProject) 
      {
         if( ($project == null || $project == $key1) && is_array($museProject) )
         {
      		foreach ($museProject as $key2 => $value) 
            {

      			if( @strpos($value, '.html') )
               {
      				$result[$key1.'/'.$value] = $key1.' - '.$value;
      			}
      		}
         }
   	}
   }
   else
   {
      add_action( 'admin_notices', 'no_mtw_template' );
   }

   if( isset( $museProjects['XD'] ) && empty( get_option( 'mtw-xd-template-instruction-notice' ) ) )
   {
      add_action( 'admin_notices', 'mtw_xd_template_instruction' );
   }

	return $result;

}



?>