<?php

$debut_script = round(microtime(true)*1000);

function extract_name_image($apk_path){
	
	$nowtime = time(); $tmp_apk_name = $nowtime."";
	decompile_apk($apk_path,$tmp_apk_name);
	$apk_decompiled_folder = getcwd().'/extraction_directory_'.$tmp_apk_name.'/'.$tmp_apk_name;
	$app_name = extract_application_name($apk_decompiled_folder);
	$icon_path = extract_application_icon($tmp_apk_name);
	deleteDirectory(getcwd().'/extraction_directory_'.$tmp_apk_name.'/');
	return array("app_name" => $app_name , "icon_path" =>$icon_path);
	
}

function deleteDirectory($dir) {
	system('rm -rf ' . escapeshellarg($dir), $retval);
	return $retval == 0; // UNIX commands return zero on success
}

function decompile_apk($apk_path,$tmp_apk_name){
	echo "DECOMPILATION DE L'APK \n  ".$apk_path;
	$command = 'export PATH=$PATH:' . getcwd() . '/apktool-install-linux-r05-ibot;';
	$cmd = $command."bash " .getcwd() ."/extract_app.sh " . $apk_path . " " . $tmp_apk_name . " 2>&1";
	$result_shell = shell_exec($cmd);
	echo " \n résultat de l'apk_tool".$result_shell."\n ";
	return  $result_shell;
	
}
//getcwd().'/extraction_directory_'.$apk_name.'/'.$apk_name

function extract_application_name($apk_decompiled_folder){
	echo "EXTRACTION DU NOM DE L'APPLICATION \n ";
	$app_name = "inconnu";
	$lines = file($apk_decompiled_folder."/res/values/strings.xml"."");
	foreach ($lines as $lineNumber => $lineContent) {
		if(preg_match("#app_name#",$lineContent)){
			$i = strpos($lineContent, ">");
			$j = strpos($lineContent, "<",5);
			$app_name= substr($lineContent,$i+1 , $j-$i-1);
			echo $lineNumber,' ',$lineContent;
			break;
		}
	}
	echo "\n app_name :".$app_name;
	echo "\n";
	return $app_name;		
}


function extract_application_icon ($apk_name){
	echo "EXTRACTION DE L'ICONE DE L'APPLICATION \n ";
	// On teste si s'est écrit en dur dans AndroidManifest
	//$folder = getcwd().'/extraction_directory/'.$apk_name."/res/";
	$res_folder = getcwd().'/extraction_directory_'.$apk_name.'/'.$apk_name."/res/";
	$folder = "";
	$icon_name = "";
	$lines = file(getcwd().'/extraction_directory_'.$apk_name.'/'.$apk_name."/AndroidManifest.xml"."");
	foreach ($lines as $lineNumber => $lineContent) {
		if(preg_match_all('#<\e*application.*android:icon\e*=\e*"@([a-z_0-9]*)\/([a-z_0-9]*).*>#',$lineContent,$out,PREG_SET_ORDER)){
			$folder=$folder.$out[0][1];
			$icon_name=$icon_name.$out[0][2];
			//var_dump($lineContent);
		}
	
	}
	echo "\n folder :".$folder;
	echo "\n icon :".$icon_name;
	echo "\n";
	$icon_file_name = move_icon($res_folder,$folder,$icon_name,getcwd().'/extracted_icon_directory/');
	echo "\n copie de l'icone:  ".$icon_file_name ;
	$icon_path = getcwd().'/extracted_icon_directory/'.$icon_file_name;
	return $icon_path ;
}






function move_icon($res_folder,$folder,$icon_name,$destination){
	$icon_founded = false;
	if ($dir = @opendir ( $res_folder )) {
		while ( ($sub_folder = readdir ( $dir )) !== false ) {
			if ($sub_folder != ".." && $sub_folder != "." && preg_match("/".$folder."/", $sub_folder)) {
				echo "\n le dossier a été retrouvé : ".$sub_folder;
				if ($actual_folder_dir = @opendir ( $res_folder.$sub_folder )) {
					while ( ($image_file = readdir ( $actual_folder_dir )) !== false ) {
						if ($image_file != ".." && $image_file != "." && preg_match("/^".$icon_name."./", $image_file)) {
							echo "\n icone retrouvée ".$image_file;
							$final_path_icon = $res_folder.$sub_folder."/".$image_file;
							$icon_founded = true;                                           
							copy($final_path_icon, $destination.$image_file);
							return $image_file;
						}
					}
				}
			}
			if($icon_founded){
				break;
			}
		}
	}else {
		return "le fichier des ressources n'a pas pu être ouvert";
	}
	return "success";
}


function TailleDossier($Rep)
{
	$Racine=opendir($Rep);
	$Taille=0;
	while($Dossier = readdir($Racine))
	{
		if($Dossier != '..' And $Dossier !='.')
		{
			if(is_dir($Rep.'/'.$Dossier)) $Taille += TailleDossier($Rep.'/'.
					$Dossier); //Ajoute la taille du sous dossier

					else $Taille += filesize($Rep.'/'.$Dossier);
					//Ajoute la taille du fichier

		}
	}
	closedir($Racine);
	return $Taille;
}

