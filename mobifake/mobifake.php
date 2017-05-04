<?php
include_once('checkDevelopper.php');
include_once('AppNameExtractor.php');

//var_dump($argv);
//echo count($argv)."\n ";
var_dump(mobifake($argv));



// entrées :   chemin absolu de l'apk
// objectif : applique mobifake sur l'apk
// retourne le résultat sous le format json ( tableau d'entreprises et de scores) 
function mobifake ($argv){
	if (count($argv) != 2   ){
		echo "\n Error :::: please, you should give one argument, and this is the absolute APK path \n\n\n ";
	}else{
		$apk_path = $argv[1]; 
		if (substr($apk_path,0 ,1) != "/") {echo " \n    please you should give absolute path   \n ";}
		else {
			$result = array();
			$apk_values  = extract_name_image($apk_path);
			gamekeeper($result, $apk_values["app_name"]);
			//var_dump($apk_values);
			return array(	"app_name"=>$apk_values["app_name"],
							"mobifake_report"=>$result);
		}
	}
}



