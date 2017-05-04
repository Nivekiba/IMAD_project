<?php
include_once "../entities/Icon.class.php";
include_once "../utils/Constants.class.php";
include_once "../utils/Lba_comparer.class.php";
include_once "../utils/Lba_transformer.class.php";
include_once "../utils/Databases_Manager.class.php";


$seuil = Constants::getSeuilLabDistance();

$result_distance = array();
echo "récupération de l'image";
$filename = $_FILES ["file_path"] ["name"];
$ext = pathinfo ( $filename, PATHINFO_EXTENSION );
$image_name = pathinfo ( $filename, PATHINFO_FILENAME );
if (in_array( $ext,Constants::getExtentionAllowed())) {
	
	
	Database_Manager::clearFolder ( Constants::getTestedImagesDir () );
	$move_success = move_uploaded_file ( $_FILES ["file_path"] ["tmp_name"], Constants::getTestedImagesDir () . $image_name . "." . $ext );
	echo " \n début du traitement avec succès: " . $move_success;
	$img = Database_Manager::read_image ( Constants::getTestedImagesDir () . $image_name, $ext );
	$icon = $icon = new Icon ( $img, $image_name . "." . $ext );
	$distinct_rgb = Lba_transformer::get_distinct_rgb_values_occurences ( $icon );
	$final_distinct_rgb_value_occurences = Lba_transformer::filter_distinct_rgb_values_occurences ( $distinct_rgb );
	$final_distinct_lba_value_occurences = Lba_transformer::get_distinct_lba_for_distinct_rgb ( $final_distinct_rgb_value_occurences );
	
	
	
	
	
	if ($dir = @opendir ( Constants::getLabDatabaseDir() )) {
		while ( ($file = readdir ( $dir )) !== false ) {
			if ($file != ".." && $file != ".") {
				$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
				$current_name = pathinfo ( $file, PATHINFO_FILENAME );
				$handler = fopen ( Constants::getLabDatabaseDir().$current_name.".".$current_ext, 'r' );
				$string = fgets ( $handler );
				fclose ( $handler );
				$array_lab = json_decode ( $string, true );
				$dist_min = Lba_comparer::compare_distinct_lba_values_occurence ( $array_lab["image_lab_values"], $final_distinct_lba_value_occurences );
				/*if ($dist_min <= $seuil) {
					echo "\n seuil:".$seuil;
					echo "\n similarité_detectee avec l'image \n" . $array_lab ["image_name"];
					echo "\n distance :".$dist_min;
					//$images_filtre_couleur [] = $array_lab ["image_name"];
				}*/
				$result_distance[] = array("image_name"=>$array_lab ["image_name"],"distance"=>$dist_min);
				
				gc_collect_cycles();
				
				
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






