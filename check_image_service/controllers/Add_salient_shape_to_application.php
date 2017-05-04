<?php header('Access-Control-Allow-Origin: *');

include_once "../entities/Icon.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/NivGris_transformer.class.php";

include_once "../utils/NivGris_transformer.class.php";
include_once "../utils/SalientShapesDatabaseManager.class.php";


echo "récupération de limage\n";
$filename = $_FILES["file_path"]["name"];
// Attention il y'a une convention de 
// nommage du nom de l'application           : application_numéro.extension
// tout nom d'application ayant au moins deux mots sera réduit à un seul par la notation java e

$ext = pathinfo($filename, PATHINFO_EXTENSION);
echo $filename;
$image_name  = pathinfo($filename, PATHINFO_FILENAME);
if (in_array($ext,Constants::getExtentionAllowed())){
	$img_repository = Constants::getImageRepository();
	Database_Manager::upload_icon_repository($_FILES["file_path"]["tmp_name"], 
	$image_name, $ext, $img_repository);

	SalientShapesDatabaseManager::upload_icon_to_salientShapes_database($image_name,$ext, 
							Constants::getImageRepository(),
							Constants::getSalientShapesDataBaseDir());
	
	
	
	
	
	
	
	
	
	
}else{
	echo "\nformat non pris en charge...." . $ext;
}

/*SalientShapesDatabaseManager::upload_icon_to_salientShapes_database("author_1","jpeg",
Constants::getImageRepository(),
Constants::getSalientShapesDataBaseDir());*/