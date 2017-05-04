<?php

include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
include_once "GraphCutInitializer.class.php";
class GrabCut {
	// initialise l'entrée de l'algorithme grabcut
	// entrées : image résultant du calcul de la saillance
	//           matrice represantant le trimap initial
	// sortie : matrice du trimap    "B"  "F" et "U";
	//          matrice des valeurs alpha
	//          matrice des valeurs de k 
	//          le vecteur teta  de array "k" "alpha"
	public static function computeGrabcut($GD_saliency_image, $matriceTrimap,$image_name,$ext){
		$zMatrix  = GrabCut::getZMatrix($GD_saliency_image);
		$alphaMatrix = GrabCut::initializeAlphaMatrix($matriceTrimap);
		$kMatrix = GrabCut::initializeKMatrix($alphaMatrix, Constants::getK_GmmClustersNumbers());
		$Teta = GrabCut::getTeTaVector($alphaMatrix, $kMatrix, $zMatrix, Constants::getK_GmmClustersNumbers());
		$tetaMeans = $Teta["tetaMeans"];
		$tetaSigma = $Teta["tetaSigma"];
		$tetaN = $Teta["tetaN"];
		$N =  $Teta["N"];
		
		
		
		
		for ($iteration = 0; $iteration< Constants::getNGrabcutIterations() ; $iteration++){
			$tetaMeans = $Teta["tetaMeans"];
			$tetaSigma = $Teta["tetaSigma"];
			$tetaN = $Teta["tetaN"];
			$N =  $Teta["N"];
			$K = Constants::getK_GmmClustersNumbers();
			$kMatrix = GrabCut::kReassignement($kMatrix, $alphaMatrix, $zMatrix, $tetaMeans, $tetaSigma, $tetaN,$N, $K);	
			$Teta = GrabCut::getTeTaVector($alphaMatrix, $kMatrix, $zMatrix, $K );
			$tetaMeans = $Teta["tetaMeans"];
			$tetaSigma = $Teta["tetaSigma"];
			$tetaN = $Teta["tetaN"];
			$N =  $Teta["N"];
		
			// TEST DES VALEURS DE TETA
			$w_test= count( $tetaMeans[1] );
			$h_test = count ( $tetaMeans );
			echo "\n**** teta Means";
			for($y = 0; $y < $h_test; $y ++) {
				echo "\n";
				for($x = 0; $x < $w_test; $x ++) {
					echo substr($tetaMeans[$y][$x]."     ",0,10 );
					echo "  ; ";
				}
			}
			echo "\n****teta Sigma";
			for($y = 0; $y < $h_test; $y ++) {
				echo "\n";
				for($x = 0; $x < $w_test; $x ++) {
					echo substr($tetaSigma[$y][$x]."        ",0,10 );
					echo "  ; ";
				}
			}
			echo "\n *** tetaN ";
			
			for($y = 0; $y < $h_test; $y ++) {
				echo "\n";
				for($x = 0; $x < $w_test; $x ++) {
			
					echo substr($tetaN[$y][$x]."          ",0,10 );
					echo "  ; ";
				}
			}
			
			$graphcutIni = new GraphCutInitializer($kMatrix, $zMatrix, $alphaMatrix, $matriceTrimap, $tetaMeans, $tetaSigma,
					 $GD_saliency_image, $tetaN, $N, $K,$image_name,$ext);
			
			
			$graphcutResult = $graphcutIni->computeGraphCut();     
			$matriceGraphcut = $graphcutResult["matriceGraphcut"];
			
			
			//return -1;
			$matriceTrimap1 = GrabCut::applyGraphcutChangesOnAlphaTrimap($matriceGraphcut,$alphaMatrix,$matriceTrimap);	
			for($i=0;$i<count($kMatrix);$i++){
				for($j=0;$j<count($kMatrix[0]);$j++){
					
					if($matriceTrimap1[$i][$j] == "F"){
						 //echo "\n effectivement modifié";
					}
				}
			}
			$matriceTrimap= GrabCut::dilate_erode($matriceTrimap1,$image_name,$ext);
			
			$alphaMatrix = GrabCut::initializeAlphaMatrix($matriceTrimap);
			
			
			// pour le test de la dilatation 
			
			$image_dilatation_erosion  = GrabCut::testDilatationErosionImage($matriceTrimap,$image_name,$ext);
			Database_Manager::produce_image($image_dilatation_erosion,Constants::getGraphCutImageDir(),$image_name."_grabcut_iteration_$iteration", $ext );
			
		}
		
		
		return $matriceTrimap;
		
		
	}
	public static function testImage($matriceTrimap){
		$w = count  ( $matriceTrimap[0] );
		$h = count ( $matriceTrimap );
		$dilatedErodedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				//if($this->graphcut->getTerminal($y*$w + $x) == Terminal::FOREGROUND){
				if($matriceTrimap[$y][$x]=="F"){
		
					$color = imagecolorallocate($dilatedErodedImage,255,255,255);
					imagesetpixel($dilatedErodedImage, $x, $y, $color);
		
				}else{
					$color = imagecolorallocate($dilatedErodedImage,0,0,0);
					imagesetpixel($dilatedErodedImage, $x, $y, $color);
				}
			}
		}
		return $dilatedErodedImage;	
	}
	public static function testDilatationErosionImage($matriceTrimap){
		$w = count  ( $matriceTrimap[0] );
		$h = count ( $matriceTrimap );
		$dilatedErodedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				//if($this->graphcut->getTerminal($y*$w + $x) == Terminal::FOREGROUND){
				if($matriceTrimap[$y][$x]=="F"){
		
					$color = imagecolorallocate($dilatedErodedImage,255,255,255);
					imagesetpixel($dilatedErodedImage, $x, $y, $color);
		
				}elseif($matriceTrimap[$y][$x]=="U"){
					
					$color = imagecolorallocate($dilatedErodedImage,115,25,200);
					imagesetpixel($dilatedErodedImage, $x, $y, $color);
				}else{
					$color = imagecolorallocate($dilatedErodedImage,0,0,0);
					imagesetpixel($dilatedErodedImage, $x, $y, $color);
				}
			}
		}
		return $dilatedErodedImage;	
	}
	public static function applyGraphcutChangesOnAlphaTrimap($matriceGraphcut,$alphaMatrix,$MatriceTrimap){
		$h = count($MatriceTrimap); $w = count($alphaMatrix[0]);
		
		for($i=0;$i<$h;$i++){
			for($j=0;$j<$w;$j++){
				if($alphaMatrix[$i][$j] == 1 && $matriceGraphcut[$i][$j]==1){
					$MatriceTrimap[$i][$j] = "F";  //echo "\n trouvé";
				}
			}	
		}
		return $MatriceTrimap;
	}
	public static function dilate_erode($matrice_trimap,$image_name,$ext){
		// procédure consistant 
		//         1 - à dilater les pixels "F" du trimap pour ne retenir que les backgrounds sûr "BS"
		// 		    2- à éroder les pixels "F" du même trimap pour ne retenir que les foregrounds sûr  "FS"
		//         3- à transformer le reste en "U" puis  les "FS" et les "BS" en "F" et "B" respectivement 
		//          
		
		$temp_trimapMatrix= array();
		$h = count($matrice_trimap); $w = count($matrice_trimap[0]);
		$dilatation_size = Constants::getDilationSize();
		//duplication du temp_trimapMatrix en temp_trimapMatrix_3
		
		for ($y = 0; $y< $h ; $y++){
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				$row[] = $matrice_trimap[$y][$x];
			}
			$temp_trimapMatrix[]= $row;
		}
		// test avant  la dilatation
		$test_dilatation = GrabCut::testImage($matrice_trimap);
		Database_Manager::produce_image($test_dilatation,Constants::getGraphCutImageDir(),$image_name."_MT_avant_dilatation", $ext );
		
		
		// dilatation 
		$delta_list= array();
		for ($y = 0; $y< $h ; $y++){
			for($x = 0; $x < $w; $x ++) {
				if($temp_trimapMatrix[$y][$x]=="F"){// on cherche les points étiquettés foreground.
					// puis on liste les pixels à mettre en position foreground
					for($i = max(array(0,$y-$dilatation_size)); $i < min(array($h-1,$y+$dilatation_size)); $i ++) {
						for($j = max(array(0,$x-$dilatation_size)); $j < min(array($w-1,$x+$dilatation_size)); $j ++) {
							$matrice_trimap[$i][$j] = "F";
						}
					}	
				}
			}
		}
		
		// test de la dilatation
		$test_dilatation   = GrabCut::testImage($matrice_trimap);
		Database_Manager::produce_image($test_dilatation,Constants::getGraphCutImageDir(),$image_name."_MT_dilatation", $ext );
		
		// mise à jour des Unkow
		for ($y = 0; $y< $h ; $y++){
			for($x = 0; $x < $w; $x ++) {
				if($matrice_trimap[$y][$x]=="F" && $temp_trimapMatrix[$y][$x]=="B"){
					$matrice_trimap[$y][$x] = "U";
				}
			}
		}
		return $matrice_trimap;
		
	}
		
	public static function kReassignement ($kMatrix,$alphaMatrix,$zMatrix, $tetaMeans,$tetaSigma,$tetaN,$N,$K){
		$h = count($alphaMatrix);
		$w = count($alphaMatrix[0]);
		for ($y = 0; $y< $h ; $y++){
			for($x = 0; $x < $w; $x ++) {
				$kmin = $kMatrix[$y][$x];
				$z = $zMatrix[$y][$x];
				$sigma = $tetaSigma[$alphaMatrix[$y][$x]][$kmin];
				$means = $tetaMeans[$alphaMatrix[$y][$x]][$kmin];
				$poids= $tetaN[$alphaMatrix[$y][$x]][$kmin] / $N;
				$Dmin = GrabCut::GaussianDensity($z, $sigma, $means, $poids);
				for($k = 0; $k < $K; $k ++) {
					$sigma = $tetaSigma[$alphaMatrix[$y][$x]][$k];
					$means = $tetaMeans[$alphaMatrix[$y][$x]][$k];
					$poids= $tetaN[$alphaMatrix[$y][$x]][$k] / $N;
					$D = GrabCut::GaussianDensity($z, $sigma, $means, $poids);
					if($D<$Dmin){
						$kmin = $k;
						$Dmin = $D;
					}
					
				}
				$kMatrix[$y][$x]= $kmin;
				
			}
		}
		return $kMatrix;
	}
	public static function GaussianDensity($z,$sigma,$means,$poids){
		if($sigma==0 || $poids==0) return 0.5;
		return -log10($poids)+0.5*log10($sigma)+0.5*pow(($z-$means),2)/$sigma;
	}
	// le résulat sera composé de
	// - une matrice tetaMeans de taille 2 * K qui à chaque position alphan,k représentera la valeur de la moyenne
	// - une matrice tetaSigma pareille pour la variance
	// - une matrice tetaN pareille mais contenant les effectifs
	// - l'effectif total N utilisé pour le calcul des poids
	
	public static function getTeTaVector($alphaMatrix,$kMatrix,$zMatrix,$K){
		/// initialisation des matrice
		$h = count($alphaMatrix);
		$w = count($alphaMatrix[0]);
		$tetaMeans = array();
		$tetaSigma = array();
		$tetaN = array();
		for ($alpha = 0; $alpha< 2 ; $alpha++){
			$rowTetaMeans = array();
			$rowTetaSigma = array();
			$rowTetaN = array();
			for($k = 0; $k < $K; $k++) {
				$rowTetaMeans[] = 0;
				$rowTetaN[] = 0;
				$rowTetaSigma[] = 0;
			}
			$tetaSigma[] = $rowTetaSigma;
			$tetaN[] = $rowTetaN;
			$tetaMeans[] = $rowTetaMeans;
		} 
		
		
		// calcul de initial de $tetaN et de $tetaMeans (valeurs sans division)
		for ($y = 0; $y< $h ; $y++){
			for($x = 0; $x < $w; $x ++) {
				$tetaMeans[$alphaMatrix[$y][$x]][$kMatrix[$y][$x]] += $zMatrix[$y][$x];
				$tetaN[$alphaMatrix[$y][$x]][$kMatrix[$y][$x]] +=1 ;
			}
		}
	
		
		// calcul des valeurs finales de $tetaMeans
		for ($alpha = 0; $alpha< 2 ; $alpha++){
			for($k = 0; $k < $K; $k ++) {
				if ($tetaN[$alpha][$k] == 0){
					$tetaMeans[$alpha][$k] = 0;
				}else{
					$tetaMeans[$alpha][$k] = floor($tetaMeans[$alpha][$k]/$tetaN[$alpha][$k]);
				}
				
			}
		}
	
		// calcul des valeurs de tetaSigma sans division
		for ($y = 0; $y< $h ; $y++){
			for($x = 0; $x < $w; $x ++) {
				$tetaSigma[$alphaMatrix[$y][$x]][$kMatrix[$y][$x]] += 
				pow($zMatrix[$y][$x] - $tetaMeans[$alphaMatrix[$y][$x]][$kMatrix[$y][$x]],2);		
		
			}
		}
	
		// calcul des valeurs finales de sigma 
		for ($alpha = 0; $alpha< 2 ; $alpha++){
			for($k = 0; $k < $K; $k ++) {
				if ($tetaN[$alpha][$k] == 0){
					$tetaSigma[$alpha][$k] = 0;
				}else{
					$tetaSigma[$alpha][$k] /= $tetaN[$alpha][$k];
				}
		
			}
		}
		
		// calcul de l'effectif total
		$N = 0;
		for ($alpha = 0; $alpha< 2 ; $alpha++){
			for($k = 0; $k < $K; $k ++) {
				$N = $N + $tetaN[$alpha][$k];
			}
		}
		
		return array("tetaN"=>$tetaN,"tetaMeans"=>$tetaMeans,"tetaSigma"=>$tetaSigma,"N"=>$N);
		
		
		
	}
	public static function getZMatrix($imageGD){
		$w = imagesx ( $imageGD );
		$h = imagesy ( $imageGD );
		$zMatrix  = array();
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				
				$pixelrgb = imagecolorat($imageGD,$x,$y);
				$cols = imagecolorsforindex($imageGD, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
			
				$row[] = floor(0.2125* $r + 0.7154 *$g + 0.0721* $b);
						
				
			}
			$zMatrix [] = $row;
		}
		return $zMatrix;
	}
	public static function initializeKMatrix($alphaMatrix,$K){
		$n = $K; $n_total = 0;
		//obtention du vecteur alphaOneList des éléments de la valeur 1
		$h = count($alphaMatrix);
		$w = count( $alphaMatrix[0] );
		$alphaOneList  = array();
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				if ($alphaMatrix[$y][$x] == 1){
					$alphaOneList[]= array("i"=>$y,"j"=>$x);
					$n_total++;
				}		
			}
		}
		
		
		// application de l'initialisation par kmeans
		$N = count($alphaOneList);
		$cluster_centers= array();
		$alphaOneListPrim = array();
		$affectation = array();
		// initialisation de la liste manipulable
		foreach ($alphaOneList as $alphaOne){
			$alphaOneListPrim [] = array("i"=>$alphaOne["i"],"j"=>$alphaOne["j"]);
		}
		// initialisation des centres des clusters
		for($i = 0; $i < $n; $i ++) {
			$j= rand(0,count($alphaOneListPrim)-1);
			$cluster_centers[] = array("i"=>$alphaOneListPrim[$j]["i"],"j"=>$alphaOneListPrim[$j]["j"]);
			array_splice($alphaOneListPrim,$j,1);
		}
		
		// première affectation avec les centres les plus proches
		for($i = 0; $i < $N; $i ++){
			$jmin = 0;
			$distMin = Grabcut::distanceBetween($alphaOneList[$i],$cluster_centers[0]);
			for($j = 1; $j < $n; $j ++){
				$dist_act = GrabCut::distanceBetween($alphaOneList[$i],$cluster_centers[$j]);
				if($dist_act<$distMin){
					$distMin = $dist_act;
					$jmin = $j;
				}
			}
			$affectation[$i]=$jmin;// car les clusteurs sont numérotés de 0 à K-1
		}
		// initialisation de la matrice K finale
		$KMatrix =array();
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				$row[]=-1;
			}
			$KMatrix[] = $row;
		}
		// utilisationd des affectation pour placer les valeurs de k à la Kmatrix
		for($j = 0; $j < $N; $j ++){
			$KMatrix[$alphaOneList[$j]["i"]][$alphaOneList[$j]["j"]] = $affectation[$j];
		}
		////////////////////// meme opération pour les valeurs de la matrice dont alpha est O
		$alphaZeroList  = array();
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				if ($alphaMatrix[$y][$x] == 0){
					$alphaZeroList[]= array("i"=>$y,"j"=>$x);
					$n_total++;
				}
			}
		}
		
		// application de l'initialisation par kmeans
		$N = count($alphaZeroList);
		$cluster_centers= array();
		$alphaZeroListPrim = array();
		$affectation = array();
		// initialisation de la liste manipulable
		foreach ($alphaZeroList as $alphaZero){
			$alphaZeroListPrim [] = array("i"=>$alphaZero["i"],"j"=>$alphaZero["j"]);
		}
		// initialisation des centres des clusters
		for($i = 0; $i < $n; $i++) {
			if(count($alphaZeroListPrim) > 0){
			$j= rand(0,count($alphaZeroListPrim)-1);
			$cluster_centers[] = array("i"=>$alphaZeroListPrim[$j]["i"],"j"=>$alphaZeroListPrim[$j]["j"]);
			array_splice($alphaZeroListPrim,$j,1);}
		}
		
	
		
		// première affectation avec les centres les plus proches
		for($i = 0; $i < $N; $i++){
			$jmin = 0;
			$distMin = Grabcut::distanceBetween($alphaZeroList[$i],$cluster_centers[0]);
			for($j = 1; $j < $n; $j ++){
				$dist_act = GrabCut::distanceBetween($alphaZeroList[$i],$cluster_centers[$j]);
				if($dist_act<$distMin){
					$distMin = $dist_act;
					$jmin = $j;
				}
			}
			$affectation[$i]=$jmin;// car les clusteurs sont numérotés de 0 à K-1
		}
		//plus d'initialisation de la matrice K finale
		// utilisationd des affectation pour placer les valeurs de k à la Kmatrix
		for($j = 0; $j < $N; $j ++){
			$KMatrix[$alphaZeroList[$j]["i"]][$alphaZeroList[$j]["j"]] = $affectation[$j];
		}	
		//test valeurs de kmatrix
		/*$w_test = count( $KMatrix[1] );
		$h_test = count ( $KMatrix );
		echo "****" ; var_dump($alphaZeroList[0]);
		for($y = 0; $y < $h_test; $y ++) {
			echo "\n";
			for($x = 0; $x < $w_test; $x ++) {
				
				echo substr ( $KMatrix [$y] [$x] . "     ", 0, 5);
			}
		}*/
		return $KMatrix;
		
	}
	public static function distanceBetween($coordonnee1,$coordonnee2){
		return sqrt(pow($coordonnee1["i"]-$coordonnee2["i"],2)+pow($coordonnee1["j"]-$coordonnee2["j"],2));
	}
	public static function initializeAlphaMatrix($matriceTrimap){
		$h = count($matriceTrimap);
		$w = count( $matriceTrimap[0] );
		$alphaMatrix  = array();
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				if ($matriceTrimap[$y][$x]=="B" || $matriceTrimap =="F"){
					$row[] = 0;
				}else{
					$row[]= 1;
				}
	
			}
			$alphaMatrix [] = $row;
		}
		return $alphaMatrix;
	}
	
	
	
	
	
	
		
	
	
	
}


























































