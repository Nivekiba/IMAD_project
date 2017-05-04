<?php
// fonction d'utilisation de curl

$debut_script = round(microtime(true)*1000);

echo "RECUPERATION  DE L'APK";
echo "\n test http check server  " . $_POST["key"] . "\n";
echo "\n test http check server  file name   " . $_FILES["file_path"]["name"];
echo "\n test http check server  file size " . $_FILES["file_path"]["size"];
echo "\n  actual location:" . getcwd();
$nowtime = time();
$apk_name = $nowtime."";
$move_success = move_uploaded_file($_FILES["file_path"]["tmp_name"], getcwd() . "/apk_repository/" . $apk_name . ".apk");
echo " \n  déplacement avec succès: " . $move_success;


echo "DECOMPILATION DE L'APK";
$command = 'export PATH=$PATH:' . getcwd() . '/apktool-install-linux-r05-ibot;';
$cmd = $command."bash " .getcwd() ."/extract_app.sh " . $apk_name . "  2>&1";
$result2 = shell_exec($cmd);
echo "\n résultat:  \n" . $result2;




echo "EXTRACTION DU NOM DE L'APPLICATION";
// extration du nom de l'application
//$lines = select_string_files($apk_name);
$lines = file(getcwd().'/extraction_directory_'.$apk_name.'/'.$apk_name."/res/values/strings.xml"."");

foreach ($lines as $lineNumber => $lineContent) {
    if(preg_match("#app_name#",$lineContent)){
        $i = strpos($lineContent, ">");
        $j = strpos($lineContent, "<",5);
        $app_name= substr($lineContent,$i+1 , $j-$i-1);
        echo $lineNumber,' ',$lineContent;
        break;
    }
    //echo $lineNumber, ' ', $lineContent;   
}
echo "\n app_name :".$app_name;
echo "\n";



echo "EXTRACTION DE L'ICONE DE L'APPLICATION";
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
$mv = move_icon($res_folder,$folder,$icon_name,getcwd().'/extracted_icon_directory/');
echo "\n copie de l'icone:  ".$mv ;






/*function select_string_files($apk_name){
	$res_folder = '/extraction_directory/'.$apk_name.'/res/';
	//$lines = file(getcwd().'/extraction_directory/'.$apk_name."/res/values/strings.xml"."");
	$string_founded = false;
	if ($dir = @opendir ( $res_folder )) {
		while ( ($sub_folder = readdir ( $dir )) !== false ) {
			if ($sub_folder != ".." && $sub_folder != "." && preg_match("/values/", $sub_folder)) {
				echo "\n le dossier en cours : ".$sub_folder;
				if ($actual_folder_dir = @opendir ( $res_folder.$sub_folder )) {
					while ( ($xml_file = readdir ( $actual_folder_dir )) !== false ) {
						if ($xml_file != ".." && $xml_file != "." && preg_match("/^string.xml/", $xml_file)) {
							echo "\n le fichier retrouvée ".$image_file;
							$final_path_string = $res_folder.$sub_folder."/".$xml_file;
							$string_founded = true;
							
							break;
						}
					}
				}
			}
			if($string_founded){
				break;
			}
		}
	}else {
		return "le fichier des ressources n'a pas pu être ouvert";
	}
	return file($final_path_string);
}*/


// réalise la copie de l'icone vers la destination
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
							break;
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











/*
 $folder = getcwd().'/extraction_directory/apk_name/res/';
$icon_name = "";
$lineContent = '<application android:allowBackup="true" android:icon="@mipmap/ic_launcher" android:label="@string/app_name" android:name="com.job.orangecleaner.OrangeCleanerApplication" android:theme="@style/AppTheme">';
if(preg_match_all(
		//'#<\e*application[.\n]*android:icon\e*=\e*"@([a-z_0-9]*)\/([a-z_0-9]*)[.\n]*>#',
		'#<\e*application.*android:icon\e*=\e*"@([a-z_0-9]*)\/([a-z_0-9]*).*>#',
		$lineContent,$out,PREG_SET_ORDER)){
var_dump($out);
$folder=$folder.$out[0][1];
$icon_name=$icon_name.$out[0][2];
}
echo "\n folder :".$folder;
echo "\n icon :".$icon_name;
echo " \n test";*/







