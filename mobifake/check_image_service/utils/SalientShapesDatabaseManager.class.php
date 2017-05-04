<?php
include_once "../entities/ImageNivGris.class.php";
include_once "Constants.class.php";
include_once "SaliencyCut.class.php";
include_once "Databases_Manager.class.php";
include_once "HistogramContrastTransformer.class.php";

class SalientShapesDatabaseManager {	
	public static function refresh_salient_shape_database($img_dir,
			$silent_shape_database_dir) {
		if ($dir = @opendir ( $img_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					ShapePointDatabaseManager::upload_icon_to_salientShapes_database($current_name, $current_ext, $img_dir, $silent_shape_database_dir);						
				}
			}
			closedir ( $dir );
		}	
	}	
	public static function upload_icon_to_salientShapes_database($image_name,$ext,$img_repository,
			$salient_shape_database_dir){
		echo "  \n enregistrement dans la base shapePoint";
		$app_folder_exist = false;
		// extraction du nom du dossier de l'application décrite par l'image
		$i = strpos($image_name, "_");
		$app_name= substr($image_name,0,$i);
		if ($dir = @opendir ( $salient_shape_database_dir )){
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					// attention chaque application a son dossier 
					// le nom de l'application est la première partie du nom de l'image
					//1---- on vérifie si le dossier de l'application caractérisé par l'image existe	
					if ($file == $app_name){
						$app_folder_exist = true;
						$application_directory   = $salient_shape_database_dir.$file;
						// on ouvre le dossier
						if ($actual_application_dir = @opendir ( $application_directory )) {
								
							while ( ($image_file = readdir ( $actual_application_dir )) !== false ) {
								if ($image_file != ".." && $image_file != ".") {
									$current_ext = pathinfo ( $image_file, PATHINFO_EXTENSION );
									$current_name = pathinfo ( $image_file, PATHINFO_FILENAME );
									if($current_name . "." . $current_ext == $image_name.".".$ext.".salientShapePoint"){
										// on vérifie si il est déjà enregistré
										echo "déjà converti en shapePointSalient";
										return -1;
									}							
								}
							}							
						}
						break;
					}				
				}
			}
			// si le fichier n'existe pas on crée le répertoire
			if(!$app_folder_exist){
				mkdir($salient_shape_database_dir.$app_name, 0777);
			}
		}
		
		gc_collect_cycles();
		$actual_img = Database_Manager::read_image( $img_repository . $image_name, $ext );	

		// quantification de l'image
		
		
		$final_salient_image = HistogramContrastTransformer::transform_to_saliency($actual_img,$image_name,$ext);	
		
		$saliencyCut = new SaliencyCut($actual_img,$final_salient_image,$image_name,$ext);
		$binary_salient_cutted_image = $saliencyCut->computeImage();
		
		
		
		
		
		$imgSobel = NivGris_transformer::get_sobel_transform($binary_salient_cutted_image,$image_name,$ext);
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
				"salientShapePoint_list" => $contours
		);
		$string = json_encode ( $data_base_array );
		
		
		
		$fichier_shapePoint = fopen($salient_shape_database_dir.$app_name."/".$image_name.".".$ext.".salientShapePoint", 'w');
		fputs($fichier_shapePoint, $string);
		fclose($fichier_shapePoint);
		echo "fichier ajouté avec succès à la base salient shapePoint\n";
		var_dump(array (
		"image_name" => $image_name . "." . $ext,
		"salientShapePoint_list" => $contours
		));
	}	
}
