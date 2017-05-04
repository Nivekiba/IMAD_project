<?php
include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
include_once "Lba_comparer.class.php";
include_once "Lba_transformer.class.php";
class Database_Manager {
	/*public static function refresh_lab_database($img_dir, $lab_database, $percentage = Constants::PERCENTAGE_RGB_VALUES, $nmax = Constants::NMAX_RGB_VALUES) {
		$data_base_array = array ();
		$handler = fopen ( $lab_database, 'r' );
		$string = fgets ( $handler );
		fclose ( $handler );
		$initial_array_lab = json_decode ( $string, true );
		if ($dir = @opendir ( $img_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					if (! in_array ( $current_name . "." . $current_ext, array_column ( $initial_array_lab, "image_name" ) )) {
						$actual_img = Database_Manager::read_image ( $img_dir . $current_name, $current_ext );
						$icon = new Icon ( $actual_img, $current_name . "." . $current_ext );
						$actual_distinct_rgb_value = Lba_transformer::get_distinct_rgb_values_occurences ( $icon );
						$actual_final_distinct_rgb_value_occurences = Lba_transformer::filter_distinct_rgb_values_occurences ( $actual_distinct_rgb_value, $percentage, $nmax );
						$actual_final_distinct_lba_value_occurences = Lba_transformer::get_distinct_lba_for_distinct_rgb ( $actual_final_distinct_rgb_value_occurences );
						$data_base_array [] = array (
								"image_name" => $current_name . "." . $current_ext,
								"image_lab_values" => $actual_final_distinct_lba_value_occurences 
						);
					}
				}
			}
			closedir ( $dir );
		}
		$final_data_base_array = array_merge ( $data_base_array, $initial_array_lab );
		$string = json_encode ( $final_data_base_array );
		file_put_contents ( $lab_database, $string );
		echo "\n final data base array:\n";
		var_dump ( $final_data_base_array );
	}*/
	
