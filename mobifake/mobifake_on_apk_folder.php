<?php
include_once ('mobifake_function.php');

// var_dump($argv);
// echo count($argv)."\n ";
mobifake_on_app_folder ( $argv );

// entrée : dossier contenant des fichiers apk d'applications  appelé dossier
// objectif : utiliser mobifake pour examiner ces fichiers et générer le résultat
// sortie : un fichier dans le dossier report 
//			nom du fichier : mobifake_dossier_date 
//			format de chaque ligne :   apk_name,nombre_entreprises,entreprise_1,entreprise_2,....
// 			
// utilisation  :  php -q  mobifake_on_apk_folder.php  chemin_absolu_vers_le_dossier_des_apks
function mobifake_on_app_folder($argv) {
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
			$report_file_name =  getcwd()."/report/mobifake_" . $current_folder_name."_".date('d_m_Y_H_i_s');
				
			if ($dir = @opendir ( $apk_folder_path )) {
				while ( ($file = readdir ( $dir )) !== false ) { // lecture des apk
					if ($file != ".." && $file != ".") {
						$current_apk_file_name_without_extension = pathinfo ( $file, PATHINFO_FILENAME );
						$current_apk_file_name_with_extension = pathinfo ( $file, PATHINFO_FILENAME ).".apk";
						
							
						
												
						echo " \n dossier de l'apk : ".$apk_folder_path ;
						echo " \n nom de l'apk "  .  $current_apk_file_name_with_extension  ;
						echo " \n composition des deux : ". $apk_folder_path . $current_apk_file_name_with_extension ;
						$mobifake_results = mobifake ( $apk_folder_path . $current_apk_file_name_with_extension);
						echo " \n nom du dossier contenant l'APK (dossier de test): ".$current_folder_name;
	               
						
						echo "\n------------construction de la chaine à écrire: ". $string."\n----------\n";
						$mobifake_report = $mobifake_results["mobifake_report"];
						$enterprises = "";
						foreach ($mobifake_report as $key => $value) {
							echo "Clé : $key; Valeur : $value<br />\n";
							$enterprises =$enterprises.",".$key;
						}
						$string = $current_apk_file_name_with_extension.",".$mobifake_results ["app_name"].",".count($mobifake_report).$enterprises."\n";
						echo "\n----------- chaine  à écrire dans le fichier \n ";
						echo $string."\n";
						
						
						
						$fichier_result = fopen ( $report_file_name , 'a' );
						fputs ( $fichier_result, $string );
						fclose ( $fichier_result );
						
					}
					
				}
			}
		}
	}
}



