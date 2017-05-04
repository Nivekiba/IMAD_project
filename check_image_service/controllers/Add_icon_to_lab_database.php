<?php

include_once "../entities/Icon.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/Lba_comparer.class.php";
include_once "../utils/Lba_transformer.class.php";
include_once "../utils/Databases_Manager.class.php";


echo "récupération de l'image";
$filename = $_FILES["file_path"]["name"];
$ext = pathinfo($filename, PATHINFO_EXTENSION);
$image_name  = pathinfo($filename, PATHINFO_FILENAME);
if (in_array($ext,Constants::getExtentionAllowed())){
	$img_repository = Constants::getImageRepository();
	Database_Manager::upload_icon_repository($_FILES["file_path"]["tmp_name"], 
	$image_name, $ext, $img_repository);
	Database_Manager::upload_icon_to_lba_database($image_name,$ext, 
							Constants::getImageRepository(),
							Constants::getLabDatabaseDir());
}else{
   echo "\nformat non pris en charge...." . $ext;
}
