<?php

include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
include_once "Grabcut.class.php";

class SaliencyCut {
	
	var $connexComponantMatrix;
	var $connexComponantSize;
	var $GdBinaryImg;
	var $connexComponantIndex;
	var $maxConnexComponantMatrix;
	
	var $gdImage;
	var $gdSalientImageHC;
	var $image_name;
	var $ext;
	function __construct($actual_img,$final_salient_image,$image_name,$ext){
		$this->gdImage = $actual_img;
		$this->gdSalientImageHC = $final_salient_image;
		$this->image_name = $image_name;
		$this->ext = $ext;
	}
	// opère l'initialisation de l'image issue du calcul de la saillence via la méthode HC
	// paramètres :
	// - image gd provenant du calcule de la saillance (pas binarisée)
	// sorties:  
	// - une matrice de même taille que l'image décrivant pour chaque position i,j la 
	// région dans laquelle elle appartient  "B", "F" ou "U"
	
	public function computeImage(){
	   //CALCUL DE LA SAILLENCE À UTILISER 
		//$this->GdBinaryImg;
	    $threshold  =  $this->getThreshold();
		
		//$this->binarize(Constants::getThresholdTrimapInitialization());
	    $this->binarize($threshold);
	    
		/// test image de saillance améliorée et binarisée
		Database_Manager::produce_image($this->GdBinaryImg,Constants::getSalientImagesDir(), $this->image_name."_binarize",$this->ext );
		//return;
		// CALCUL DES COMPOSANTS DES CONNEXES 
		$this->ConnexComposantMatrix_Sizes($this->GdBinaryImg);
	
		
		//$maxConnexionComponantMatrix = SaliencyCut::GetBiggerConnexComposant($connexionComponantMatrix, $connexionComponantSize);
		
		
		
		$this->SetBiggerConnexComposant();
		/// test matrice max composant connexes
		$image_bigger_region = $this->getImageFromBiggerConnexRegion();
		Database_Manager::produce_image($image_bigger_region,Constants::getSalientImagesDir(), $this->image_name."_binarize_bigger_region",$this->ext );
		
		
		
		
		
		$TrimapInitialisation = $this->trimapInitilisation();
		
		//test initialisation du trimap
		/*$w = count  ( $TrimapInitialisation[0] );
		$h = count ( $TrimapInitialisation );
		for($y = 0; $y < $h; $y ++) {
			echo "\n";
			for($x = 0; $x < $w; $x ++) {
		
				echo substr($TrimapInitialisation[$y][$x]."    ",0, 3);
			}
		}
		return 0;
		
		*/
				
		$matriceTrimap = GrabCut::computeGrabcut($this->gdImage, $TrimapInitialisation,$this->image_name,$this->ext);
		
		
		$binarized_shape_image = $this->getBinaryImageFromTrimap($matriceTrimap);
		/// test image de saillance améliorée et binarisée
		Database_Manager::produce_image($binarized_shape_image,Constants::getSalientImagesDir(), $this->image_name."_binarize_shape",$this->ext );
		
		return $binarized_shape_image;
		
		
	}
	public function getThreshold(){
		$w = imagesx ( $this->gdSalientImageHC);
		$h = imagesy ( $this->gdSalientImageHC );
		$histogramme  = array();

		for($y = 0; $y < 255; $y ++) {
			$histogramme[] = 0;
		}
		for($y = 1; $y < $h-1; $y ++) {
			for($x = 1; $x < $w-1; $x ++) {
				$pixelrgb = imagecolorat($this->gdSalientImageHC,$x,$y);
				$cols = imagecolorsforindex($this->gdSalientImageHC, $pixelrgb);
				$r = ($cols['red']);
				$histogramme[$r]	= 	$histogramme[$r]+1;	
			}
		}
		
		//calcul du niveau seuil
		$niveau_seuill = 0;
		$som = array_sum($histogramme);
		for($niveau_seuil = 1; $niveau_seuil < 255; $niveau_seuil ++) {
			$histogramme[$niveau_seuil] = $histogramme[$niveau_seuil]+$histogramme[$niveau_seuil-1] ;
			if ($histogramme[$niveau_seuil]>(Constants::getSeuilSaliency()*$som)){
				$niveau_seuill = $niveau_seuil;
				break;
			}
		}
		echo "\n \n \n --- niveau seuil".$niveau_seuill;
		echo "\n \n  \n -- histogramme";
		var_dump($histogramme);
		return $niveau_seuill;
	}
	public function getImageFromBiggerConnexRegion(){
		$w = count  ( $this->maxConnexComponantMatrix[0] );
		$h = count ( $this->maxConnexComponantMatrix );
		$binarizedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				if($this->maxConnexComponantMatrix[$y][$x] == 1){
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
	public function getBinaryImageFromTrimap($matriceTrimap){
		$h = count ( $matriceTrimap );
		$w = count ( $matriceTrimap[0] );
		$binarizedImage = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				if($matriceTrimap[$y][$x]=="F"){
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
	// initialise le trimap en prenant en paramètre la matrice de O et 1 selon la plus grande région connexe binarisé
	public function trimapInitilisation(){
		$h = count($this->maxConnexComponantMatrix);
		$w = count( $this->maxConnexComponantMatrix[0] );
		$TrimapMatrix = array();
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				if ($this->maxConnexComponantMatrix[$y][$x]==1){
					$row[] = "U";
				}else{
					$row[]= "B";
				}
	
			}
			$TrimapMatrix [] = $row;
		}
		return $TrimapMatrix;
	}
	// calcul de la matrice de plus grande composante connexe O pour le reste 1 pour celle ci
	// prend en paramètre la fonction suivante
	public function SetBiggerConnexComposant(){
		$max_index = array_search(max($this->connexComponantSize), $this->connexComponantSize);
		echo "\nmax: ".$max_index;
		$h = count($this->connexComponantMatrix);
		$w = count( $this->connexComponantMatrix[0] );
		$this->maxConnexComponantMatrix = array();
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				if ($this->connexComponantMatrix[$y][$x]==$max_index){
					$row[] = 1;
				}else{
					$row[]= 0;
				}
				
			}
			$this->maxConnexComponantMatrix [] = $row;
		}
		
	}
	
