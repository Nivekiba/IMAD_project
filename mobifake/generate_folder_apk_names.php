<?php
//include_once ('mobifake_function.php');
include_once('AppNameExtractor.php');

// var_dump($argv);
// echo count($argv)."\n ";
generate_folder_apk_names ( $argv );

// entrée : dossier contenant des fichiers apk d'applications  appelé dossier
// objectif : utiliser le module de décompression de mobifake pour extraire leur noms et leurs icônes 
//            ceci sera utile lors de la sélection des apk lié au groupe entreprises nouvellement créées (create) de l'évaluation de mobifake
// sortie : un fichier qui contient 
//        sur une ligne : MD5_apk,nom application,chemin vers l'icône.
//		
//         on utilisera ce fichier (ou alors ce qu'il en restera après modification) en entrée d'un autre script pour le déplacement des applis liéées aux lignes retenues
//			
// 			
// utilisation  :  php -q  generate_folder_apk_names.php  chemin_absolu_vers_le_dossier_des_apks
function generate_folder_apk_names ($argv) {
	if (count ( $argv ) != 2) {
		echo "\n Error :::: please, you should give one argument, and this is the absolute APK folder path \n\n\n ";
	} else {
		$apk_folder_path = $argv [1];
		if (substr ( $apk_folder_path, 0, 1 ) != "/") {
			echo " \n please you should give absolute path \n ";
		} else {
			//$result = array ();
			$current_directory = getcwd()."/";
			mkdir ($current_directory."report");
			$current_folder_name = pathinfo ( $apk_folder_path, PATHINFO_FILENAME );
			$report_file_name =  getcwd()."/report/apk_informations_" . $current_folder_name."_".date('d_m_Y_H_i_s');
				
			if ($dir = @opendir ( $apk_folder_path )) {
				while ( ($file = readdir ( $dir )) !== false ) { // lecture des apk
					if ($file != ".." && $file != ".") {
						$current_apk_file_name_without_extension = pathinfo ( $file, PATHINFO_FILENAME );
						$current_apk_file_name_with_extension = pathinfo ( $file, PATHINFO_FILENAME ).".apk";
						
											
						echo " \n dossier de l'apk : ".$apk_folder_path ;
						echo " \n nom de l'apk "  .  $current_apk_file_name_with_extension  ;
						echo " \n composition des deux : ". $apk_folder_path . "/".$current_apk_file_name_with_extension ;
						$apk_informations = array();
						$apk_informations["SHA256"] = $current_apk_file_name_with_extension;
						$apk_values  = extract_name_image($apk_folder_path . "/".$current_apk_file_name_with_extension );
					    $apk_informations["app_name"]	= $apk_values["app_name"];
					    $apk_informations["icon_path"]	= $apk_values["icon_path"];
						
						echo " \n nom du dossier contenant l'APK (dossier de test): ".$current_folder_name;
	               
						$string = $apk_informations["SHA256"].",".  $apk_informations["app_name"].",". $apk_informations["icon_path"]	;
						
						
						$fichier_result = fopen ( $report_file_name , 'a' );
						fputs ( $fichier_result, $string );
						fclose ( $fichier_result );
						
					}
					
				}
			}
		}
	}
}