//phpinfo();


 /*
/// exemple de requête google
$google_result_string = httpGet("https://www.googleapis.com/customsearch/v1"
         . "?key=AIzaSyDUdl9NUi6_ULzvVcK_q3FnSx24NKyxeUg"
         . "&cx=008737521736637413879:-y9h8xwzw0a"
         . "&q=apple"
        . "&filter=1");
 
echo "\n google_result : ".$google_result_string;


$google_result = json_decode($google_result_string);
$items = (array) $google_result->{'items'};


// analyse des mots recurrents (simulation et tests)

/*$liste_marques = array("Apple","Google","Coca-Cola","IBM","Microsoft","General Electric","Samsung","Toyota",
    "Mc Donald's","Mercedes Benz","BMW","Intel","Disney","Cisco","Amazon","Oracle","HP","Gillette",
    "Louis Vuitton","Honda","H&M","Nike","American Express","Pepsi","SAP","IKEA","UPS","eBay","Facebok",
    "Pampers","Wolkswagen","Kellogg's","HSBC","Budweiser","JP Mogan","Zara","Canon","Nescafé","Ford",
    "Hyundnai","Gucci","Philips","L'Oréal","Accenture","Audi","Hermès Paris","Goldman Sachs","Citi",
    "Siemens","Colgate","Danone","Sony","Axa","Nestlé","Allianz","Nissan","Thomson Reuters","Cartier",
    "Adidas","Porsche","Caterpillar","Xerox","Morgan Stanley","Panasonic","Shell","3M","Discovery","KFC",
    "Visa","Prada","Tiffany & Co","Sprite","Burberry","Kia","Santander","Starbucks","Adobe","Johnson & Johson",
    "John Deere","MTV","DHL","Chevrolet","Ralph Lauren","Duracell","Jack Daniel's","Johnnie Walker","Harley Davidson",
    "Mastercard","Kleenex","Smirnoff","Land Rover","FedEx","Corona","Huawei","Heineken","Pizza Hut","Hugo Boss","Nokia","Gap","Nintendo");
*/
/*$liste_marques = array("djfosdml","sfsfqsdfs","dfsqd","dfsqd","dfqsfd","qfsdf","dsff","sff",
    "dsfsd","fcsd","dfdd","dfdf","dfdxx","sfrdes","sdfdf","dsfs","dffsdf","sfdsf",
    "dfqs","sfsdf","sdf","sfdfc","dsfcsc","sfds","jvg","llmkj","jklk","po","ohnhku",
    "igubuyj","jjb","hkkjbbkj","jhbjb","hknkj","jljhi","vjvh","gjhb","gubjb","ihkh",
    "vgjhb","fvhj","jvhjhv","hiinknk","jgjygbjhb","bjhb","gbjbj","vhbjhn","jhkbjh",
    "khb","kkbhk","bhjbj","ugb","uhbkb","iugg","kugu","ghib","hkn","uhubbkjb",
    "gbjkjbk","lohnkkj","hknnkj","kgbj","hknkj","bknk","jhhknk","jllnjl","bghkj","dgdg",
    "ugbbk","kjhj","bjkj","hln","knklnkl","Kia","jkbk","nk","ghj","ljk",
    "uubhu","jhbv","ghbuj","gvgvhjbv","gkjiui","vjhjb","jbjb","jb","hbjk",
    "bhujbj","gjybjb","hvjjj","jgbjuj","gjbj","jgjbjbkj,","gjkbjkhjk","hbkik","hkbnjkn","jbnj","bjjbjbjk","nnjnk","bkjnnk");
*/

