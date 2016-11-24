<?php

function register_ttr_page_linker(){
	wp_enqueue_script(' jquery ' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-ui-dialog ' );
 	wp_enqueue_script( 'jquery-form' );
	 	
	wp_enqueue_style( 'ui-tabs', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css' );

	//add_menu_page( 'Muse', 'Muse', 'manage_options', 'muse-page', 'ttr_page_linker', '' ); 

	//add_submenu_page( "muse-to-wordpress-setting", "Projects", "Projects", 'manage_options', 'mtw-projects', 'ttr_page_linker' );
}

function ttr_page_linker(){
	
	global $html;

	if($_POST){

	}

	echo '<h2>Muse to wordpress</h2>';

	?>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.js"></script> 
	<script src="http://malsup.github.com/jquery.form.js"></script> 

 	<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
 
	<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script> 


	
	<div>
	<!--<form id="uploadForm"action="<?php echo TTR_MW_PLUGIN_URL;  ?>ttr_checkForFile.php" method="post" enctype="multipart/form-data">
    <b>Select a new archive to upload:</b><br>
    <input type="file" name="file" id="file" >
    <input type="submit" value="Upload your zip file" name="submit" class="button button-primary">
	</form>
	<p id="output"></p>
	<p><i>Only zip files allowed!<br/> You can upload multiple muse projects but you need to put each one of them in a folder.<br>
		If you have only one muse project you can either put it in a folder or leave it at the root of the zip file.</i></p>
	</div>-->

	<a href="?page=muse-to-wordpress-setting&update_all_logic_links=1" class="button">Update all links</a><br/><br/>

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
	  		<li data-count="<?php echo $countTab; ?>"><a href="#<?php echo $projectKey; ?>"><?php echo $projectKey; ?></a></li>
	  		<?php $countTab++; ?>
	  		<?php } ?>
	  	</ul>

	<?php
	//project tabs
	foreach ($ttrProjects as $projectKey => $project) {
		?>
		<div id="<?php echo $projectKey; ?>">
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

	<script>
	var response;
	var toUpload;
	var myData = new FormData();
		$(document).ready(function() {
			var options = {
				target : 			null,
				beforeSubmit :  	checkIfFileExist,
				success : 			fileUploaded,
			};
			$('#uploadForm').ajaxForm(options);
		});


		function checkIfFileExist(formData,jqForm,options){

			var querryString = $.param(formData);
			var file = formData[0].value.name;
			var name = file.replace(".zip","");
			console.log('formData');
			console.log(formData);
			console.log(formData[0].value);

			var folderPath= "templates/"+name;
			var postData;


			myData.append('file',formData[0].value);
			console.log('myData');
			console.log(myData);
		

		}//end check for file



		function fileUploaded(responseText, statusText, xhr, $form){
			//$.post("http://192.168.1.30/ttr-template/wp-content/plugins/muse-wordpress/ttr_upload.php", {file: <?php echo $_FILES["fileToUpload"];?>});
/*			console.log("response text");
			console.log(responseText);
			console.log(statusText);
			console.log(xhr);*/
			console.log($form);
			var isCheck;
			responseText =jQuery.parseJSON(responseText);
			if(responseText==true){
				console.log('response=true');
				var r =confirm("Do you want to replace the old project")
			     if (r == true) {
			     	isCheck= true;
			     } 
			     else {
			         isCheck= false;
			     }; 
			}
			if(responseText==false){
				console.log('response false');
				isCheck= true;
			}
			if(responseText==2 || responseText==3){
				console.log('response 2 or 3 ='+responseText);
				isCheck= false;
			}
			if(isCheck){
				var settings = $.ajax({
					url: 'http://192.168.1.30/ttr-template/wp-content/plugins/muse-wordpress/ttr_upload2.php',
					type: 'POST',
					//dataType: 'default: Intelligent Guess (Other values: xml, json, script, or html)',
					data: myData,
					cache: false,
				 	contentType: false,
				 	processData: false,

				});
			}
			$form.find("input[type=file]").val("");
		}
		
	</script>
<div id="dialog-confirm" title="Create a copy ?"></div>
	<?php
}



?>