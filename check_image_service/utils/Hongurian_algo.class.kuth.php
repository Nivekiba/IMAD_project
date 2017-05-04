<?php
include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
// / une position est ici une array de deux valeurs. avec les clés "i" et "j"ù
// / la matrice d'entrée est un array de n array (de chiffre) de longueur n

// / dans la nouvelle version, la matrice manipulée par l'algorithme est différente de celle entrée
// elle est constitué d'objet de classe elementMatriceHongroise
class ELementMatriceHongroise {
	var $value; // chiffre
	var $status; // simple,encadree, barree, s'il s'agit d'un zero.
	public function __construct($value) {
		$this->value = $value;
		$this->status = "simple";
	}
	function getValue() {
		return $this->value;
	}
	function setValue($value) {
		$this->value = $value;
	}
	function getStatus() {
		return $this->status;
	}
	function setStatus($status) {
		$this->status = $status;
	}
}
class Hongurian_algo {
	public static function getOptimalPermutationIt($matrice_entree) {
		$n = count ( $matrice_entree );
		$matrice_simple = Hongurian_algo::initialisationMatrice ( $matrice_entree );
	   $matrice_hongroise_simplifiee = Hongurian_algo::transformToHungurianMatrice ( $matrice_simple );
		//var_dump($matrice_hongroise_simplifiee);
		
		$matriceHongroiseZeroBarree = Hongurian_algo::BarrerEncadrerZero ( $matrice_hongroise_simplifiee );
																														$test = 0;
		
	    $oldMatriceHongroise = Hongurian_algo::sauvegarde_matrice($matriceHongroiseZeroBarree);
		while ( Hongurian_algo::nombrePositionZeroEncadree($matriceHongroiseZeroBarree) != $n ) { 
			gc_collect_cycles();
			$oldMatriceHongroise = Hongurian_algo::sauvegarde_matrice($matriceHongroiseZeroBarree);
			
			$barrageLignesColonnes = Hongurian_algo::barrerLignesColonnes ($matriceHongroiseZeroBarree );
			$ligneBarree = $barrageLignesColonnes ["ligneBarree"];
			$ligneNonBarree = $barrageLignesColonnes ["ligneNonBarree"];
			$colonneBarree = $barrageLignesColonnes ["colonneBarree"];
			$colonneNonBarree = $barrageLignesColonnes ["colonneNonBarree"];
			//echo "\n nombre de positions encadree \n"; echo  Hongurian_algo::nombrePositionZeroEncadree($matriceHongroiseZeroBarree);
			
			
			
			 //echo "\n lignes barrées \n "; var_dump($ligneBarree); echo "\n lignes non barrées \n "; var_dump($ligneNonBarree); echo "\n colonnes barrées \n "; var_dump($colonneBarree); echo "\n colonnes non barrées \n "; var_dump($colonneNonBarree);
			
			$matriceHongroiseModifiee = Hongurian_algo::modificationMatrice ( $matriceHongroiseZeroBarree, $ligneBarree, $ligneNonBarree, $colonneBarree, $colonneNonBarree );
			
			// calcul des positions simples
			
			//echo "\n matrice ***********************\n";
			//var_dump($matriceHongroiseModifiee);
			
			
			$matriceHongroiseModifiee = Hongurian_algo::resetElementStatus($matriceHongroiseModifiee, "simple");
			
		
			
			$matriceHongroiseZeroBarree = Hongurian_algo::BarrerEncadrerZero ( $matriceHongroiseModifiee);        $test++;       if($test==1000000)  break;
			
			/*if (Hongurian_algo::haveSameSolutionMatriceHongroise($matriceHongroiseZeroBarree, $oldMatriceHongroise)==1){
			 echo "\n ------------------------************même solution ".$test;break;}*/
			
			
		}
		echo "\n Garbage Colletor enabled (défaut) : " . (gc_enabled() ? 'OUI' : 'NON') . "\n";
		$sortieBoucle=Hongurian_algo::haveSameSolutionMatriceHongroise($matriceHongroiseZeroBarree, $oldMatriceHongroise);
		echo "\n matching : ".$sortieBoucle;
		if($sortieBoucle==1){
			
		}
		return Hongurian_algo::extractPermutations($matriceHongroiseZeroBarree);
	}
	
