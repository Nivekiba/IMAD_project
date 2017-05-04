<?php
include_once('checkDevelopper.php');
include_once('AppNameExtractor.php');

//var_dump($argv);
//echo count($argv)."\n ";
var_dump(mobifake_rapid($argv));



// entrées :   le nom de l'applicaiton
// objectif : applique mobifake sur ce nom
// retourne le résultat sous le format json ( tableau d'entreprises et de scores) 
// utilisation : php -q  mobifake_rapid.php  nom_del'app
function mobifake_rapid($argv){
	if (count($argv) != 2   ){
		echo "\n Error :::: please, you should give one argument, and this is the absolute APK path \n\n\n ";
	}else{
		$app_name = $argv[1]; 
		
		$result = array();
		
		gamekeeper($result, $app_name);
		//var_dump($apk_values);
		return array(	"app_name"=>$app_name,
						"mobifake_report"=>$result);
		
	}
}



