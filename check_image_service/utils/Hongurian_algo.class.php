<?php
include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
// / une position est ici une array de deux valeurs. avec les clés "i" et "j"ù
// / la matrice d'entrée est un array de n array (de chiffre) de longueur n

// / dans la nouvelle version, la matrice manipulée par l'algorithme est différente de celle entrée
// elle est constitué d'objet de classe elementMatriceHongroise

class Hongurian_algo {
	
	public static function getOptimalPermutationIt($matrice_entree) {
		$from_suite = 0;//--
		$altererMatrice = 1;
		$suitePositionEtoilee = array();
		$suitePositionPrimee = array();
		$n = count ( $matrice_entree );
		$listeLigneCouvertes = array();
		$matrice_simple = Hongurian_algo::initialisationMatrice ( $matrice_entree );
		$matrice_hongroise_simplifiee = Hongurian_algo::transformToHungurianMatrice ( $matrice_simple );
		$matriceEtoilee = Hongurian_algo::etoilageMatrice($matrice_hongroise_simplifiee);
		$listeColonneCouvertes = Hongurian_algo::couvertureColonneMatrice($matriceEtoilee);										//	$test = 0;
		while (count($listeColonneCouvertes) != $n){                                                                   // $test++;       echo " \ntest au début *****".$test."******" ;  //if ($test >= 100000){ return 0;}
			gc_collect_cycles();
			if($altererMatrice == 1){
				$matriceEtoilee = Hongurian_algo::primageMatrice($matriceEtoilee, $listeColonneCouvertes, $listeLigneCouvertes);
			}
			$altererMatrice = 1;
	     	$prochainPrimeeMatrice = Hongurian_algo::rechercheProchainPrimeeMatrice($matriceEtoilee,$listeLigneCouvertes,$listeColonneCouvertes);           	
			while($prochainPrimeeMatrice["rep"]==true){gc_collect_cycles();
				$matriceEtoilee[$prochainPrimeeMatrice["ligne"]][$prochainPrimeeMatrice["colonne"]]->setStatus("primee");
				$rechercheEtoileLigne = Hongurian_algo::rechercheProchainEtoileeLigne($matriceEtoilee,$prochainPrimeeMatrice["ligne"]);	    
				if ($rechercheEtoileLigne["rep"]==false){
					$prochainPrimeeLigne = array("rep"=>true,"colonne"=>$prochainPrimeeMatrice["colonne"]);
					$suitePositionPrimee[] =  array("i"=>$prochainPrimeeMatrice["ligne"],"j"=>$prochainPrimeeLigne["colonne"]);
					$prochainEtoileeColonne = Hongurian_algo::rechercheProchainEtoileeColonne($matriceEtoilee,$prochainPrimeeLigne["colonne"]);   if($prochainEtoileeColonne["rep"]==false){$matriceEtoilee[$prochainPrimeeMatrice["ligne"]][$prochainPrimeeMatrice["colonne"]]->setStatus("etoilee"); $listeColonneCouvertes = array();$listeLigneCouvertes = array();$listeColonneCouvertes = Hongurian_algo::couvertureColonneMatrice($matriceEtoilee);break;}
					do {$suitePositionEtoilee[] = array("i"=>$prochainEtoileeColonne["ligne"],"j"=>$prochainPrimeeLigne["colonne"]);
						$prochainPrimeeLigne = Hongurian_algo::rechercheProchainPrimeeLigne($matriceEtoilee,$prochainEtoileeColonne["ligne"]);
						$suitePositionPrimee[] = array("i"=>$prochainEtoileeColonne["ligne"],"j"=>$prochainPrimeeLigne["colonne"]);
						$prochainEtoileeColonne = Hongurian_algo::rechercheProchainEtoileeColonne($matriceEtoilee,$prochainPrimeeLigne["colonne"]);
		gc_collect_cycles();			}while($prochainEtoileeColonne["rep"]==true);                                                                                   //  echo "\n après construction suite"; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);
					$matriceEtoilee = Hongurian_algo::etoilerDesetoiler($matriceEtoilee, $suitePositionEtoilee, $suitePositionPrimee);$suitePositionEtoilee = array();$suitePositionPrimee = array();	
					$listeColonneCouvertes = array();$listeLigneCouvertes = array();					                                         // $test++;  echo "\n test".$test;      echo "\n après application étoilage suite"; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);										    
					$listeColonneCouvertes = Hongurian_algo::couvertureColonneMatrice($matriceEtoilee);
					$from_suite = 1;
					break;
				}else{																																				//	 echo "\n avant interchangement"; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);		
					if ( !in_array ($prochainPrimeeMatrice["ligne"], $listeLigneCouvertes )){
						$listeLigneCouvertes[] = $prochainPrimeeMatrice["ligne"];	
					}
					if ( in_array ($rechercheEtoileLigne["colonne"], $listeColonneCouvertes)){
						$listeColonneCouvertes= Hongurian_algo::removeFromList($listeColonneCouvertes, $rechercheEtoileLigne["colonne"]);
					}$from_suite = 0;	                                                                                                                                     
				    $prochainPrimeeMatrice = Hongurian_algo::rechercheProchainPrimeeMatrice($matriceEtoilee,$listeLigneCouvertes,$listeColonneCouvertes);  // $test++; // echo "\n test".$test;    echo "\n après interchangement"; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);
				}	
			}
			if($from_suite == 0){
				$smallValue = Hongurian_algo::getSmallUncouveredValue($matriceEtoilee, $listeColonneCouvertes, $listeLigneCouvertes);                       //     echo "\n avant retrait du plus petit  qui est ".$smallValue; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);
				$matriceEtoilee = Hongurian_algo::modificationMatrice($matriceEtoilee, $smallValue, $listeColonneCouvertes, $listeLigneCouvertes);			// $test++;  echo "\n test".$test;     echo "\n après retrait du plus petit"; Hongurian_algo::print_Test($matriceEtoilee, $listeLigneCouvertes, $listeColonneCouvertes, $suitePositionPrimee, $suitePositionEtoilee);	
				$altererMatrice = 0;
			}
			$from_suite == 0;
		}		
		echo "sortie de la boucle";
		return Hongurian_algo::extractPermutations($matriceEtoilee);
	}
	
	
	
	
	
	
	public static function print_Test($matriceEtoilee,$listeLigneCouvertes,$listeColonneCouvertes,$suitePositionPrimee,$suitePositionEtoilee){
		echo "\n *********************un affichage*************************    \n";
		echo "matrice";Hongurian_algo::afficheMatrice($matriceEtoilee);
		echo "\n liste lignes couvertes: ";Hongurian_algo::afficheTableau($listeLigneCouvertes);
		echo "\nliste colonnes  couvertes : ";Hongurian_algo::afficheTableau($listeColonneCouvertes);
		if(count($suitePositionEtoilee)!=0){
			echo "\n liste des positions étoilee\n";
			var_dump($suitePositionEtoilee);
		}
		if(count($suitePositionPrimee)!=0){
			echo "\n liste des positions primee\n";
			var_dump($suitePositionPrimee);
		}	
		echo "\n ******************fin de l'affichage*************************** \n";
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public static function removeFromList($liste,$value){
		$test= array();$test[] = $value;
		$newListe = array_diff($liste, $test);
		$liste = array();
		foreach ($newListe as $n){
			$liste[] = $n;
		}
		return $liste;
	}
	
	public static function deprimer($matriceHongroise){
		$n = count($matriceHongroise);
		for($i = 0; $i < $n; $i ++) {
			for($j = 0; $j < $n; $j ++) {
				if( $matriceHongroise [$i] [$j] ->getValue() == 0 &&
						$matriceHongroise [$i] [$j] ->getStatus()=="primee")
				{
					$matriceHongroise [$i] [$j]->setStatus("simple");
				}
			}
		}
		return $matriceHongroise;
		
	}
	
	public static function etoilerDesetoiler($matriceHongroise,$positionsEtoilee,$PositionsPrimee){
		foreach ($positionsEtoilee as $posEtoil){
			$matriceHongroise[$posEtoil["i"]][$posEtoil["j"]]->setStatus("simple");
		}
		foreach ($PositionsPrimee as $posPrimee){
			$matriceHongroise[$posPrimee["i"]][$posPrimee["j"]]->setStatus("etoilee");
		}
		return $matriceHongroise;
	}
	
	public static function modificationMatrice($matriceHongroise,$smallValue,$listeColonneCouvertes,$listeLigneCouvertes){
		$n = count($matriceHongroise);
		// ajout aux lignes couvertes
		foreach($listeLigneCouvertes as $i) {
			
				for($j = 0; $j < $n; $j++) {
				
					$p =( $matriceHongroise[$i][$j]->getValue() )+$smallValue;
					$matriceHongroise[$i][$j]->setValue($p);
				}
		}
		//retrait aux colonnes non couvertes
		for($j = 0; $j < $n; $j++) {
			if ( !in_array ( $j, $listeColonneCouvertes )){
				for($i = 0; $i < $n; $i++) {
					$m =  ( $matriceHongroise[$i][$j]->getValue() ) -$smallValue ;
					$matriceHongroise[$i][$j]->setValue(  $m  );
				}
				
					
			}
		}
		return $matriceHongroise;
	}
	
	public static function getSmallUncouveredValue($matriceHongroise,$listeColonneCouvertes,$listeLigneCouvertes){
		$n = count($matriceHongroise);
		$smallValue = 0;
		$firstFind=0;
		for($j = 0; $j < $n; $j++) {
			for($i = 0; $i < $n; $i++) {
				if (! in_array ( $j, $listeColonneCouvertes )
						&& !in_array ( $i, $listeLigneCouvertes )
							){
					$smallValue=$matriceHongroise[$i][$j]->getValue();
				//	echo "/////////////********************première prise******************************************************************".$smallValue;
					$firstFind = 1;
					break;
					
				}
			}
			if($firstFind==1){
				break;
			}	
		}
		for($j = 0; $j < $n; $j++) {
			for($i = 0; $i < $n; $i++) {
				if (! in_array ( $j, $listeColonneCouvertes )
					&& !in_array ( $i, $listeLigneCouvertes )
					&&	$smallValue > $matriceHongroise[$i][$j]->getValue()
				){
					$smallValue=$matriceHongroise[$i][$j]->getValue();	
				//	echo "/////////////********************première prise******************************************************************".$smallValue;
				}
			}
		}
		return $smallValue;
	}
	
	public static function rechercheProchainEtoileeLigne($matriceHongroise,$ligne){
		$n= count($matriceHongroise); 
		for($j = 0; $j < $n; $j++) {
			if(	  $matriceHongroise [$ligne][$j] ->getValue() == 0 &&
					$matriceHongroise[$ligne][$j]->getStatus()=="etoilee"){
				return array("rep"=>true,"colonne"=>$j);
			}
		}
		return array("rep"=>false);
	}
	
	public static function rechercheProchainEtoileeColonne($matriceHongroise,$colonne){
		$n= count($matriceHongroise);
		for($i = 0; $i < $n; $i++) {
			if(	     $matriceHongroise [$i][$colonne] ->getValue() == 0 &&
					$matriceHongroise[$i][$colonne]->getStatus()=="etoilee"){
				return array("rep"=>true,"ligne"=>$i);
			}
		}
		return array("rep"=>false);
	}
	
	public static function rechercheProchainPrimeeLigne($matriceHongroise,$ligne){
		$n= count($matriceHongroise);	
		for($j = 0; $j < $n; $j++) {
			if(	    $matriceHongroise [$ligne][$j]->getValue() == 0 &&
					$matriceHongroise[$ligne][$j]->getStatus()=="primee"){
				return array("rep"=>true,"colonne"=>$j);
			}
		}
		return array("rep"=>false);
	}
	
	public static function rechercheProchainPrimeeMatrice($matriceHongroise,$listeLigneCouvertes,$listeColonneCouvertes){
		$n= count($matriceHongroise);
		for($i = 0; $i< $n; $i++) {
			for($j = 0; $j < $n; $j++) {
				if(  $matriceHongroise [$i][$j] ->getValue() == 0 &&
						! in_array ( $j, $listeColonneCouvertes )
						&& ! in_array ( $i, $listeLigneCouvertes)){
					
					return array("rep"=>true,"ligne"=>$i,"colonne"=>$j);
				}
		
			}
		}
		return array("rep"=>false);
	}
	
	public static function primageMatrice($matriceHongroise,$listeColonneCouvertes,$listeLigneCouvertes){
		$n= count($matriceHongroise);
		for($i = 0; $i< $n; $i++) {
			for($j = 0; $j < $n; $j++) {
				if(! in_array ( $j, $listeColonneCouvertes ) 
					&& ! in_array ( $i, $listeLigneCouvertes)
					&&  $matriceHongroise[$i][$j]->getValue()==0){
					$matriceHongroise[$i][$j]->setStatus("primee");
				}
				
			}
		}
		return $matriceHongroise;
	}
	
	public static function couvertureColonneMatrice($matriceHongroise){
		$n = count($matriceHongroise);
		$listeColonneCouvertes = array();
		for($j = 0; $j < $n; $j++) {
			for($i = 0; $i < $n; $i++) {
				if (  $matriceHongroise [$i][$j] ->getValue() == 0 &&
						$matriceHongroise[$i][$j]->getStatus()=="etoilee"
					&& ! in_array ( $j, $listeColonneCouvertes )){	
						$listeColonneCouvertes [] = $j;
						break;
				}
			}
		}
		return $listeColonneCouvertes;
	}

	public static function etoilageMatrice($matriceHongroise){
		$n = count($matriceHongroise);
		for($i = 0; $i < $n; $i ++) {
			for($j = 0; $j < $n; $j ++) {
				if( $matriceHongroise [$i] [$j] ->getValue() == 0 &&
						$matriceHongroise [$i] [$j] ->getStatus()=="simple")
				{
					$IlYaZeroEtoileLigneCol = 0;
					for($k = 0; $k < $n; $k ++) {
						if( $k!=$j && $matriceHongroise [$i] [$k] ->getValue() == 0 &&
								$matriceHongroise [$i] [$k] ->getStatus()=="etoilee"){
							$IlYaZeroEtoileLigneCol = 1;break;
						}
					}
					if ($IlYaZeroEtoileLigneCol == 0){
						for($k = 0; $k < $n; $k ++) {
							if( $k!=$i && $matriceHongroise [$k] [$j] ->getValue() == 0 &&
									$matriceHongroise [$k] [$j] ->getStatus()=="etoilee"){
								$IlYaZeroEtoileLigneCol = 1;break;
							}
						}
					}
					if ($IlYaZeroEtoileLigneCol == 0){
						$matriceHongroise [$i] [$j] ->setStatus("etoilee");
					}				
				}
			}
		
		}

		return $matriceHongroise;
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
	
	public function extractPermutations($matrice_hongroise){
		$n = count ( $matrice_hongroise );
		$result = array();
		for($i = 0; $i < $n; $i ++) {
	
			for($j = 0; $j < $n; $j ++) {
				if(
						$matrice_hongroise [$i] [$j] ->getStatus()=="etoilee")
						{$result[]= array("i"=>$i,"j"=>$j);
					}
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
	public static function afficheMatrice($matriceHongroise){
		$n = count ( $matriceHongroise );
		for($i = 0; $i < $n; $i ++) {
			echo "\n";
			for($j = 0; $j < $n; $j ++) {
				echo " ".$matriceHongroise [$i] [$j] -> getValue();
				echo ";".$matriceHongroise [$i] [$j] -> getStatus();
			}
			echo "\n";
		}
	}
	public static function afficheTableau($matriceHongroise){
		$n = count ( $matriceHongroise );
		echo "\n";
		for($j = 0; $j < $n; $j ++) {
			echo " ".$matriceHongroise [$j] ;
		
		}
		echo "\n";
		
	}

}


class ELementMatriceHongroise {
	var $value; // chiffre
	var $status; // simple,etoilee, primee, s'il s'agit d'un zero.
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