/*
$liste_marques = array("facebook","youtube","yahoo","live","msn","wikipedia.org","blogspot","baidu","microsoft",
"qq","bing","ask","adobe","taobao","twitter","youku","soso","wordpress","sohu","hao123",
"163","tudou","amazon","apple","ebay","4399","yahoo","linkedin","go","tmall","paypal",
"sogou","ifeng","aol","xunlei","craigslist.org","orkut","56","orkut","about","skype",
"7k7k","dailymotion","flickr","pps","qiyi","bbc.uk","4shared","mozilla","ku6","imdb",
"cnet","babylon","mywebsearch","alibaba","mail","uol","badoo","cnn","myspace","netflix",
"weather","soku","weibo","renren","rakuten","17kuxun","yandex","booking","ehow","bankofamerica",
"58","zedo","2345","globo","mapquest","goo","answers","360","chase","naver","hp","odnoklassniki",
"alipay","huffingtonpost","ameblo","ganji","alot","scribd","megaupload",
"tumblr","softonic","camzap","vkontakte","avg","walmart","pptv","xinhuanet","mediafire");


$fichier_title = fopen(getcwd()."/datamining_directory/titles.txt", "w+");
$fichier_snippet = fopen(getcwd()."/datamining_directory/snippet.txt", "w+");
$fichier_pagemap_metatags_ogtype = fopen(getcwd()."/datamining_directory/pagemap_metatags_ogtype.txt", "w+");
$fichier_pagemap_metatags_ogsitename = fopen(getcwd()."/datamining_directory/pagemap_metatags_ogsitename.txt", "w+");
$fichier_pagemap_metatags_ogtitle = fopen(getcwd()."/datamining_directory/pagemap_metatags_ogtitle.txt", "w+");
$fichier_pagemap_metatags_ogurl = fopen(getcwd()."/datamining_directory/pagemap_metatags_ogurl.txt", "w+");
$fichier_pagemap_breadcrumbs_url = fopen(getcwd()."/datamining_directory/pagemap_breadcrumbs_url.txt", "w+");
$fichier_pagemap_breadcrumbs_title = fopen(getcwd()."/datamining_directory/pagemap_breadcrumbs_title.txt", "w+");

  
foreach ($liste_marques as $marque_actuelle) {
   echo "\nrecherche :".$marque_actuelle;
   $google_result_string = httpGet("https://www.googleapis.com/customsearch/v1?"
            . "key=AIzaSyDUdl9NUi6_ULzvVcK_q3FnSx24NKyxeUg"
            . "&cx=008737521736637413879:-y9h8xwzw0a"
           . "&filter=1"
           . "&hq=marque"
            . "&q=".$marque_actuelle
        );
    //$google_result_string = file_get_contents(getcwd()."/datamining_directory/resultat_de_test.txt");
    
     $google_result = json_decode($google_result_string);
    //echo "\n google_result : ".$google_result;
    $items = (array) $google_result->{'items'};
    foreach ($items as $item_actuel) {
        fwrite($fichier_title, " ".  str_replace($marque_actuelle," ", strtolower ($item_actuel->{'title'}))."\n");
        fwrite($fichier_snippet, " ". str_replace($marque_actuelle," ",strtolower ($item_actuel->{'snippet'}))."\n");
        if (isset($item_actuel->{'pagemap'})){
            $page_map_actuel  = $item_actuel->{'pagemap'};
            if($page_map_actuel ->{'metatags'}){
                $metatags = (array) $page_map_actuel ->{'metatags'};
                foreach ($metatags as $metatag_actuel) {
                    fwrite($fichier_pagemap_metatags_ogtype," ". str_replace($marque_actuelle," ",strtolower ($metatag_actuel->{'og:type'}))."\n");
                    fwrite($fichier_pagemap_metatags_ogsitename, " ". str_replace($marque_actuelle," ",strtolower ($metatag_actuel->{'og:site_name'}))."\n");
                    fwrite($fichier_pagemap_metatags_ogtitle, " ". str_replace($marque_actuelle," ",strtolower ($metatag_actuel->{'og:title'}))."\n");
                    fwrite($fichier_pagemap_metatags_ogurl, " ".str_replace($marque_actuelle," ",strtolower ($metatag_actuel->{'og:url'}))."\n");     
                }
            }
            if($page_map_actuel ->{'breadcrumb'}){
                $breadcrumbs = (array) $page_map_actuel ->{'breadcrumb'};
                foreach ($breadcrumbs as $breadcrumb_actuel) {
                    fwrite($fichier_pagemap_breadcrumbs_url, " ".str_replace($marque_actuelle," ",strtolower ($breadcrumb_actuel->{'url'}))."\n");
                    fwrite($fichier_pagemap_breadcrumbs_title, " ".str_replace($marque_actuelle," ",strtolower ($breadcrumb_actuel->{'title'}))."\n");
                        
                }
            }
            
        }
        
    }  
}
echo "\n google_result : ".$google_result_string;
fclose($fichier_pagemap_breadcrumbs_title); 
fclose($fichier_pagemap_breadcrumbs_url);
fclose($fichier_pagemap_metatags_ogsitename);
fclose($fichier_pagemap_metatags_ogtitle);
fclose($fichier_pagemap_metatags_ogtype);
fclose($fichier_pagemap_metatags_ogurl);
fclose($fichier_snippet);
fclose($fichier_title);


$chaine_fichier_title =  file_get_contents(getcwd()."/datamining_directory/titles.txt");
$chaine_fichier_snippet =  file_get_contents(getcwd()."/datamining_directory/snippet.txt");
$chaine_fichier_pagemap_metatags_ogtype =  file_get_contents(getcwd()."/datamining_directory/pagemap_metatags_ogtype.txt");
$chaine_fichier_pagemap_metatags_ogsitename =  file_get_contents(getcwd()."/datamining_directory/pagemap_metatags_ogsitename.txt");
$chaine_fichier_pagemap_metatags_ogtitle = file_get_contents(getcwd()."/datamining_directory/pagemap_metatags_ogtitle.txt");
$chaine_fichier_pagemap_metatags_ogurl = file_get_contents(getcwd()."/datamining_directory/pagemap_metatags_ogurl.txt");
$chaine_fichier_pagemap_breadcrumbs_url = file_get_contents(getcwd()."/datamining_directory/pagemap_breadcrumbs_url.txt");
$chaine_fichier_pagemap_breadcrumbs_title = file_get_contents(getcwd()."/datamining_directory/pagemap_breadcrumbs_title.txt");


echo "\n \n dans les titres : ";

$words = array_count_values(str_word_count($chaine_fichier_title, 1));
arsort($words);
print_r($words);


echo "\n \n dans les snippets : ";

$words = array_count_values(str_word_count($chaine_fichier_snippet, 1));
arsort($words);
print_r($words);

echo "\n \n dans les  types : ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_metatags_ogtype, 1));
arsort($words);
print_r($words);

echo "\n \n dans les noms des sites : ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_metatags_ogsitename, 1));
arsort($words);
print_r($words);

echo "\n \n dans les titres des métadonnées: ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_metatags_ogtitle, 1));
arsort($words);
print_r($words);

echo "\n \n dans les url : ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_metatags_ogurl, 1));
arsort($words);
print_r($words);

echo "\n \n dans les fil d'ariaine : ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_breadcrumbs_title, 1));
arsort($words);
print_r($words);

echo "\n \n dans les url fil d'ariane : ";

$words = array_count_values(str_word_count($chaine_fichier_pagemap_breadcrumbs_url, 1));
arsort($words);
print_r($words);*/
