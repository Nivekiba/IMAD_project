<?php
include_once "../utils/ShapePointDatabaseManager.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/ShapePoint_Comparer.class.php";
include_once "../utils/ShapePoint_ComparerNeutralPoint.class.php";
include_once "../utils/NivGris_transformer.class.php";
include_once "../utils/Databases_Manager.class.php";


$seuil = $_POST["seuil"];


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
	$shapePointList = NivGris_transformer::get_contours_from_GD_color_IMG($GD_img);
	
	
	
	echo "\n seuil:".$seuil."\n"; 
	if ($dir = @opendir ( Constants::getShapePointDataBaseDir() )) {                  $test_iteration= 0;
		while ( ($file = readdir ( $dir )) !== false ) {
			if ($file != ".." && $file != ".") {
				echo "\n *********début du test";
				$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
				$current_name = pathinfo ( $file, PATHINFO_FILENAME );
				$handler = fopen ( Constants::getShapePointDataBaseDir().$current_name.".".$current_ext, 'r' );
				$string = fgets ( $handler );
				fclose ( $handler );
				$array_shapePoint = json_decode ( $string, true );
				$current_shapePointArrayList = $array_shapePoint["shapePoint_list"];
				
				$current_shapePointList = array();
				foreach ($current_shapePointArrayList as $shapePointArray){
					$shapePointObject = new ShapePoint($shapePointArray["x"], $shapePointArray["y"]);
					$current_shapePointList[]=$shapePointObject;
				}
				
				$distance_actuelle = ShapePoint_ComparerNeutralPoint::compare($shapePointList, $current_shapePointList);
				
				
				$result_distance[] = array("image_name"=>$array_shapePoint["image_name"],"distance"=>$distance_actuelle);
				
				gc_collect_cycles();
				
				//if ($distance_actuelle <= $seuil) {
			   /* echo "\n similarité_detectee avec l'image  : " . $array_shapePoint["image_name"]."\n";	
				echo "\n distance trouvée :   ".$distance_actuelle;
				echo "\n *********fin du test";*/
				//}
				//break;
				//$test_iteration ++; if($test_iteration > 4) {echo "sortie" ;break;}
			}
			
		}
	}
	
	$distances  = array();
	
	foreach($result_distance as $k => $v) {
		$distances[$k] = $v["distance"];
	}
	
	array_multisort($distances, SORT_ASC,$result_distance);
	
	
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






