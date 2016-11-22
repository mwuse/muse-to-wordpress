<?php
	function get_asset_url( $asset )
	{
		global $folderName;

		$file_url = $folderName . '/' . $asset;
		

		if( file_exists(  TTR_MW_TEMPLATES_PATH . $file_url ) )
		{
			return TTR_MW_TEMPLATES_URL . $file_url;
		}
		else
		{
			return $asset;
		}

	}
?>