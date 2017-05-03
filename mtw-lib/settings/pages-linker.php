<?php

function register_ttr_page_linker(){
	wp_enqueue_script(' jquery ' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-dialog ' );
 	wp_enqueue_script( 'jquery-form' );
	 	
	wp_enqueue_style( 'ui-tabs', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );
}

function mtw_delete_project()
{
	function ttr_link_delete_path($path)
    {
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file)
            {
                ttr_link_delete_path(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }

        else if (is_file($path) === true)
        {
            return unlink($path);
        }

        return false;
    }
	if( current_user_can( 'administrator' ) && $_POST['project_url'] )
	{
		ttr_link_delete_path( $_POST['project_url'] );
		die( $_POST['project_url'] );
	}
	else
	{
		die( 'mtw_delete_project_error' );
	}
}

add_action( 'wp_ajax_mtw_delete_project' , 'mtw_delete_project' );

function ttr_page_linker(){
	
	global $html;

	echo '<h2>Your Projects</h2>';

	?>

	<script type="text/javascript">

	jQuery(document).ready(function($) {
		$('.delete-project').on('click', function(event) {
			event.preventDefault();
			if ( confirm("<?php echo __('Are you sure to delete this project', 'muse-to-wordpress') ?>") == true ) 
			{
			    jQuery.post(
			        ajaxurl, 
			        {
			            'action': 'mtw_delete_project',
			            'project_url':   $(this).data('dir')
			        }, 
			        function(response){
			            location.reload();
			        }
			    );
			} 
		});
	});
	

	</script>

	<style type="text/css">
	#ttr-muse-to-wordpress-tabs .ui-state-default 
	{
		padding-right: 30px;
	}

	.bt-delete-project
	{
		padding: 7px;
		background: none !important;
	}

	</style>

	
	<div>

	<script>
		

		jQuery(document).ready(function($) {
			
			$.post(
			    ajaxurl, 
			    {
			        'action': 'mtw_get_current_tab',
			        'data':   { id : "ttr-muse-to-wordpress-tabs" }
			    }, 
			    function(response){
			    	try {
				    	var mtw_get_current_tab = jQuery.parseJSON( response );
				        var tab = mtw_get_current_tab.current_tab;

				        $( "#ttr-muse-to-wordpress-tabs" ).tabs({
						  active: tab
						});
					}
					catch (e) {
						$( "#ttr-muse-to-wordpress-tabs" ).tabs();
					}
			    	
			    }
			);

    		$( "#ttr-muse-to-wordpress-tabs" ).on("tabsactivate", function( event, ui ) {
				  	$.post(
					    ajaxurl, 
					    {
					        'action': 'mtw_update_current_tab',
					        'data':   { id : $(this).attr('id'), count: ui.newTab.data('count') }
					    }, 
					    function(response){
					    	/*console.log("reponse mtw_update_current_tab")
					        console.log( jQuery.parseJSON( response ) );*/
					    }
					);
			})
  		});
	</script>
	<?php 
		$ttrProjects = ttr_get_muse_projects();
	?>
	<?php
		$countTab = 0;
	?>
	<div id="ttr-muse-to-wordpress-tabs">
	  	<ul>
	  		<?php foreach ($ttrProjects as $projectKey => $project) { ?>
	  		<li data-count="<?php echo $countTab; ?>"><a href="#<?php echo $projectKey; ?>"><?php echo $projectKey; ?><span class="screen-reader-text">Dismiss this notice.</span></a></li>
	  		<?php $countTab++; ?>
	  		<?php } ?>
	  	</ul>



	<?php
	//project tabs
	foreach ($ttrProjects as $projectKey => $project) {
		?>
		<div id="<?php echo $projectKey; ?>">
			<a href="#" class="delete-project" data-dir="<?php echo TTR_MW_TEMPLATES_PATH . $projectKey ?>" >Delete this project</a><br/><br/>
			<?php

			$links = array();
			

			$projectFolder = $projectKey;
			$MuseProject = new MuseProject;
			$MuseProject->init($projectFolder);
		
			echo $MuseProject->table_project();
			
			?>
		</div><!-- end tab -->
	<?php
	}//end foreach project list
	?>
	</div><!-- end tabs -->

	<?php
}



?>