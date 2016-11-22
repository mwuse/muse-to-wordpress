<?php

class MuseProject 
{

	var $folder_name;
	var $muse_pages = array();


	public function init( $folderName )
	{

		$this->folder_name = $folderName;
		$this->muse_pages = $this->get_muse_pages( $folderName );

	}


	private function get_muse_pages( $folderName )
	{

		$return = array();

		foreach ( ttr_get_muse_html_array( $folderName ) as $key => $value ) 
		{
			$MusePage = new MusePage;
			$fileUrl = $key;
			$MusePage->init( TTR_MW_TEMPLATES_URL . $fileUrl );
			$return[] =  $MusePage;
		}

		return $return;
	}

	public function table_project()
	{
		ob_start();
		?>
		<table class="mtw_table_project">
			<!--<tr>
				<th colspan="2">Adobe Muse</th>
				<th colspan="2">Wordpress</th>
			</tr>-->
			<tr>
				<th>File</th>
				<th>Title</th>
				<!--<th>Warnings</th>-->
				<!--<th>Type</th>-->
				<!--<th>Actions</th>-->
			</tr>
			<?php foreach ($this->muse_pages as $page) { ?>
				<tr>
					<td><?php echo $page->base_name; ?></td>
					<td><?php echo $page->muse_title; ?></td>
					<!--<td>
						<?php add_thickbox(); ?>
						<div id="<?php echo sanitize_title( $page->muse_title ).'2';  ?>" style="display:none;">
						    <?php echo $page->get_warnings_list(); ?>
						</div>
						<a href="#TB_inline?width=800&height=600&inlineId=<?php echo sanitize_title( $page->muse_title ).'2';  ?>" class="thickbox inline button"><?php echo count($page->warnings); ?></a>
					</td>-->
					<!--<td><?php echo $page->types_HTML; ?></td>-->
					<!--<td>
						<a class="button" href="?page=muse-page&update_logic_links=<?php echo $page->template_slug; ?>">Update innner links</a>
						<?php if( !in_array("exclude", $page->types)  ){ ?>
						<form class="inline create-page" action="<?php echo TTR_MW_PLUGIN_URL;  ?>ttr_page_creator.php" method="post" enctype="multipart/form-data">
							<input value="<?php echo $page->template_slug; ?>" name="template" type="hidden" >
							<input value="<?php echo $page->muse_title; ?>" name="page" type="hidden" >
			    			<input type="submit" value="Create new page" name="submit" class="button">
						</form>
						<?php } ?>
					</td>-->
				</tr>	
			<?php } ?>
		</table>
		<?php
		return ob_get_clean();
	}

} 
?>