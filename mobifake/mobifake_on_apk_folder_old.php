<?php
include_once ('mobifake_function.php');

// var_dump($argv);
// echo count($argv)."\n ";
mobifake_on_app_folder ( $argv );

// entrée : dossier contenant des fichiers apk d'applications
// objectif : utiliser mobifake pour examiner ces fichiers et générer le résultat
// sortie : un fichier dans le répertoire general_report/mobifake_nom_dossier au format csv 
// pour chaque entrée on a : le nom de l'apk(fichier analysé), le nom de l'application, les résultat de mobifake
function mobifake_on_app_folder($argv) {
	if (count ( $argv ) != 2) {
		echo "\n Error :::: please, you should give one argument, and this is the absolute APK folder path \n\n\n ";
	} else {
		$apk_folder_path = $argv [1];
		if (substr ( $apk_folder_path, 0, 1 ) != "/") {
			echo " \n please you should give absolute path \n ";
		} else {
			$result = array ();
			if ($dir = @opendir ( $apk_folder_path )) {
				while ( ($file = readdir ( $dir )) !== false ) { // lecture des apk
					if ($file != ".." && $file != ".") {
						$current_apk_sha256 = pathinfo ( $file, PATHINFO_FILENAME );
						$current_apk_file_name = pathinfo ( $file, PATHINFO_FILENAME ).".apk";
						$current_apk_base_name = pathinfo ( $file, PATHINFO_BASENAME );
						$current_folder_name = pathinfo ( $apk_folder_path, PATHINFO_FILENAME );
							
						
						$general_report_file_name = "general_report/" . $current_folder_name;
						
						echo " \n dossier de l'apk : ".$apk_folder_path ;
						echo " \n nom de l'apk "  .  $current_apk_file_name  ;
						echo " \n composition des deux : ". $apk_folder_path . $current_apk_file_name ;
						$mobifake_results = mobifake ( $apk_folder_path . $current_apk_file_name);
						
						
						echo " \n nom du dossier contenant l'APK : ".$current_folder_name;
	               
						
						echo "\n------------chaine : ". $string."\n----------\n";
						
					
						$string = $current_apk_sha256.";".$mobifake_results ["app_name"].";".json_encode($mobifake_results ["mobifake_report"])."\n";
						echo "-----------\n à écrire dans le fichier \n ";
						echo $string."\n";
						
						
						
						$fichier_result = fopen ( $general_report_file_name , 'a' );
						fputs ( $fichier_result, $string );
						fclose ( $fichier_result );
						
											}
					
				}
			}
		}
	}
}



