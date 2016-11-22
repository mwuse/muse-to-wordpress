<?php
//echo 'data';
/*echo $_POST['file'];
echo $_POST['type'];
echo $_POST['path'];*/

//echo json_encode($_FILES['file']['type']);

$target_dir = "templates/";
$target_file = $target_dir . basename($_FILES["file"]["name"]);

$toExtract=array();
$folders=array();
$return=false;
$index=false;

if($_FILES['file']['type'] != "application/zip") {
	$return = 2;
}
if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
	$zip = new ZipArchive;
	$res = $zip->open($target_file);
	if($res===TRUE){
		//echo'TRUUUUE';
		for($i = 0 ; $i < $zip->numFiles;$i++){
	    	$stat = $zip->statIndex($i);
	        //if index is at the root get the achive name to create the folder
	        if($stat['name']=='index.html'){
	            $index=true;
	            break;
	        } 
	        //echo$stat['name'].'<br>';
	        $pos=strpos($stat['name'],'/');
	        if($pos!==FALSE){
	             $folder = substr($stat['name'],0,$pos);
	             $folders[] = $folder.'/';
	             $toExtract[]=$stat['name'];
	        }
	    }//for
	 /*   echo'<br>';
	    echo'index';
	    echo $index;
	    echo'<br>';
	   	echo json_encode($folders);
	   	echo " <br> <br>";
	   	echo json_encode($toExtract);
		echo " <br> <br>";
*/
	    if($index){
	    	if(file_exists($target_file))
	    		$return = true;
	    	else
	    		$return= false;
	    }
	    else{
	         $count = array_unique($folders);
	        // echo json_encode($folders);
	         //echo'count :';
/*	         echo $count[0];
	         echo count($count);*/
	        // echo pathinfo($count[0],PATHINFO_FILENAME);
	         if(count($count)==1){
	         	if(file_exists($target_dir.pathinfo($count[0],PATHINFO_FILENAME)))
	            	$return=true;
		        else
		        	$return=false;
	    	}
	    	else{
	    	   $return=3;
	    	}
		}
	$zip->close();
    unlink($target_file);

	}//if res === true
}
echo json_encode($return);
?>