	//calcul de la matrice de composantes  connexes sur une image binarisée
	public  function ConnexComposantMatrix_Sizes(){
		
		$w = imagesx ( $this->GdBinaryImg );
		$h = imagesy ( $this->GdBinaryImg );
		$this->connexComponantMatrix = array();
		$this->connexComponantSize = array(); $this->connexComponantSize[]=0;
		$this->connexComponantIndex = 0;
		
		//  initialisation de la matrice
		for($y = 0; $y < $h; $y ++) {
			$row = array();
			for($x = 0; $x < $w; $x ++) {
				$row[]= 0;
			}
			$this->connexComponantMatrix[] = $row;
		}
		// construction de la matrice des composantes connexes
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				$pixelrgb = imagecolorat($this->GdBinaryImg,$x,$y);
				$cols = imagecolorsforindex($this->GdBinaryImg, $pixelrgb);
				$r = ($cols['red']);
				
				
				if ($r == 255 && $this->connexComponantMatrix[$y][$x]==0){
					$this->connexComponantIndex += 1;
					$this->connexComponantSize[] = $this->constructConnexComponant($y,$x,1);	
					echo "\n  ****fin récursivité***appel de la boucle";
							
				}
			}
		}
		for($y = 0; $y < count($this->connexComponantSize); $y ++) {
			echo $this->connexComponantSize[$y]." +++ ";
		}
			
	}
	public  function constructConnexComponant ($i,$j,$size_connexComponant){
		$w = imagesx ( $this->GdBinaryImg );
		$h = imagesy ( $this->GdBinaryImg );	
		$actual_list = array();
		$sum_child = 0;
		
		array_unshift($actual_list, array("i"=>$i,"j"=>$j));
		$this->connexComponantMatrix[$i][$j] = $this->connexComponantIndex;
		
		
		while (count($actual_list)>0){
			//echo "\n -------------- composant principal    :".$i." - ".$j;
			//echo "\n----- son index   :".$this->connexComponantIndex;
			$i = $actual_list[0]["i"];
			$j = $actual_list[0]["j"];
			array_shift($actual_list);
			for($y = max(array(0,$i-1)) ; $y <= min(array($h-1,$i+1)); $y ++) {
				for($x = max(array($j-1,0)); $x <= min(array($j+1,$w-1)); $x ++) {
					$pixelrgb = imagecolorat($this->GdBinaryImg,$x,$y);
					$cols = imagecolorsforindex($this->GdBinaryImg, $pixelrgb);
					$r = ($cols['red']);
					//echo "autour de lui ". $y ."++++".$x;
					//echo "\n valeur de la couleur :". $r;
					//echo "\n --------------son index: ".$this->connexComponantMatrix[$y][$x];
					if ($r == 255 && $this->connexComponantMatrix[$y][$x] == 0){
						//echo "\n  -------- donc itération et ajout dans la liste";
						$this->connexComponantMatrix[$y][$x] = $this->connexComponantIndex;
						array_unshift($actual_list, array("i"=>$y,"j"=>$x));
						$sum_child = $sum_child + 1;
					}
				}
			}
			
		}
		
		//echo "\n-----connexComponantIndex   :".$this->connexComponantIndex;
		//echo "\n ----connexComponantSize    :".($size_connexComponant+$sum_child);
		//echo "\n --------------componant    :".$i." - ".$j;
	
		//return array("size"=>$size_connexComponant+$sum_child,"connexComponantMatrix"=>$connexComponantMatrix);
		return $sum_child;
	}
	/// construction du graphe de connexion d'un élément du graPHE;
	// retourne la taille du graphe construit et la nouvelle matrice de connexion
	/*public  function constructConnexComponant ($i,$j,$size_connexComponant){
		$w = imagesx ( $this->GdBinaryImg );
		$h = imagesy ( $this->GdBinaryImg );
		$this->connexComponantMatrix[$i][$j] = $this->connexComponantIndex;
		
		echo "\n -------------- composant principal    :".$i." - ".$j;
		echo "\n----- son index   :".$this->connexComponantIndex;
		
		
		$sum_child = 0;
		for($y = max(array(0,$i-1)) ; $y <= min(array($h-1,$i+1)); $y ++) {
			for($x = max(array($j-1,0)); $x <= min(array($j+1,$w-1)); $x ++) {
				$pixelrgb = imagecolorat($this->GdBinaryImg,$x,$y);
				$cols = imagecolorsforindex($this->GdBinaryImg, $pixelrgb);
				$r = ($cols['red']);	
				echo "autour de lui ". $y ."++++".$x;
				echo "\n valeur de la couleur :". $r;
				echo "\n --------------son index: ".$this->connexComponantMatrix[$y][$x];
				if ($r == 255 && ($this->connexComponantMatrix[$y][$x]) == 0){	
					echo "\n  -------- donc récursivité";		
					$sum_child = $sum_child + $this->constructConnexComponant ($y,$x,$size_connexComponant);	
				}
			}
		}
		//echo "\n-----connexComponantIndex   :".$this->connexComponantIndex;
		//echo "\n ----connexComponantSize    :".($size_connexComponant+$sum_child);
		//echo "\n --------------componant    :".$i." - ".$j;
		
		//return array("size"=>$size_connexComponant+$sum_child,"connexComponantMatrix"=>$connexComponantMatrix);
		return $size_connexComponant+$sum_child;
	}*/
	
	/// binarisation de l'image avec un threshold
	public function binarize($treshold){
		$w = imagesx ( $this->gdSalientImageHC );
		$h = imagesy ( $this->gdSalientImageHC );
		$this->GdBinaryImg = imagecreatetruecolor($w, $h);
		// echo "taille de l'image : ".$w ."-----".$h;
		for($y = 0; $y < $h; $y ++) {
			for($x = 0; $x < $w; $x ++) {
				
				
				$pixelrgb = imagecolorat($this->gdSalientImageHC,$x,$y);
				$cols = imagecolorsforindex($this->gdSalientImageHC, $pixelrgb);
				$r = ($cols['red']);
				$g = ($cols['green']);
				$b = ($cols['blue']);
				
				
				if($r>=$treshold){
					$color = imagecolorallocate($this->GdBinaryImg,255,255,255);
					imagesetpixel($this->GdBinaryImg, $x, $y, $color);
					
				}else{
					$color = imagecolorallocate($this->GdBinaryImg,0,0,0);
					imagesetpixel($this->GdBinaryImg, $x, $y, $color);
				}	
			}
		}
		
	}
	
	
	
	
	
}


























































