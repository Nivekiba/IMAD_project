<?php
include_once "../entities/ImageNivGris.class.php";
include_once "Constants.class.php";
include_once "Databases_Manager.class.php";
include_once "NivGris_transformer.class.php";
class ShapePointDatabaseManager {	
	public static function refresh_shapePoint_database($img_dir,
			$shapePoint_database_dir) {
	
		if ($dir = @opendir ( $img_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					ShapePointDatabaseManager::upload_icon_to_shapePoint_database($current_name, $current_ext, $img_dir, $shapePoint_database_dir);						
				}
			}
			closedir ( $dir );
		}	
	}	
	public static function upload_icon_to_shapePoint_database($image_name,$ext,$img_repository,$shapePoint_database_dir){
		echo "  \n enregistrement dans la base shapePoint";
		if ($dir = @opendir ( $shapePoint_database_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					if($current_name . "." . $current_ext==$image_name.".".$ext.".shapePoint"){
						echo "déjà converti en shapePointList";
						return -1;		
					}
				}
			}
		}
		gc_collect_cycles();
		$actual_img = Database_Manager::read_image( $img_repository . $image_name, $ext );			
		$imgSobel = NivGris_transformer::get_sobel_transform($actual_img,$image_name,$ext);
		// pour le test de visualisation étape Sobel
		Database_Manager::produce_image($imgSobel,Constants::getSobelImagesDir(),$image_name,$ext );
		// fin test sobel
		$contours = NivGris_transformer::get_contours($imgSobel);
		$hardCoreContoursImage = NivGris_transformer::getHardCoreContoursImage($contours, imagesy($imgSobel), imagesx($imgSobel));
		// pour le test de visualisation étape contours
		Database_Manager::produce_image($hardCoreContoursImage,Constants::getContoursImagesDir(), $image_name,$ext );
		// fin test sobel
		
		$data_base_array = array (
				"image_name" => $image_name . "." . $ext,
				"shapePoint_list" => $contours
		);
		$string = json_encode ( $data_base_array );
		$fichier_shapePoint = fopen($shapePoint_database_dir.$image_name.".".$ext.".shapePoint", 'w');
		fputs($fichier_shapePoint, $string);
		fclose($fichier_shapePoint);
		echo "fichier ajouté avec succès à la base lab\n";
		var_dump(array (
		"image_name" => $image_name . "." . $ext,
		"shapePoint_list" => $contours
		));
	}	
}