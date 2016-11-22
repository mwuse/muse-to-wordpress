<?php


function register_option_pll()
{
	//add_submenu_page( 'muse-page', 'translate', 'translate', 'manage_options', 'muse-translate', 'get_oprion_pll' );
}


function get_oprion_pll()
{

	function get_post_meta_all($post_id){
	    global $wpdb;
	    $data   =   array();
	    $wpdb->query("
	        SELECT `meta_key`, `meta_value`
	        FROM $wpdb->postmeta
	        WHERE `post_id` = $post_id
	    ");
	    foreach($wpdb->last_result as $k => $v){
	        $data[$v->meta_key] =   $v->meta_value;
	    };
	    return $data;
	}

	$project_name_FR = 'formation-fr-pro';
	$project_name_NL = 'formation-nl-pro';

	$frProject = ttr_get_muse_html_array( $project_name_FR );
	$nlProject = ttr_get_muse_html_array( $project_name_NL );

	?>
	<table border="1">
		<tr>
			<th>Template FR</th>
			<th>ID FR</th>
			<th>Action</th>
			<th>Template NL</th>
		</tr>
	<?php
	foreach ($frProject as $key => $value) {
		$id = 0;
		$translate = 0;
		?>
		<tr>
			<td>
				<?php 
				
				echo($key); 
				
				?>
			</td>
			<td>
				<?php

				$getPage =  ttr_get_page_by_template($key);
				$countArray = count( $getPage );
				if( $countArray > 0 && $countArray < 2  ){
					$id = $getPage[0]['ID'];
					echo $id;
				}

				?>
			</td>
			<td>
				<?php
				
				if( $id > 0 && !pll_get_post( $id, 'nl' ) )
				{
					echo 'translate';
					$translate = 1;
				}elseif (pll_get_post( $id, 'nl' )) {
					echo 'none (exist)';

					wp_update_post( array(
						'ID' => pll_get_post( $id, 'nl' ),
						'post_status' => 'publish'
						) );
				}else{
					echo 'none';
				}
				
				?>
			</td>
			<td>
				<?php

				if( $translate == 1 )
				{

					$templateNl = str_replace($project_name_FR, $project_name_NL, $key);

					if( $nlProject[$templateNl] ){
						echo $templateNl;
					}else{
						$translate = 0;
					}

				}
				?>
			</td>
			<td>
				<?php
					if( $translate == 1 )
					{
						$metas = get_post_meta_all($id);
						unset( $metas['_wp_page_template'], $metas['_edit_lock'], $metas['_edit_last'] );

						

						$postarr = array(
							'post_title' => get_the_title( $id ) . ' - NL' ,
							'post_type'  => 'page',
							'page_template' => $templateNl
							);
						
						

						$newPostID = wp_insert_post( $postarr, $wp_error );

						
						print_r( $newPostID );

						foreach ($metas as $key => $value) {

							update_post_meta( $newPostID, $key, $value );

						}

						pll_set_post_language( $newPostID , 'nl');
						pll_save_post_translations( array( 'fr' => $id, 'nl' => $newPostID ) );
					}
				?>
			</td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}

?>