	public static function sauvegarde_matrice($matriceHongroise){
		$n = count($matriceHongroise);
		$result =array();
		for($i = 0; $i < $n; $i++) {
			$ro = array();
			for($j = 0; $j < $n; $j++) {
				$elt = new  ELementMatriceHongroise($matriceHongroise[$i][$j]->getValue());
				$elt->setStatus($matriceHongroise[$i][$j]->getStatus());
				$ro[] =  $elt;
			}
			$result[] = $ro;
		}
		return $result;
	}
	
	public static function haveSameSolutionMatriceHongroise($matrice1,$matrice2){
		$result = 1;
		$n = count($matrice1);
		for($i = 0; $i < $n; $i++) {
			for($j = 0; $j < $n; $j++) {
				if($matrice1[$i][$j]->getStatus()=="encadree" &&
					$matrice2[$i][$j]->getStatus()!="encadree"){				
						return 0;			
				}
					
			}
		}
		return $result;
		
		
	}
	
	public static function areSamePermutationList($permutationList1,$permutationList2){
		$result = true;
		if(count($permutationList1)==count($permutationList1)){
			$n = count($permutationList1);
			for($i = 0; $i < $n; $i++) {
				$actual_in_array2 = 0;
				$actual_permutation = $permutationList1[$i];
				if(!Hongurian_algo::isInPermutationList($actual_permutation, $permutationList2)){
					$result = false; break;
				}			
			}
		}
		return $result;
	}
	public static function isInPermutationList($permutation,$permutationList){
		$result = false;
		$n = count($permutationList);
		foreach ($permutationList as $p){
			
				if ($p["i"]==$permutation["i"] && $p["j"]==$permutation["j"]){
					$result = true;break;
				}
		}
		return $result;
	}
	public static function modificationMatrice($matrice, $ligneBarree, $ligneNonBarree, $colonneBarree, $colonneNonBarree) {
		// recherche du plus petit élément
		$min = $matrice [$ligneNonBarree [0]] [$colonneNonBarree [0]];
		foreach ( $ligneNonBarree as $a ) {
			foreach ( $colonneNonBarree as $b ) {
				if ($matrice [$a] [$b]->getValue() < $min->getValue()) {
					$min = $matrice [$a] [$b];
				}
			}
		}
		
		$minVal = $min->getValue() ;
		
		// soustraction aux valeur non barrées
		foreach ( $ligneNonBarree as $l ) {
			//echo "\n";
			foreach ( $colonneNonBarree as $c ) {
			//	echo "ajout à la poisition".$l."--".$c."--";
			//	echo $matrice [$l] [$c] ->getValue()."--";
				
				$matrice [$l] [$c] ->setValue($matrice [$l] [$c]->getValue() - $minVal);
				
				
				//echo $matrice [$l] [$c] ->getValue();
			}
		}
		// addition aux valeurs barrées
		foreach ( $ligneBarree as $l ) {
			foreach ( $colonneBarree as $c ) {
				$matrice [$l] [$c] ->setValue($matrice [$l] [$c]->getValue()  + $minVal );
			}
		}
		return $matrice;
	}
	public static function barrerLignesColonnes($matriceHongroiseZeroBarree) {
		// marquage des lignes n'ayant pas de position encadrée
		$n = count ( $matriceHongroiseZeroBarree );
		
		$ligneBarree = array ();
		$ligneNonBarree = array ();
		$colonneBarree = array ();
		$colonneNonBarree = array ();
		
		$lignePosEncadree = Hongurian_algo::getLignePositionEncadree($matriceHongroiseZeroBarree);
		
		// echo "\n ligne contenant des encadrées \n ";
		// var_dump($lignePosEncadree);
		
		for($l = 0; $l < $n; $l ++) {
			if (! in_array ( $l, $lignePosEncadree )) {
				$ligneNonBarree [] = $l;
			}
		}
		// echo "\n lignes non barrées \n ";
		// var_dump($ligneNonBarree);
		
		$marquee = true;
		$test = 0;
		while ( $marquee ) {
			// marquage des colonnes dont les éléments des lignes précédentes ont des positions barréee
			$marquee = false;
			// echo "\n positions barrées \n ";
			// var_dump($PosBarree);
			foreach ( $ligneNonBarree as $l ) {
				for($j = 1; $j < $n; $j ++) {
					
					if ($matriceHongroiseZeroBarree [$l] [$j]->getValue() == 0 &&
							$matriceHongroiseZeroBarree [$l] [$j]->getStatus() == "barree"){
						
						if (! in_array ( $j, $colonneBarree )) {
							$colonneBarree [] = $j;
							$marquee = true;
						}
					}
				}
			}
			
			// echo "\n colonne barrées \n ";
			// var_dump($colonneBarree);
			// Marquage des lignes dont les éléments des colonnes précédentes sont encadrées
			// echo "\n positions encadree \n ";
			// var_dump($posEncadree);
			foreach ( $colonneBarree as $c ) {
				for($i = 0; $i < $n; $i ++) {
					
					if ($matriceHongroiseZeroBarree [$i] [$c]->getValue() == 0 && 
							$matriceHongroiseZeroBarree [$i] [$c]->getStatus() == "encadree"
					 ) {
						
						if (! in_array ( $i, $ligneNonBarree )) {
							$ligneNonBarree [] = $i;
							$marquee = true;
						}
					}
				}
				// echo "\n ligne non barrées \n ";
				// var_dump($ligneNonBarree);
			} // echo "\n marquee ".$marquee;
				  // echo "\n test ------------- ".$test; // $test++; if ($test == 4) break;
		}
		// calcul des lignes Barrées
		for($i = 0; $i < $n; $i ++) {
			if (! in_array ( $i, $ligneNonBarree )) {
				$ligneBarree [] = $i;
			}
		}
		for($j = 0; $j < $n; $j ++) {
			if (! in_array ( $j, $colonneBarree )) {
				$colonneNonBarree [] = $j;
			}
		}
		
		// echo "\n résultat \n";
		
		return array (
				"ligneBarree" => $ligneBarree,
				"ligneNonBarree" => $ligneNonBarree,
				"colonneBarree" => $colonneBarree,
				"colonneNonBarree" => $colonneNonBarree 
		);
	}
	public static function BarrerEncadrerZero($matrice_hongroise_simplifiee) {
		$n = count ( $matrice_hongroise_simplifiee );
		$NomPosSimpleParLigne = array ();
		for($t = 0; $t < $n; $t ++) {
			$NomPosSimpleParLigne [] = 0;
		}
		// obtention de la future ligne sur laquelle un zero sera encadré
		
		// $test=0;
		$nombrePositionZeroSimple = Hongurian_algo::nombrePositionZeroSimple ( $matrice_hongroise_simplifiee );
		while ( $nombrePositionZeroSimple  > 0 ) {
			$prochaineLigne = 0;
		    $NomPosSimpleParLigne = Hongurian_algo::nombrePositionZeroSimpleParLigne($matrice_hongroise_simplifiee);
			
			//echo "\n nombres de positions simples par ligne \n ";
			 //var_dump($NomPosSimpleParLigne);
			$ligneNombreZeroSimpleMin = 0;
			// $NombreZeroSimpleMin = $NomPosSimpleParLigne[0];
			$NombreZeroSimpleMin = $n+1;
			for($t = 0; $t < $n; $t ++) {
				if ($NomPosSimpleParLigne [$t] != 0 && $NomPosSimpleParLigne [$t] < $NombreZeroSimpleMin) {
					$prochaineLigne = $t;
					$NombreZeroSimpleMin = $NomPosSimpleParLigne [$t];
				}
			}
			// echo "\n prochaine ligne \n ";
			 //echo $prochaineLigne;
			// encadrement du zero de cette ligne
			for($j = 0; $j < $n; $j ++) {
				if ($matrice_hongroise_simplifiee [$prochaineLigne] [$j]->getValue() == 0 &&
						 $matrice_hongroise_simplifiee [$prochaineLigne] [$j]->getStatus()=="simple") {
					$matrice_hongroise_simplifiee [$prochaineLigne] [$j]->setStatus("encadree");
					$colonneZero = $j;
					$nombrePositionZeroSimple = $nombrePositionZeroSimple-1;
					break;
				}
			}
			// echo "\n prochaine colonne \n ";
			// echo $colonneZero;
		
			// barrage des zero superflus par ligne et par colonnes
			for($j = $colonneZero + 1; $j < $n; $j ++) {
				if (
						$matrice_hongroise_simplifiee [$prochaineLigne] [$j]->getValue() == 0 &&
						$matrice_hongroise_simplifiee [$prochaineLigne] [$j]->getStatus()=="simple"
						 ) {
						 	$matrice_hongroise_simplifiee [$prochaineLigne] [$j]->setStatus("barree");
						 	$nombrePositionZeroSimple = $nombrePositionZeroSimple-1;
					// echo "zero retrouvé sur la même ligne sur la colonne ".$j;
				}
			}
			
			for($i = 0; $i < $n; $i ++) {
				if ($i != $prochaineLigne && 
						$matrice_hongroise_simplifiee [$i] [$colonneZero]->getValue() == 0 &&
						 $matrice_hongroise_simplifiee [$i] [$colonneZero]->getStatus()=="simple" ) {
						 	$matrice_hongroise_simplifiee [$i] [$colonneZero]->setStatus("barree");
						 	$nombrePositionZeroSimple = $nombrePositionZeroSimple-1;
					// echo "zero retrouvé sur la même colonne sur la ligne ".$i;
				}
			}
			// $test++; if($test==4) break;
		}
		
		return $matrice_hongroise_simplifiee;
		
		//
	}
	public static function initialisationMatrice($matrice) {
		$n = count ( $matrice );
		$PosSimples = array ();
		for($i = 0; $i < $n; $i ++) {
			$min = min ( $matrice [$i] );
			for($j = 0; $j < $n; $j ++) {
				$matrice [$i] [$j] = $matrice [$i] [$j] - $min;
				
			}
		}
		for($j = 0; $j < $n; $j ++) {
			$min = min ( array_column ( $matrice, $j ) );
			if ($min != 0) {
				for($i = 0; $i < $n; $i ++) {
					$matrice [$i] [$j] = $matrice [$i] [$j] - $min;
				
				}
			}
		}
		return $matrice;
	}
	
	
	
	
	
	
	public static function getLignePositionEncadree($matriceHongroiseZeroBarree){
		$lignePosEncadree = array ();
		$n = count($matriceHongroiseZeroBarree);
		for($i = 0; $i < $n; $i ++) {	
			for($j = 0; $j < $n; $j ++) {
				if( $matriceHongroiseZeroBarree [$i] [$j] ->getValue() == 0 &&
						$matriceHongroiseZeroBarree [$i] [$j] ->getStatus()=="encadree")
						{$lignePosEncadree [] = $i; break;}
			}
				
		}
		return $lignePosEncadree;
	}
	public static function resetElementStatus($matrice_hongroise,$status){
		$n = count ( $matrice_hongroise );
	
		for($i = 0; $i < $n; $i ++) {
			for($j = 0; $j < $n; $j ++) {
					$matrice_hongroise [$i] [$j] ->setStatus("simple");		
			}
		
		}
		return $matrice_hongroise;
	}
	public function nombrePositionZeroSimpleParLigne($matrice_hongroise){
		$n = count ( $matrice_hongroise );
		$NomPosSimpleParLigne  = array();
		for($t = 0; $t < $n; $t ++) {
			$NomPosSimpleParLigne [] = 0;
		}
		for($i = 0; $i < $n; $i ++) {	
			for($j = 0; $j < $n; $j ++) {
				if( $matrice_hongroise [$i] [$j] ->getValue() == 0 &&
						$matrice_hongroise [$i] [$j] ->getStatus()=="simple")
							$NomPosSimpleParLigne [$i] = $NomPosSimpleParLigne [$i]+1;
			}
				
		}
		return $NomPosSimpleParLigne;
	
	}
	public function nombrePositionZeroEncadree($matrice_hongroise){
		$n = count ( $matrice_hongroise );
		$result = 0;
		for($i = 0; $i < $n; $i ++) {
				
			for($j = 0; $j < $n; $j ++) {
				if( $matrice_hongroise [$i] [$j] ->getValue() == 0 &&
						$matrice_hongroise [$i] [$j] ->getStatus()=="encadree")
							$result = $result+1;
			}
				
		}
		return $result;
	
	}
	public function extractPermutations($matrice_hongroise){
		$n = count ( $matrice_hongroise );
		$result = array();
		for($i = 0; $i < $n; $i ++) {
		
			for($j = 0; $j < $n; $j ++) {
				if( $matrice_hongroise [$i] [$j] ->getValue() == 0 &&
						$matrice_hongroise [$i] [$j] ->getStatus()=="encadree")
							$result[]= array("i"=>$i,"j"=>$j);
			}
		
		}
		return $result;
		
	}
	public function nombrePositionZeroSimple($matrice_hongroise){
		$n = count ( $matrice_hongroise );
		$result = 0;
		for($i = 0; $i < $n; $i ++) {
			
			for($j = 0; $j < $n; $j ++) {
				if( $matrice_hongroise [$i] [$j] ->getValue() == 0 &&
		           $matrice_hongroise [$i] [$j] ->getStatus()=="simple")
					$result = $result+1;
			}
			
		}
		return $result;
		
	}
	public function transformToHungurianMatrice($matrice_simplifiee) {
		$n = count ( $matrice_simplifiee );
		$result = array ();
		for($i = 0; $i < $n; $i ++) {
			$resultRo = array ();
			for($j = 0; $j < $n; $j ++) {
				$resultRo [] = new ELementMatriceHongroise ( $matrice_simplifiee [$i] [$j] );
			}
			$result [] = $resultRo;
		}
		return $result;
	}
}































































