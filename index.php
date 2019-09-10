<?php
# test Api_sia_cloud_tech
# @bennabdellah


include_once("sia.cloud.tech.class.php");
echo "-> start "."\n"; 
$sia_cloud_tech = new Api_sia_cloud_tech('api-key-here','api-secret-here');

$sia_cloud_tech->progress_callback = function ($progress) {
    echo $progress.' %'."\n";
};
echo "\n"."-> uploading ..."."\n";
$result = $sia_cloud_tech->uploadstream('image_upload.jpg');
if ($result) {
    echo "\n"."-> downloading ..."."\n";
    $sia_cloud_tech->progress_callback = function ($progress) {
        $base = log($progress, 1024);
        $suffixes = array('Byte', 'Kb', 'Mb', 'Gb', 'Tb');   
    
        echo round(pow(1024, $base - floor($base)), 2) .' '. $suffixes[floor($base)]."\n";
    };

    $result = $sia_cloud_tech->downloadstream('image_download.jpg','/image.jpg');
    if ($result) {
        $sia_cloud_tech->progress_callback = null;
        echo "\n"."-> list ..."."\n";
        
        $result = $sia_cloud_tech->cloud_list();
        if ($result) {
            echo $result."\n";
        }  
        echo "\n"."-> info ..."."\n";
        $result = $sia_cloud_tech->cloud_file('image_01.jpg');
        if ($result) {
            echo $result."\n";
        }  
    } else {
        echo json_encode($sia_cloud_tech->last_error)."\n";
    }    
    echo "\n"."-> deleting ..."."\n";
    $sia_cloud_tech->cloud_delete('image_01.jpg');

} else {
    echo json_encode($sia_cloud_tech->last_error)."\n";
}
echo "\n"."-> complete"."\n";  
