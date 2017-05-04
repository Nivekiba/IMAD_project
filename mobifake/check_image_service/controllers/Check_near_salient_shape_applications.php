<?php
include_once "../utils/ShapePointDatabaseManager.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/ShapePoint_Comparer.class.php";
include_once "../utils/ShapePoint_ComparerNeutralPoint.class.php";
include_once "../utils/NivGris_transformer.class.php";
include_once "../utils/Databases_Manager.class.php";
include_once "../utils/SalientShapesDatabaseManager.class.php";
include_once "../utils/SaliencyCut.class.php";
include_once "../utils/HistogramContrastTransformer.class.php";

$seuil = $_POST["seuil"];

$result_distance_image=array();
$result_distance = array();
echo "récupération de l'image";
$filename = $_FILES ["file_path"] ["name"];
$ext = pathinfo ( $filename, PATHINFO_EXTENSION );
$image_name = pathinfo ( $filename, PATHINFO_FILENAME );
if (in_array( $ext,Constants::getExtentionAllowed())) {
	
	
	Database_Manager::clearFolder ( Constants::getTestedImagesDir () );
	$move_success = move_uploaded_file ( $_FILES ["file_path"] ["tmp_name"], Constants::getTestedImagesDir () . $image_name . "." . $ext );
	echo " \n début du traitement avec succès: " . $move_success;
	$GD_img = Database_Manager::read_image ( Constants::getTestedImagesDir () . $image_name, $ext );
	
	
	
	$final_salient_image = HistogramContrastTransformer::transform_to_saliency($GD_img,$image_name,$ext);
	
	$saliency = new SaliencyCut($GD_img, $final_salient_image, $image_name, $ext);
	$binary_salient_cutted_image = $saliency->computeImage();
	
	
	
	$shapePointList = NivGris_transformer::get_contours_from_GD_color_IMG($binary_salient_cutted_image);
	
	
	
	
	
	
	
	echo "\n seuil:".$seuil."\n"; 
	if ($dir = @opendir (Constants::getSalientShapesDataBaseDir())) {               $test_iteration= 0;
		while ( ($file = readdir ( $dir )) !== false ) {
			if ($file != ".." && $file != ".") {
				// ouverture du dossier de chaque application
				
				
				
			
				//  attention chaque application a son dossier
				// le nom de l'application est la première partie du nom de l'image
				$app_distance = Constants::getMaximumShapeDistance();
				// on ouvre le prochain dossier
				if ($actual_application_dir = @opendir ( Constants::getSalientShapesDataBaseDir().$file )) {
					while ( ($image_file = readdir ( $actual_application_dir )) !== false ) {
						if ($image_file != ".." && $image_file != ".") {
							echo "\n ***********************************************début du test";
							
							
							
							
							$current_ext = pathinfo ( $image_file, PATHINFO_EXTENSION );
							$current_name = pathinfo ( $image_file, PATHINFO_FILENAME );
							echo "\n ***********************************************avec l'image".$current_name;
							
							$i = strpos($current_name, "_");
							$folder_name = substr($current_name,0,$i);
							
							$handler = fopen ( Constants::getSalientShapesDataBaseDir().$folder_name."/".$current_name.".".$current_ext, 'r' );
							
							
							$string = fgets ( $handler );
							fclose ( $handler );
							$array_shapePoint = json_decode ( $string, true );
							$current_shapePointArrayList = $array_shapePoint["salientShapePoint_list"];
							
							$current_shapePointList = array();
							foreach ($current_shapePointArrayList as $shapePointArray){
								$shapePointObject = new ShapePoint($shapePointArray["x"], $shapePointArray["y"]);
								$current_shapePointList[]=$shapePointObject;
							}
							
							$distance_actuelle = ShapePoint_ComparerNeutralPoint::compare($shapePointList, $current_shapePointList);				
							
							$result_distance[] =  array("image_name"=>$current_name,"distance"=>$distance_actuelle);
							//if($distance_actuelle<$app_distance) {$app_distance = $distance_actuelle;}
							gc_collect_cycles();
							
						}
					}
				}
				
				//$result_distance[] = array("application_name"=>$file,"distance"=>$app_distance);

			}
			
		}
	}
	
	$distances_applications  = array();
	
	foreach($result_distance as $k => $v) {
		$distances_applications[$k] = $v["distance"];
	}
	
	array_multisort($distances_applications, SORT_ASC,$result_distance);
	
	
	echo "\n résultat final : \n";
	var_dump($result_distance);
	
	
	
	
	/*
	
	$handler = fopen ( Constants::getLabDatabase (), 'r' );
	$string = fgets ( $handler );
	fclose ( $handler );
	$array_lab = json_decode ( $string, true );
	$images_filtre_couleur = array ();
	
	foreach ( $array_lab as $array ) {
		$dist_min = Lba_comparer::compare_distinct_lba_values_occurence ( $array ["image_lab_values"], $final_distinct_lba_value_occurences );
		if ($dist_min <= $seuil) {
			echo "\n similarité_detectee avec l'image \n" . $array ["image_name"];
			$images_filtre_couleur [] = $array ["image_name"];
		}
	}
	
	
	
	*/
	
	
	
} else {
	echo "\nformat non pris en charge...." . $ext;
}