class Hongurian_algo_old {
	public static function getOptimalPermutationRec($matrice) {
		$initialisation = Hongurian_algo::initialisationMatrice ( $matrice );
		$matrice_simplifiee = $initialisation ["matrice"];
		$posSimple = $initialisation ["posSimple"];
		$barragePositions = Hongurian_algo::BarrerEncadrerZero ( $matrice_simplifiee, $posSimple );
		
		$posEncadree = $barragePositions ["posEncadree"];
		$posBarree = $barragePositions ["posBarree"];
		if (count ( $posEncadree ) == count ( $matrice )) {
			return $posEncadree;
		} else {
			$barrageLignesColonnes = Hongurian_algo::barrerLignesColonnes ( $matrice, $posEncadree, $posBarree );
			$ligneBarree = $barrageLignesColonnes ["ligneBarree"];
			$ligneNonBarree = $barrageLignesColonnes ["ligneNonBarree"];
			$colonneBarree = $barrageLignesColonnes ["colonneBarree"];
			$colonneNonBarree = $barrageLignesColonnes ["colonneNonBarree"];
			$matrice = Hongurian_algo::modificationMatrice ( $matrice, $ligneBarree, $ligneNonBarree, $colonneBarree, $colonneNonBarree );
			return Hongurian_algo::getOptimalPermutation ( $matrice );
		}
	}
	public static function getOptimalPermutationIt($matrice_entree) {
		$n = count ( $matrice_entree );
		$initialisation = Hongurian_algo::initialisationMatrice ( $matrice_entree );
		
		$matrice_simplifiee = $initialisation ["matrice"];
		$posSimple = $initialisation ["posSimple"];
		// var_dump($matrice);
		
		$barragePositions = Hongurian_algo::BarrerEncadrerZero ( $matrice_simplifiee, $posSimple );
		$posEncadree = $barragePositions ["posEncadree"];
		$posBarree = $barragePositions ["posBarree"];
		
		while ( count ( $posEncadree ) != $n ) {
			$barrageLignesColonnes = Hongurian_algo::barrerLignesColonnes ( $matrice_simplifiee, $posEncadree, $posBarree );
			$ligneBarree = $barrageLignesColonnes ["ligneBarree"];
			$ligneNonBarree = $barrageLignesColonnes ["ligneNonBarree"];
			$colonneBarree = $barrageLignesColonnes ["colonneBarree"];
			$colonneNonBarree = $barrageLignesColonnes ["colonneNonBarree"];
			/*
			 * echo "\n lignes barrées \n "; var_dump($ligneBarree); echo "\n lignes non barrées \n "; var_dump($ligneNonBarree); echo "\n colonnes barrées \n "; var_dump($colonneBarree); echo "\n colonnes non barrées \n "; var_dump($colonneNonBarree);
			 */
			$matrice = Hongurian_algo::modificationMatrice ( $matrice_simplifiee, $ligneBarree, $ligneNonBarree, $colonneBarree, $colonneNonBarree );
			// echo "\n nouvelle matrice \n";
			// var_dump($matrice);
			// calcul des positions simples
			$posSimple = array ();
			for($i = 0; $i < $n; $i ++) {
				for($j = 0; $j < $n; $j ++) {
					// echo " ".$matrice[$i][$j];
					if ($matrice [$i] [$j] == 0) {
						$posSimple [] = array (
								"i" => $i,
								"j" => $j 
						);
					}
				}
			}
			// echo "\n matrice ***********************\n";
			// var_dump($matrice);
			// echo "\n position simples ***********************\n";
			// var_dump($posSimple);
			
			// echo "\n positions simples \n";
			// var_dump($posSimple);
			
			$barragePositions = Hongurian_algo::BarrerEncadrerZero ( $matrice, $posSimple );
			
			$posEncadree = $barragePositions ["posEncadree"];
			$posBarree = $barragePositions ["posBarree"];
			// echo "\n pos encadree \n ";
			// var_dump($posEncadree);
			// echo "\n pos barree \n ";
			// var_dump($posBarree);
			// break;
		}
		
		// echo "\n positions encadree finales\n ";
		// var_dump($posEncadree);
		
		// return $posEncadree;
		return $posEncadree;
	}
	public static function modificationMatrice($matrice, $ligneBarree, $ligneNonBarree, $colonneBarree, $colonneNonBarree) {
		// recherche du plus petit élément
		$min = $matrice [$ligneNonBarree [0]] [$colonneNonBarree [0]];
		foreach ( $ligneNonBarree as $l ) {
			foreach ( $colonneNonBarree as $c ) {
				if ($matrice [$l] [$c] < $min) {
					$min = $matrice [$l] [$c];
				}
			}
		}
		// soustraction aux valeur non barrées
		foreach ( $ligneNonBarree as $l ) {
			foreach ( $colonneNonBarree as $c ) {
				$matrice [$l] [$c] = $matrice [$l] [$c] - $min;
			}
		}
		// addition aux valeurs barrées
		foreach ( $ligneBarree as $l ) {
			foreach ( $colonneBarree as $c ) {
				$matrice [$l] [$c] = $matrice [$l] [$c] + $min;
			}
		}
		return $matrice;
	}
	public static function barrerLignesColonnes($matrice, $posEncadree, $PosBarree) {
		// marquage des lignes n'ayant pas de position encadrée
		$n = count ( $matrice );
		
		$ligneBarree = array ();
		$ligneNonBarree = array ();
		$colonneBarree = array ();
		$colonneNonBarree = array ();
		
		$lignePosEncadree = array ();
		foreach ( $posEncadree as $pos ) {
			if (! in_array ( $pos ["i"], $lignePosEncadree )) {
				$lignePosEncadree [] = $pos ["i"];
			}
		}
		// echo "\n ligne contenant des encadrées \n ";
		// var_dump($lignePosEncadree);
		
		for($l = 0; $l < $n; $l ++) {
			if (! in_array ( $l, $lignePosEncadree )) {
				$ligneNonBarree [] = $l;
			}
		}
		// echo "\n lignes non barrées \n ";
		// var_dump($ligneNonBarree);
		
		$marquee = true;
		$test = 0;
		while ( $marquee ) {
			// marquage des colonnes dont les éléments des lignes précédentes ont des positions barréee
			$marquee = false;
			// echo "\n positions barrées \n ";
			// var_dump($PosBarree);
			foreach ( $ligneNonBarree as $l ) {
				for($j = 1; $j < $n; $j ++) {
					
					if ($matrice [$l] [$j] == 0 && Hongurian_algo::positionInListPosition ( array (
							"i" => $l,
							"j" => $j 
					), $PosBarree )) {
						
						if (! in_array ( $j, $colonneBarree )) {
							$colonneBarree [] = $j;
							$marquee = true;
						}
					}
				}
			}
			
			// echo "\n colonne barrées \n ";
			// var_dump($colonneBarree);
			// Marquage des lignes dont les éléments des colonnes précédentes sont encadrées
			// echo "\n positions encadree \n ";
			// var_dump($posEncadree);
			foreach ( $colonneBarree as $c ) {
				for($i = 0; $i < $n; $i ++) {
					
					if ($matrice [$i] [$c] == 0 && 

					Hongurian_algo::positionInListPosition ( array (
							"i" => $i,
							"j" => $c 
					), $posEncadree )) {
						
						if (! in_array ( $i, $ligneNonBarree )) {
							$ligneNonBarree [] = $i;
							$marquee = true;
						}
					}
				}
				// echo "\n ligne non barrées \n ";
				// var_dump($ligneNonBarree);
			} // echo "\n marquee ".$marquee;
				  // echo "\n test ------------- ".$test; // $test++; if ($test == 4) break;
		}
		// calcul des lignes Barrées
		for($i = 0; $i < $n; $i ++) {
			if (! in_array ( $i, $ligneNonBarree )) {
				$ligneBarree [] = $i;
			}
		}
		for($j = 0; $j < $n; $j ++) {
			if (! in_array ( $j, $colonneBarree )) {
				$colonneNonBarree [] = $j;
			}
		}
		
		// echo "\n résultat \n";
		
		return array (
				"ligneBarree" => $ligneBarree,
				"ligneNonBarree" => $ligneNonBarree,
				"colonneBarree" => $colonneBarree,
				"colonneNonBarree" => $colonneNonBarree 
		);
	}
	public static function BarrerEncadrerZero($matrice, $PosSimple) {
		$n = count ( $matrice );
		$PositionEncadree = array ();
		$PositionBarree = array ();
		
		$NomPosSimpleParLigne = array ();
		for($t = 0; $t < $n; $t ++) {
			$NomPosSimpleParLigne [] = 0;
		}
		// obtention de la future ligne sur laquelle un zero sera encadré
		
		// $test=0;
		while ( count ( $PosSimple ) > 0 ) {
			$prochaineLigne = 0;
			
			$m = count ( $PosSimple );
			for($t = 0; $t < $n; $t ++) {
				$NomPosSimpleParLigne [$t] = 0;
			}
			// echo "\n pos simple \n ";
			// var_dump($PosSimple);
			for($t = 0; $t < $m; $t ++) {
				$NomPosSimpleParLigne [$PosSimple [$t] ["i"]] = $NomPosSimpleParLigne [$PosSimple [$t] ["i"]] + 1;
			}
			// echo "\n nombres de positions simples par ligne \n ";
			// var_dump($NomPosSimpleParLigne);
			$ligneNombreZeroSimpleMin = 0;
			// $NombreZeroSimpleMin = $NomPosSimpleParLigne[0];
			$NombreZeroSimpleMin = 100000;
			for($t = 0; $t < $n; $t ++) {
				if ($NomPosSimpleParLigne [$t] != 0 && $NomPosSimpleParLigne [$t] < $NombreZeroSimpleMin) {
					$prochaineLigne = $t;
					$NombreZeroSimpleMin = $NomPosSimpleParLigne [$t];
				}
			}
			// echo "\n prochaine ligne \n ";
			// echo $prochaineLigne;
			// encadrement du zero de cette ligne
			for($j = 0; $j < $n; $j ++) {
				if ($matrice [$prochaineLigne] [$j] == 0 && Hongurian_algo::positionInListPosition ( array (
						"i" => $prochaineLigne,
						"j" => $j 
				), $PosSimple )) {
					$colonneZero = $j;
					break;
				}
			}
			// echo "\n prochaine colonne \n ";
			// echo $colonneZero;
			$PosSimple = Hongurian_algo::deletePositionInListPosition ( array (
					"i" => $prochaineLigne,
					"j" => $colonneZero 
			), $PosSimple );
			$PositionEncadree [] = array (
					"i" => $prochaineLigne,
					"j" => $colonneZero 
			);
			// barrage des zero superflus par ligne et par colonnes
			for($j = $colonneZero + 1; $j < $n; $j ++) {
				if ($matrice [$prochaineLigne] [$j] == 0 && Hongurian_algo::positionInListPosition ( array (
						"i" => $prochaineLigne,
						"j" => $j 
				), $PosSimple )) {
					$PosSimple = Hongurian_algo::deletePositionInListPosition ( array (
							"i" => $prochaineLigne,
							"j" => $j 
					), $PosSimple );
					$PositionBarree [] = array (
							"i" => $prochaineLigne,
							"j" => $j 
					);
					// echo "zero retrouvé sur la même ligne sur la colonne ".$j;
				}
			}
			
			for($i = 0; $i < $n; $i ++) {
				if ($i != $prochaineLigne && $matrice [$i] [$colonneZero] == 0 && Hongurian_algo::positionInListPosition ( array (
						"i" => $i,
						"j" => $colonneZero 
				), $PosSimple )) {
					$PosSimple = Hongurian_algo::deletePositionInListPosition ( array (
							"i" => $i,
							"j" => $colonneZero 
					), $PosSimple );
					$PositionBarree [] = array (
							"i" => $i,
							"j" => $colonneZero 
					);
					// echo "zero retrouvé sur la même colonne sur la ligne ".$i;
				}
			}
			// $test++; if($test==4) break;
		}
		
		return array (
				"posBarree" => $PositionBarree,
				"posEncadree" => $PositionEncadree 
		);
		
		//
	}
	public static function initialisationMatrice($matrice) {
		$n = count ( $matrice );
		$PosSimples = array ();
		for($i = 0; $i < $n; $i ++) {
			$min = min ( $matrice [$i] );
			for($j = 0; $j < $n; $j ++) {
				$matrice [$i] [$j] = $matrice [$i] [$j] - $min;
				if ($matrice [$i] [$j] == 0) {
					$PosSimples [] = array (
							"i" => $i,
							"j" => $j 
					);
				}
			}
		}
		for($j = 0; $j < $n; $j ++) {
			$min = min ( array_column ( $matrice, $j ) );
			if ($min != 0) {
				for($i = 0; $i < $n; $i ++) {
					$matrice [$i] [$j] = $matrice [$i] [$j] - $min;
					if ($matrice [$i] [$j] == 0) {
						$PosSimples [] = array (
								"i" => $i,
								"j" => $j 
						);
					}
				}
			}
		}
		return array (
				"matrice" => $matrice,
				"posSimple" => $PosSimples 
		);
	}
	public static function positionInListPosition($position, $listePosition) {
		$result = false;
		$n = count ( $listePosition );
		for($i = 0; $i < $n; $i ++) {
			if (($position ["i"] == $listePosition [$i] ["i"]) && ($position ["j"] == ($listePosition [$i] ["j"]))) {
				$result = true;
				break;
			}
		}
		return $result;
	}
	public static function deletePositionInListPosition($position, $listePosition) {
		$p = - 1;
		$n = count ( $listePosition );
		$listePositionPrim = array ();
		foreach ( $listePosition as $l ) {
			$listePositionPrim [] = $l;
		}
		for($i = 0; $i < $n; $i ++) {
			if (($position ["i"] == $listePositionPrim [$i] ["i"]) && ($position ["j"] == ($listePositionPrim [$i] ["j"]))) {
				$p = $i;
				
				break;
			}
		}
		if ($p != - 1) {
			array_splice ( $listePositionPrim, $p, 1 );
		}
		return $listePositionPrim;
	}
}