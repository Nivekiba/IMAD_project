<?php
include_once('checkDevelopper.php');
include_once('AppNameExtractor.php');

//var_dump($argv);
//echo count($argv)."\n ";
//var_dump(mobifake($argv));



// entrées :   chemin absolu de l'apk
// objectif : applique mobifake sur l'apk
// retourne le résultat sous le format json ( tableau d'entreprises et de scores) 
function mobifake ($apk_path){
	
		
			$result = array();
			$apk_values  = extract_name_image($apk_path);
			gamekeeper($result, $apk_values["app_name"]);
			//var_dump($apk_values);
			return array(	"app_name"=>$apk_values["app_name"],
							"mobifake_report"=>$result);
	}