	public static function refresh_lab_database($img_dir, $lab_database_dir, $percentage = Constants::PERCENTAGE_RGB_VALUES, $nmax = Constants::NMAX_RGB_VALUES) {
		
		if ($dir = @opendir ( $img_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					Database_Manager::upload_icon_to_lba_database($current_name, $current_ext, $img_dir, $lab_database_dir);
				}
			}
			closedir ( $dir );
		}
		
	}
	
	
	public static function produce_image($imgGD, $dir,$name ,$ext) {
		switch ($ext) {
			case 'png' :
				
				imagepng($imgGD,$dir . $name.".".$ext);
				break;
			case 'jpg' :
				imagejpeg($imgGD,$dir . $name.".".$ext);
				break;
			case 'jpeg' :
				imagejpeg($imgGD,$dir . $name.".".$ext);
				break;
			case 'gif' :
				imagegif($imgGD,$dir . $name.".".$ext);
				break;
		}
		return 0;
	}
	
	public static function read_image($image_name, $ext) {
		switch ($ext) {
			case 'png' :
				$img = imagecreatefrompng ( $image_name . "." . $ext );
				/*$background = imagecolorallocate($img, 0, 0, 0);
		        imagecolortransparent($img, $background);   
		        imagealphablending($img, true);
		        imagesavealpha($img, true);*/
		        //imagepng($img,Constants::getCacheDir()."test_png.".$ext);
				break;
			case 'jpg' :
				$img = imagecreatefromjpeg ( $image_name . "." . $ext );
			case 'jpeg' :
				$img = imagecreatefromjpeg ( $image_name . "." . $ext );
				break;
			case 'gif' :
				$img = imagecreatefromgif ( $image_name . "." . $ext );
				$background = imagecolorallocate($img, 0, 0, 0);
				// removing the black from the placeholder
				imagecolortransparent($img, $background);
				break;
		}
		return $img;
	}
	/*
	 * public static function upload_icon_to_lba_database($image_name,$ext,$img_repository,$lab_database){ echo " \n enregistrement dans la base lba"; $data_base_array = array (); $handler = fopen($lab_database, 'r'); $string = fgets($handler); fclose($handler); $initial_array_lab = json_decode($string,true); if(!in_array($image_name . "." . $ext, array_column($initial_array_lab,"image_name"))){ $img = Database_Manager::read_image($img_repository.$image_name,$ext); $icon = new Icon($img, $image_name.".".$ext); $actual_distinct_rgb_value = Lba_transformer::get_distinct_rgb_values_occurences($icon); $actual_final_distinct_rgb_value_occurences = Lba_transformer::filter_distinct_rgb_values_occurences($actual_distinct_rgb_value); $actual_final_distinct_lba_value_occurences = Lba_transformer::get_distinct_lba_for_distinct_rgb ( $actual_final_distinct_rgb_value_occurences ); $initial_array_lab [] = array ( "image_name" => $image_name . "." . $ext, "image_lab_values" => $actual_final_distinct_lba_value_occurences ); $string = json_encode ( $initial_array_lab ); file_put_contents ( $lab_database, $string ); } echo "fichier ajouté avec succès à la base lab\n"; var_dump(array ( "image_name" => $image_name . "." . $ext, "image_lab_values" => $actual_final_distinct_lba_value_occurences )); }
	 */
	public static function upload_icon_to_lba_database($image_name, $ext, $img_repository, $lab_database_dir) {
		if ($dir = @opendir ( $lab_database_dir )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					if ($current_name . "." . $current_ext == $image_name . "." . $ext . ".lab") {
						echo "fichier exisant";
						return - 1;
					}
				}
			}
		}	
		closedir ( $dir );	
		$img = Database_Manager::read_image ( $img_repository . $image_name, $ext );
		$icon = new Icon ( $img, $image_name . "." . $ext );		
		$actual_distinct_rgb_value = Lba_transformer::get_distinct_rgb_values_occurences ( $icon );		
		$actual_final_distinct_rgb_value_occurences = Lba_transformer::filter_distinct_rgb_values_occurences ( $actual_distinct_rgb_value );	
		$actual_final_distinct_lba_value_occurences = Lba_transformer::get_distinct_lba_for_distinct_rgb ( $actual_final_distinct_rgb_value_occurences );			
		$initial_array_lab  = array (
				"image_name" => $image_name . "." . $ext,
				"image_lab_values" => $actual_final_distinct_lba_value_occurences
		);
		$string = json_encode ( $initial_array_lab );
		$fichier_lab = fopen($lab_database_dir.$image_name.".".$ext.".lab", 'w');
		fputs($fichier_lab, $string); // On écrit le nouveau nombre de pages vues	
		fclose($fichier_lab);	
		echo "fichier ajouté avec succès à la base lab\n";
		var_dump(array (
		"image_name" => $image_name . "." . $ext,
		"image_lab_values" => $actual_final_distinct_lba_value_occurences
		));
	}
	
	public static function upload_icon_repository($temp_file_name,$image_name,$ext,$img_repository){
		//$move_success = move_uploaded_file($_FILES["file_path"]["tmp_name"], getcwd() . "/actual_image/" . $image_name . "." . $ext);	
		if ($dir = @opendir ( $img_repository )) {
			while ( ($file = readdir ( $dir )) !== false ) {
				if ($file != ".." && $file != ".") {
					$current_ext = pathinfo ( $file, PATHINFO_EXTENSION );
					$current_name = pathinfo ( $file, PATHINFO_FILENAME );
					// on vérifie si il est déjà enregistré
					if($current_name .".". $current_ext == $image_name.".".$ext){
						echo "fichier exisant";
						return -1;
					}
				}
			}
		}
			
		closedir ( $dir );
		$move_success = move_uploaded_file($temp_file_name, $img_repository.$image_name.".".$ext);
		echo " \n enregistrement avec succès: " . $move_success;
		return 0;
	}	
	
	public static function  clearFolder($folder){
        // 1 ouvrir le dossier
        $dossier=opendir($folder);
        //2)Tant que le dossier est aps vide
        while ($fichier = readdir($dossier))
        {
                //3) Sans compter . et ..
                if ($fichier != "." && $fichier != "..")
                {
                        //On selectionne le fichier et on le supprime
                        $Vidage= $folder.$fichier;
                        unlink($Vidage);
                }
        }
        //Fermer le dossier vide
        closedir($dossier);
}
}