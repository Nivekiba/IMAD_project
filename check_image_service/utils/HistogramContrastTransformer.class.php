<?php

include_once "../entities/Icon.class.php";
include_once "Constants.class.php";

class HistogramContrastTransformer {
	
	public static function transform_to_saliency($actual_img,$image_name,$ext){
		$quantified_image = HistogramContrastTransformer::getQuantifiedImage($actual_img);
		
		///test image quantifiée 
		Database_Manager::produce_image($quantified_image,Constants::getQuantifiedImagesDir(), $image_name,$ext );
		
		
		$distinct_colors = HistogramContrastTransformer::getDistinctRgbValuesOccurences($quantified_image);
		
		
		
		
		
		
		
		$color_importance_distinction = HistogramContrastTransformer::getUsefullAndUselessRgbValuesOccurences($distinct_colors);
		
		
		
		$usefull_colors = $color_importance_distinction["usefullValues"];
		$useless_colors = $color_importance_distinction["uselessValues"];
		
		
		$color_importance_associated = HistogramContrastTransformer::associateUsefullUselessValues($usefull_colors, $useless_colors);
		$usefull_colors = $color_importance_associated["usefullValues"];
		$useless_colors = $color_importance_associated["uselessAssociatesValues"];
		
		
		
		
		
		$usefullQuantifiedImage = HistogramContrastTransformer::getUsefullQuantifiedImage($quantified_image,$usefull_colors,$useless_colors);
		
		/// test de l'image quantifiée de manière utile
		Database_Manager::produce_image($usefullQuantifiedImage,Constants::getUsefullQauntifiedImageDir(), $image_name,$ext );
		
		
		
		$usefull_color_saliency = HistogramContrastTransformer::calculateSaliencyForColors($usefull_colors);
		//echo "\n \n \n \n \n couleurs distinctes importantes et saillance";
		//var_dump($usefull_color_saliency);
		
		
		/// test image de saillance 
		$nofinal_saliency_image = HistogramContrastTransformer::getImageSaliencyFromSmothedImage
		($usefullQuantifiedImage,$usefull_color_saliency);
		Database_Manager::produce_image($nofinal_saliency_image,Constants::getSalientImagesDir(),
		                                 $image_name."_before_smoothing_",$ext );
		
		
		
	    // test de la liste munie des saillances 
	    //echo "\n -------------liste de saillance--------------\n ";
	    //var_dump($usefull_color_saliency);	
		
		
		$smoothed_usefull_color_saliency = HistogramContrastTransformer::calculateSmoothingSaliencyForColors($usefull_color_saliency);
		
		
		
		// test de la liste munie des saillances améliorées 
		//echo "\n ---\n----------liste de saillance améliorées--------------\n ";
		//var_dump($smoothed_usefull_color_saliency);
		
		
		$final_saliency_image = HistogramContrastTransformer::getImageSaliencyFromSmothedImage($usefullQuantifiedImage,$smoothed_usefull_color_saliency);
		
		/// test image de saillance améliorée
		Database_Manager::produce_image($final_saliency_image,Constants::getSalientImagesDir(), $image_name."_saillance_am",$ext );
		
		
		/*$final_binarized_saliency_image = HistogramContrastTransformer::binarize($final_saliency_image,Constants::getThresholdTrimapInitialization());
		
		
		/// test image de saillance améliorée et binarisée
		Database_Manager::produce_image($final_binarized_saliency_image,Constants::getSalientImagesDir(), $image_name."_binarize",$ext );
		
		*/
		
		
		
		
		return $final_saliency_image;
	}
	
	
	/// binarisation de l'image avec un threshold
	public static function binarize($gdImage,$treshold){
		$w = imagesx ( $gdImage );
		$h = imagesy ( $gdImage );
		$binarizedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				$pixelrgb = imagecolorat($gdImage,$x,$y);
				$cols = imagecolorsforindex($gdImage, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				if($r>=$treshold){
					$color = imagecolorallocate($binarizedImage,255,255,255);
					imagesetpixel($binarizedImage, $x, $y, $color);
					
				}else{
					$color = imagecolorallocate($binarizedImage,0,0,0);
					imagesetpixel($binarizedImage, $x, $y, $color);
				}	
			}
		}
		return $binarizedImage;
	}
	
	/// récupère une impage lissée  et une liste de couleurs et d'occurences et de saillances  
	/// produit l'image présentant la saillance 
	public static function getImageSaliencyFromSmothedImage($GDSmothedImage,$usefullColorSaliency){
		$w = imagesx ( $GDSmothedImage );
		$h = imagesy ( $GDSmothedImage );
		$SaliencyImage = imagecreatetruecolor($w, $h);	$n=0;		
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				
				$pixelrgb = imagecolorat($GDSmothedImage,$x,$y);
				$cols = imagecolorsforindex($GDSmothedImage, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				foreach ($usefullColorSaliency as $color_saliency){
					if($color_saliency["r"]==$r &&
					$color_saliency["g"]==$g &&
						$color_saliency["b"]==$b ){
						$color = imagecolorallocate($SaliencyImage,
								$color_saliency["saliency"],
								$color_saliency["saliency"],
								$color_saliency["saliency"]);
						/*$color = imagecolorallocate($SaliencyImage,
								$color_saliency["r"],
								$color_saliency["g"],
								$color_saliency["b"]);*/
						imagesetpixel($SaliencyImage, $x, $y, $color); 
						break;
					}
				}		
			}
		}
		//echo "\n  \n nombre d'aff".$n;
		return $SaliencyImage;
	}
	
	
	//-- 6------------------- calcule la nouvelle saillance de chaque couleurs dans la liste
	public static function calculateSmoothingSaliencyForColors($usefullColorsSaliency){
		$n = count($usefullColorsSaliency);
		$new_list = array();$usefullColorsSaliency_ameliore = array();
		for($i = 0; $i < $n; $i ++) {
			$new_list[] = $usefullColorsSaliency[$i];
		}
		for($i = 0; $i < $n; $i ++) {
			$usefullColorsSaliency_ameliore[] = $usefullColorsSaliency[$i];
		}
		
		for($i = 0; $i < $n; $i ++) {
			array_splice($new_list,$i,1);		
			$T= 0;		
			$voisins = HistogramContrastTransformer::calculIndexKPPVoisin($i,
					 $usefullColorsSaliency);
			foreach ($voisins as $voisin){
				$T+=HistogramContrastTransformer::calculLbaDistance(
						$usefullColorsSaliency[$i], $usefullColorsSaliency[$voisin]);				
			}
			
			 //test valeur de T 
			//echo " \n ------ valeur de voisins :  \n";var_dump($voisins);
			
			$new_salency = $usefullColorsSaliency_ameliore[$i]["saliency"];
			foreach ($voisins as $voisin){
				$new_salency=$new_salency+
				(($T-HistogramContrastTransformer::calculLbaDistance(
						$usefullColorsSaliency[$i], $usefullColorsSaliency[$voisin]))*
						$usefullColorsSaliency[$voisin]["saliency"]);
			
				//test valeur de T
				//echo " \n --------------------------valeur de  D :  ".HistogramContrastTransformer::calculLbaDistance(
				//	$usefullColorsSaliency[$i], $usefullColorsSaliency[$voisin]);
				//echo "\n valeur de i ". $i ."INDICE DU VOISIN  ".$voisin;
			
				//echo " \n --------------------------valeur de  S :  ".$usefullColorsSaliency[$voisin]["saliency"];;
					
			}
			$m = count($voisins);
			//echo " \n ------------------------ valeur de m :  ".$m;
				
			if (($m-1)*$T  !=   0){
				$new_salency = $new_salency / (($m-1)*$T);
			}
			//echo " \n -------------------------------------------- valeur de new_silency :  ".$new_salency;
			
			$usefullColorsSaliency_ameliore[$i]["saliency"] = $new_salency;
		
			
			array_splice($new_list, $i, 0, $usefullColorsSaliency[$i]);
			
			
		}
		return $usefullColorsSaliency_ameliore;
	}
	
	
	// outil permettant de calculer les plus proches voisins d'une couleur dans une liste de couleurs
	public static function calculIndexKPPVoisin($ind,$usefullColors){
		// extraction de la couleur de la liste recherchée
		$color = $usefullColors[$ind];
		array_splice($usefullColors,$ind,1);
		// initialisation des voisins
		$n= count($usefullColors);
		$k=  floor($n*Constants::getProportionPointVoisinsLissage());
		$indexVoisins = array();
		
		for($i = 0; $i < $k; $i ++) {
			$indexVoisins[]=$i;
		}
		
		
		for($i = $k+1; $i < $n; $i ++) {
			$d = HistogramContrastTransformer::calculLbaDistance($color, $usefullColors[$i]);
			foreach ($indexVoisins as $indexVoisin){
				if(HistogramContrastTransformer::calculLbaDistance(
						$usefullColors[$indexVoisin], $color)>$d){
					$index = HistogramContrastTransformer::getDistantColorIndexFromVoisins
					($color, $usefullColors, $indexVoisins);
					$indexVoisins[$index]=$i;
					//echo "\n - *---------- évolution des voisins : \n ";
					for($j = 0; $j < $k; $j ++) {
						//echo $indexVoisins[$j]."--";
					}
				}
				break; 
			}			
		}
		for($i = 0; $i < $k; $i ++) {
			if ($indexVoisins[$i]>=$ind){
				$indexVoisins[$i]++;
			}	
		}
		
		//var_dump($indexVoisins);
		return $indexVoisins;
		
		
	}
	
	
	//----------------------- outil------------------------- utilisé pour le calcul des plus proches voisins 
	// --------------------retourne l' index dans usefullColors de la couleur la plus éloignée parmis les voisins 
	public static function getDistantColorIndexFromVoisins($color,$usefullColours,$voisins){
		$result = 0;
		$d = HistogramContrastTransformer::calculLbaDistance($color, $usefullColours[$voisins[0]]);
		$n = count($voisins);
		for($i = 1; $i < $n; $i ++) {
			if(HistogramContrastTransformer::calculLbaDistance
					($color,  $usefullColours[$voisins[$i]])>$d){
				$result = $i;
				$d =  HistogramContrastTransformer::calculLbaDistance($color, $usefullColours[$voisins[$result]]);
			}
		}
		return $result ;
	}
	
	
	// -------------------------outil--------------------------calcule la distance lab entre deux couleurs 
	public static function calculLbaDistance($rgb1,$rgb2){
		$lbaValue = Lba_transformer::rgb_to_lab_format( $rgb1);
		$lbaValueActuelle = Lba_transformer::rgb_to_lab_format($rgb2);
		return Lba_comparer::lba_distance($lbaValue["l"],
				 $lbaValue["a"], 
				$lbaValue["b"],
				$lbaValueActuelle["l"], $lbaValueActuelle["a"],$lbaValueActuelle["b"],
				 Constants::getLabDistanceKl(),
				 Constants::getLabDistanceKh(), 
				Constants::getLabDistanceKc());
	}
	
	
	//  calcule la saillance de chaque couleur passée dans la liste
	public static function calculateSaliencyForColors($usefullList){
		$n = count($usefullList);
		$Ncolors_pixels = 0;
		foreach ($usefullList as $usf){
			$Ncolors_pixels = $Ncolors_pixels + $usf["n"];
		}
		//echo "\n ##########################somme".$Ncolors_pixels; 
		$new_list = array();
		for($i = 0; $i < $n; $i ++) {
			$new_list[] = $usefullList[$i];
		}
		for($i = 0; $i < $n; $i ++) {
			array_splice($new_list,$i,1);		
			$D= 0;
			
			
			//$lbaValue = Lba_transformer::rgb_to_lab_format( $usefullList[$i]);
			
			foreach ($new_list as $usefullRGB){
				//$lbaValueActuelle = Lba_transformer::rgb_to_lab_format($usefullRGB);
				$f = $usefullRGB["n"]/$Ncolors_pixels;
				$D+=$f*HistogramContrastTransformer::calculLbaDistance($usefullList[$i], $usefullRGB);
			}
			array_splice($new_list, $i, 0, $usefullList[$i]);
			$usefullList[$i]["saliency"] = $D;
		}
		return $usefullList;
	}
	
	// --------------5------------------------modifie l'image en obtenant uniquement les couleurs utiles
	public static function getUsefullQuantifiedImage($GDQuantifiedImage,$usefullList,$uselessList){
		$w = imagesx ( $GDQuantifiedImage );
		$h = imagesy ( $GDQuantifiedImage );
		$UsefullGDQuantifiedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				
				
				$pixelrgb = imagecolorat($GDQuantifiedImage, $x, $y);
				$cols = imagecolorsforindex($GDQuantifiedImage, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				$test = HistogramContrastTransformer::coulorInColorList($r, $g, $b, $uselessList);
				if($test["rep"] == true){
					$index_usefull = $uselessList[$test["index"]]["associate"];
					$color = imagecolorallocate($UsefullGDQuantifiedImage,
							$usefullList[$index_usefull]["r"],
							$usefullList[$index_usefull]["g"],
							$usefullList[$index_usefull]["b"]);
					imagesetpixel($UsefullGDQuantifiedImage, $x, $y, $color);	
				}else{
					$color = imagecolorallocate($UsefullGDQuantifiedImage,
							$r,
							$g,
							$b);
					imagesetpixel($UsefullGDQuantifiedImage, $x, $y, $color);
				}
		
			}
		}
		
		return $UsefullGDQuantifiedImage;
	}
	
	// --------------------------------outils ------------ vrai si une couleur est dans la liste
	public static function coulorInColorList($r,$g,$b,$colorList){
		$n = count($colorList);
		for($i=0 ;$i<$n;$i++){
			if($r == $colorList[$i]["r"] && $g== $colorList[$i]["g"] && $b == $colorList[$i]["b"]){
				return array("rep"=>true,"index"=>$i);
			}
		}
		return  array("rep"=>false);
	}
	
	
	//// 4----- associe les couleur non utiles aux utiles
	public static  function associateUsefullUselessValues($usefullValues,$uselessValues){
		//$uselessAssociates = array();
		foreach ($uselessValues as $uselessRGB){
			$index_in_usefull = HistogramContrastTransformer::getIndexNearRgbValue($uselessRGB,
					 $usefullValues);
			$usefullValues[$index_in_usefull]["n"]+=$uselessRGB["n"];
			$uselessRGB["associate"] = $index_in_usefull;
			$uselessAssociates[] = $uselessRGB;	
		}
		return array("usefullValues"=>$usefullValues,"uselessAssociatesValues"=>$uselessAssociates);
	}
	
	
	// ---------------------outil---------------- retourne l'index de la position la plus proche
	public static function getIndexNearRgbValue($rgbValue,$arrayValue){
		$index = 0;$n = count($arrayValue);	
		$distance = HistogramContrastTransformer::calculLbaDistance($rgbValue,$arrayValue[0]);
		for($y=1;$y<$n;$y++){
			$new_distance = HistogramContrastTransformer::calculLbaDistance($rgbValue,$arrayValue[$y]);
			if($new_distance<$distance){
				$index = $y;
				$distance = $new_distance;
			}
		}
		return $index;		
	}
	
	/// 3--- retourne les couleurs utiles et inutiles
	public static function getUsefullAndUselessRgbValuesOccurences
	($distinct_rgb_values_occurences){
	    $usefullRGBValues = array();
	    $uselessRGBValues = array();
	    // calcul de la somme des occurences
	    $sum = 0;
	    foreach($distinct_rgb_values_occurences as $k => $v) {
	    	$sum = $v["n"] + $sum;
	    }
	   
	    $limit = $sum * Constants::getPourcentageCouleursCouvertes();
	    
		$fusion2 = array();
	
		foreach($distinct_rgb_values_occurences as $k => $v) {
			$fusion2[$k] = $v["n"];
		}
	
		array_multisort($fusion2, SORT_DESC,$distinct_rgb_values_occurences);
		$n = sizeof($distinct_rgb_values_occurences);
		$somme_cumulee= 0;
		for($y=0;$y<$n;$y++){
			$somme_cumulee = $somme_cumulee + $distinct_rgb_values_occurences[$y]["n"];
			$usefullRGBValues[]= $distinct_rgb_values_occurences[$y];
			if ($somme_cumulee>$limit) break;
		}
		for($z=$y;$z<$n;$z++){
			$uselessRGBValues[]= $distinct_rgb_values_occurences[$z];
		}
		
		return array("usefullValues"=>$usefullRGBValues,"uselessValues"=>$uselessRGBValues);
	}
	
	
	//-- 2 ------------------ retourne les couleurs de l'image 
	public static function getDistinctRgbValuesOccurences($GDQuantifiedImage){
		$w = imagesx ( $GDQuantifiedImage );
		$h = imagesy ( $GDQuantifiedImage );
		$distinct_triplets_rgb = array ();
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				$pixelrgb = imagecolorat($GDQuantifiedImage,$x,$y);
				$cols = imagecolorsforindex($GDQuantifiedImage, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
			
		
				$find = 0;
				$i = 0;
				foreach ( $distinct_triplets_rgb as $triplet_actuel ) {
					if ($triplet_actuel ["r"] == $r && $triplet_actuel ["g"] == $g && $triplet_actuel ["b"] == $b) {
						$n = $triplet_actuel ["n"];
						$triplet_actuel ["n"] = $n + 1;
						$find = 1;
						$distinct_triplets_rgb [$i] = $triplet_actuel;
						break;
					}
					$i ++;
				}
				if ($find == 0) {
					// echo "find ".$find;
					$rgb_array = array (
							"r" => $r,
							"g" => $g,
							"b" => $b,
							"n" => 1
					);
					$distinct_triplets_rgb [] = $rgb_array;
				}
			}
		}
		return $distinct_triplets_rgb;
	}
	
	
	//-- 1 ------------------ retourne l'image quantifiée  
	public static function getQuantifiedImage($GD_img){
		$w = imagesx ( $GD_img );
		$h = imagesy ( $GD_img );
		$GDQuantifiedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				$pixelrgb = imagecolorat($GD_img,$x,$y);
				$cols = imagecolorsforindex($GD_img, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				//echo "\n couleur actuelle (" . $r .", ".$g." ,". $b ." )";
				$rp = HistogramContrastTransformer::getNearSimpleColor($r, Constants::getNombreCouleursQuantification());
				$gp= HistogramContrastTransformer::getNearSimpleColor($g, Constants::getNombreCouleursQuantification());
			   $bp = HistogramContrastTransformer::getNearSimpleColor($b, Constants::getNombreCouleursQuantification());
				
				$color = imagecolorallocate($GDQuantifiedImage,$rp,$gp,$bp);
				imagesetpixel($GDQuantifiedImage, $x, $y, $color);
				
				/// test des couleurs 
			/*	echo "\n ".HistogramContrastTransformer::getNearSimpleColor($r, Constants::getNombreCouleursQuantification());
				echo "---".HistogramContrastTransformer::getNearSimpleColor($g, Constants::getNombreCouleursQuantification());
				 "----".HistogramContrastTransformer::getNearSimpleColor($b, Constants::getNombreCouleursQuantification());
		*/
			}
		}
		
		return $GDQuantifiedImage;
	}
	
	// ------------------ fait la quantification de couleurs 
	public static function getNearSimpleColor($color,$nombreCouleurs){
		$alpha = floor(255/$nombreCouleurs);
		return floor($color/$alpha) * $alpha;
	}
}




















