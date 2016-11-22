<?php

add_action( 'wp_ajax_ttr_create_pages','ttr_create_pages' );

function ttr_create_pages(){
    $links= array();
    $countExisting=0;
    $countAll=0;

    foreach (ttr_get_muse_html_array($_POST["pages"]) as $pageKey => $page) {

        $countAll++;
                
        $fileUrl = TTR_MW_TEMPLATES_URL.$pageKey;
        libxml_use_internal_errors(true);
        $html->loadHTMLFile($fileUrl);
        $title=$html->getElementsByTagName('title')->item(0)->nodeValue;

        if(get_page_by_title($title)===NULL){

            $id=wp_insert_post(array(
                'post_title'=>$title,
                //'post_name'=>"",
                'post_content'=>"",
                'post_type'=>'page',
                'post_status'=>"publish",
                'page_template'=>$pageKey
                ));
            $exploded=explode('/', $pageKey);
            $links [ utf8_uri_encode( $exploded [ 1 ] ) ] = $id;
            if($exploded[1]==="index.html"){
                update_option( 'page_on_front', $id );
                update_option( 'show_on_front', 'page' );
            }
        }//end if(get_page_by_title($title)!==NULL)
        else
            $countExisting++;
    }//end foreach
    // echo'<pre>';
    // print_r($links);
    // exit();
    $url=$_SERVER['HTTP_REFERER'];

    if($countExisting===$countAll){
        echo "<script type='text/javascript'>";
        echo "alert('all pages were allready created');";
        //redirect to the previous page
        echo "window.location.href = '$url';";
        echo "</script>";
    }

    else{
        $message=$countAll-$countExisting." page(s) created, ".$countExisting." were already created.";
        echo "<script type='text/javascript'>";
        echo "alert('$message');";
        echo "window.location.href = '$url';";
        echo "</script>";
        update_option( "muse_linker_replace_".strtolower($_POST["pages"]), json_encode($links), "no" );
    }

    exit();
}

add_action( 'wp_ajax_ttr_delete_pages','ttr_delete_project' );

function ttr_delete_project(){
    rmdir_recursive("templates/".$_POST['project']);
    delete_option("muse_linker_replace_".strtolower($_POST['project']));
}

function rmdir_recursive($dir) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    rmdir($dir);
}
?>