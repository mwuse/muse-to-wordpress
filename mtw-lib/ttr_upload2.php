<?php

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

function ttr_explore($zip){
    $toExtract=array();
    $folders=array();
    $multi=false;
    $index=false;

     for($i = 0 ; $i < $zip->numFiles;$i++){
        $stat = $zip->statIndex($i);
        //if index is at the root get the achive name to create the folder
        if($stat['name']=='index.html'){
            $index=true;
            break;
        } 
        echo$stat['name'].'<br>';
        $pos=strpos($stat['name'],'/');
        if($pos!==FALSE){
             $folder = substr($stat['name'],0,$pos);
             $folders[] = $folder.'/';
             $toExtract[]=$stat['name'];
        }
    }//for
    echo'<br>';
    echo'index';
    echo $index;
    echo'<br>';
   echo json_encode($folders);
   echo " <br> <br>";
   echo json_encode($toExtract);
echo " <br> <br>";

    if($index){
        return 'index';
    }
    else{
         $count = array_unique($folders);
         echo json_encode($folders);
         echo'count :';
         echo $count[0];
         echo count($count);
         if(count($count)==1){
            return $count;
         }
         else{
            return false;
         }
    }
       
}//function

function ttr_unZip($fileToExtract,$extractDir){
    echo 'unzip ';
    $zip = new ZipArchive;
    $isMuse=false;
    $res = $zip->open($fileToExtract);
     if($res===TRUE){
        echo'true ';
        $toExtract = ttr_explore($zip);
        if(isset($toExtract) && !empty($toExtract)){
            echo "extract set and not empty ";
            echo json_encode($toExtract);
            //if index.html is at the root
            if($toExtract==false){
                echo 'not a muse project';
            }

            if($toExtract=="index"){
                if(file_exists($extractDir.'/'.pathinfo($fileToExtract,PATHINFO_FILENAME))){
  
                    rmdir_recursive($extractDir.'/'.pathinfo($fileToExtract,PATHINFO_FILENAME));
                    //delete_option("muse_linker_replace_".strtolower(pathinfo($fileToExtract,PATHINFO_FILENAME)));
                }
                //mkdir($extractDir.'/'.pathinfo($fileToExtract,PATHINFO_FILENAME));     
                $zip->extractTo($extractDir.'/'.pathinfo($fileToExtract,PATHINFO_FILENAME));
                $zip->close();
                echo'exctraction done ! ';
            }
           else{
                echo'<br> to exctract <br>';
                echo json_encode($toExtract);
                if(file_exists($extractDir.'/'.pathinfo($toExtract[0],PATHINFO_FILENAME))){
                    rmdir_recursive($extractDir.'/'.pathinfo($toExtract[0],PATHINFO_FILENAME));
                   // delete_option("muse_linker_replace_".strtolower(pathinfo($toExtract[0],PATHINFO_FILENAME)));
                }
                echo '<br><br><br> path infod <br>';
                echo $extractDir.'/'.pathinfo($toExtract[0],PATHINFO_FILENAME);
    
                $zip->extractTo($extractDir);
                //$zip->extractTo($extractDir,$toExtract);
                $zip->close();
            }
        }//if isset && !empty
        else{
            echo 'No index.html found !';
        }
    }//if ===TRUE
}//function

function ttr_unRar($fileToExtract,$extractDir){
    $rarFile = rar_open($fileToExtract);
    $list= rar_list($rarFile);
    foreach ($list as $file) {
        $entry = rar_entry_get($rarFile,$file->getName());
        $entry->extract($extractDir);
    }
    rar_close($rarFile);
}

function ttr_extract($fileToExtract,$extractDir,$fileType){
    if($fileType=='zip'){
        ttr_unZip($fileToExtract,$extractDir);
    }
    if($fileType=='rar'){
        ttr_unRar($fileToExtract,$extractDir);
    }
}

function rmdir_recursive($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}





echo json_encode($_FILES)." ";
//echo json_encode($_POST['copy'])." ";
$target_dir = "templates/";

$target_file = $target_dir . basename($_FILES["file"]["name"]);

echo pathinfo($target_file,PATHINFO_FILENAME)." ";
$uploadOk = 1;
$fileType = pathinfo($target_file,PATHINFO_EXTENSION);

if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        //extract
        ttr_extract($target_file,$target_dir,$fileType);
        //delte the zip
        unlink($target_file);
        echo "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
} else {
    echo "Sorry, there was an error uploading your file.";
}


?> 