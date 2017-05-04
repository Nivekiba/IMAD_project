<?php
include_once "../entities/ImageNivGris.class.php";
include_once "../entities/ShapePoint.class.php";
include_once "Constants.class.php";
include_once "Hongurian_algo.class.php";
include_once "MukresAlgorithm.class.php";
include_once "ThinPlaneSpline.class.php";
class ShapePoint_Comparer{

	
	
	public static function compare($shapePointList1,$shapePointList2){
		$tailleC = min(array(count($shapePointList2),count($shapePointList1)));
	   
		//////      début de l'affectation ****************************************************************/////////
	
		// 1-  égalisation du nombre de points par shapepointList via kmeans
		if(count($shapePointList1)!=count($shapePointList2)){
			$n = min(array(count($shapePointList1),count($shapePointList2)));
				
			if ($n == count($shapePointList1)){
	
				$shapePointList2 = ShapePoint_Comparer::reduce($shapePointList2,$n);
	
	
			}else{
	
				$shapePointList1 = ShapePoint_Comparer::reduce($shapePointList1,$n);
	
			}
		}
	
	    // 2-  mise à jour des histogrammes 
		for($i = 0; $i < count($shapePointList1); $i ++) {
			$histogramm = ShapePoint_Comparer::getHistogramm($shapePointList1[$i], $shapePointList1);
			$shapePointList1[$i]->setHistogram($histogramm);
				
		}
	
		for($i = 0; $i < count($shapePointList2); $i ++) {
			$histogramm = ShapePoint_Comparer::getHistogramm($shapePointList2[$i], $shapePointList2);
			$shapePointList2[$i]->setHistogram($histogramm);
		}
		
		// 3-   Calcul de la matrice C
		$C = array();
		for($i = 0; $i < $tailleC; $i ++) {
			$CarrayLigne=array();
			for($j = 0; $j < $tailleC; $j ++) {
				$CarrayLigne[] = floor(ShapePoint_Comparer::compareHistogram($shapePointList1[$i],$shapePointList2[$j]));
			}
			$C[]=$CarrayLigne;
		}
		// 4-   Calcul des permutations 
	
		//var_dump($C);
		//echo "calcul de C réussi";
	
		//echo "\n test de l'algorithme hongroi \n";
		///////////////////////////////// to delete  debut /////////////////////
	    //$shapePointList2 = array("0","1","2","3","4");
	    /*$C = array(array(1,2,3),
	    		array(2,4,6),
	    		array(3,6,9));*/
	    /*$C = array(array(17,15,9,5,12),
	            array(16,16,10,5,10),
	    		array(12,15,14,11,5),
	    		array(4,8,14,17,13),
	    		array(13,9,8,12,17));*/
	    /*$C = array(array(12,10,4,0,7),
	              array(11,11,5,0,5),
	    		array(7,10,9,6,0),
	    		array(0,4,10,13,9),
	    		array(5,1,0,4,9));*/
	  /*$C = array(array(12,10,8,0,7,0,7),
				array(11,11,5,0,8,2,9),
				array(7,10,9,65,0,2,2),
				array(0,4,10,3,13,9,9),
				array(5,1,0,4,0,9,8),
		       array(130,4,10,3,13,9,9),
		       array(9,4,10,3,3,9,9));*/
	  /*  $C = array(array(16,18,16,13,15),
	               array(14,13,12,11,13),
	    		array(20,19,20,16,18),
	    		array(19,19,21,18,20),
	    		array(22,21,24,19,20));*/
	   /*$C = array(array(1,2,10,8,0,7,0,7),
	    		array(1,1,11,5,0,8,2,9),
	    		array(7,1,0,9,65,0,2,2),
	    		array(0,4,10,3,13,9,9,129),
	    		array(5,1,0,4,0,9,34,8),
	    		array(130,4,10,3,13,9,34,9),
	    		array(7,4,10,3,6,9,34,9),
	    		array(9,4,10,3,3,9,9,3));*/
	/* $C = array(array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    		array(1,1,1,1,1,1,1,1),
	    	    array(1,1,1,1,1,1,1,1));*/
		//$shapePointList2 = array("0","1","2","3","4");
		/*$C = array(array(0,2,8,8,12,5,2,5,2,2),
		 		    array(1,1,7,1,2,7,0,8,2,5),
		 		    array(5,2,3,9,6,12,5,3,2,5),
		 		    array(13,9,9,3,0,4,9,3,45,5),
		 		    array(5,2,3,6,12,5,2,3,24,4),
		 		    array(13,9,3,0,4,13,9,3,0,4),
		 		    array(5,2,3,6,12,5,2,3,9,3),
		 		   
		 		    array(5,2,3,12,5,3,6,12,3,2),
		 		    array(1,3,9,3,0,4,13,9,3,4),
		            array(5,2,3,12,5,2,3,6,1,2));*/
	   /* $C = array(array(9,12,9,6,9,6,9,10),
	               array(7,8,5,4,3,8,15,5),
	    		   array(6,3,16,18,3,19,6,8),
	    		   array(6,1,17,6,5,11,7,9),
	    		   array(5,0,13,4,5,6,1,2),
	    		   array(12,13,4,12,3,1,12,14),
	    		   array(3,12,3,7,13,6,8,3),
	    		   array(13,4,1,5,5,5,4,9));*/
		/////////////////////////// to delete fin //////////////////////
		//var_dump($C);
		//echo "calcul des permutations";
		//$permutations = Hongurian_algo::getOptimalPermutationIt($C);
		$A = new  MukresAlgorithm();
		$A->initData($C);
		$result = $A->runMunkres();
		$permutations = array();
		for($i = 0; $i < count($C); $i ++){
			for($j = 0; $j < count($C); $j ++){
				if($result[$i][$j] ==1) {
					$permutations[] = array("i"=>$i,"j"=>$j);
				}
			}
		}
		
		// 5 - Ordonnancement de la  shape PointList 2 en fonction des permutations
		$tmp_shapePointList2 = array();
	
		for($i = 0; $i < count($C); $i ++){
			$tmp_shapePointList2[] = NULL;
		}
		foreach ($permutations as $p){
			$tmp_shapePointList2[$p["i"]]=$shapePointList2[$p["j"]];
		}
		$shapePointList2 = $tmp_shapePointList2;

		
	
		// 6 -  calcul de la transformation
		   // 6 - 1 - Construction des vecteurs d'entrée de l'algorithme
	  	$PointList1 = array();$PointList2 = array();$VListX = array();$VListY = array();
	  	
	  	for($i = 0; $i < count($shapePointList1); $i ++){
			$PointList1[]=array("x"=>$shapePointList1[$i]->getX(),"y"=>$shapePointList1[$i]->getY());
		}
		for($i = 0; $i < count($shapePointList2); $i ++){
			$PointList2[]=array("x"=>$shapePointList2[$i]->getX(),"y"=>$shapePointList2[$i]->getY());
			$VListX[] = $shapePointList2[$i]->getX();
			$VListY[] = $shapePointList2[$i]->getY();
		}
		   //  6 - 2 - Obtention des paramètres des fonctions thin spline suivant X et suivant Y
		$LMatrix = ThinPlaneSpline::LMatrix($PointList1);
		$VZeroVectorX = ThinPlaneSpline::VZeroVector($VListX);
		$VZeroVectorY = ThinPlaneSpline::VZeroVector($VListY);
	    $WaVectorX = ThinPlaneSpline::WaVector($LMatrix, $VZeroVectorX);
		$WaVectorY = ThinPlaneSpline::WaVector($LMatrix, $VZeroVectorY);
		   //  6 - 3   - calcul des images de la deuxième liste de shapePoint
			
		$shapePointList3= array();
		for($i = 0; $i < count($shapePointList2); $i ++){
			$shapePointList3[] = ShapePoint_Comparer::thinSplineTransformationFunction(
					   $PointList2[$i], $WaVectorX, $WaVectorY, $PointList1);
			
		}
		
		///  test écriture dans les fichiers pour voir l'image
		
		
		
		   //  6 - 4 -  calcul des histogramme des points de ces images

		for($i = 0; $i < count($shapePointList3); $i ++) {
			$histogramm = ShapePoint_Comparer::getHistogramm($shapePointList3[$i], $shapePointList3);
			$shapePointList3[$i]->setHistogram($histogramm);
		}
	  
		
		
		
		
		// 7 -  calcul de la distance entre la shapePointList1 et la ShapePointList3
		
		$somme1 = 0; 
		$somme2 = 0;
		
		for($i = 0; $i < $tailleC; $i ++){
			gc_collect_cycles();
			$min = ShapePoint_Comparer::compareHistogram($shapePointList1[$i], $shapePointList3[0]);
			$arg_min=0;
			for($j = 0; $j < $tailleC; $j ++){
				$temp = ShapePoint_Comparer::compareHistogram($shapePointList1[$i], $shapePointList3[$j]);
				if($min > $temp){
					$min = $temp;
					$arg_min = $j;
				}
			}
			$somme1 = $somme1+$min;
		}
		for($i = 0; $i < $tailleC; $i ++){
			gc_collect_cycles();
			$min = ShapePoint_Comparer::compareHistogram($shapePointList1[0], $shapePointList3[$i]);
			$arg_min=0;
			for($j = 0; $j < $tailleC; $j ++){
				$temp = ShapePoint_Comparer::compareHistogram($shapePointList1[$j], $shapePointList3[$i]);
				if($min > $temp){
					$min = $temp;
					$arg_min = $j;
				}
			}
			$somme2 = $somme2+$min;
		}	
		
		$distance = ($somme1+$somme2)/$tailleC;
		
		return $distance;
	}
	
	public static function thinSplineTransformationFunction($point,$WaVectorX,$WaVectorY,$pointList1){
		
		$Ximage = (ThinPlaneSpline::ThinPlaneSplineFunction($pointList1, $WaVectorX, $point));
		$Yimage = (ThinPlaneSpline::ThinPlaneSplineFunction($pointList1, $WaVectorY, $point));
		return new ShapePoint($Ximage, $Yimage);
	}
	public static function compareHistogram($shapePoint1,$shapePoint2){
		$histogramSize = Constants::getRAreaNumberHistrogram()*Constants::getTetaAreaNumberHistogram();
		$sum = 0;
		for($k = 0; $k < $histogramSize; $k ++){
			$numerator = pow($shapePoint1->getHistogram()[$k]-
					$shapePoint2->getHistogram()[$k], 2);
			$denominator = $shapePoint1->getHistogram()[$k]+
					$shapePoint2->getHistogram()[$k];
			if($denominator != 0){
				$sum = $sum + $numerator/$denominator;
			}
			
		}
		return $sum/2;
	}
	public static function reduce($shapePointList,$n){
		// application de l'agorithme kmeans
		$N = count($shapePointList);
		$cluster_centers= array();
		$shapePointListPrim = array();
		$affectation = array();
		// initialisation de la liste manipulable
		foreach ($shapePointList as $shapePoint){
			$shapePointListPrim [] = $shapePoint;
		}
		// initialisation des centres des clusters
		for($i = 0; $i < $n; $i ++) {
			$j= rand(0,count($shapePointListPrim)-1);
			$cluster_centers[] = $shapePointListPrim[$j];
			array_splice($shapePointListPrim,$j,1);		
		}
		// première affectation avec les centres les plus proches
		for($i = 0; $i < $N; $i ++){
			$jmin = 0;$distMin = 1000000;
			for($j = 0; $j < $n; $j ++){
				$dist_act = ShapePoint_Comparer::distanceBetween($shapePointList[$i],$cluster_centers[$j]);
				if($dist_act<$distMin){
					$distMin = $dist_act;
					$jmin = $j;
				}
			}
			$affectation[$i]=$jmin;		
		}
		
		// calcul des nouveaux centres 
		for($i = 0; $i < $n; $i ++){
			$moyenneX = 0;$moyenneY = 0; $count = 0;
			for($j = 0; $j < $N; $j ++){
				if($affectation[$j] == $i){
					$moyenneX = $moyenneX + $shapePointList[$j]->getX();
					$moyenneY = $moyenneY + $shapePointList[$j]->getY();
					$count = $count +1;
				}
			}
			$cluster_centers[$i] = new ShapePoint(floor($moyenneX/$count), floor($moyenneY/$count));
		}
		// tant qu'il n'y a pas changement faire
		$change = 1;
		while($change==1){
			$change = 0;
			
			// calcul de nouvelles affectations
			
			for($i = 0; $i < $N; $i ++){
				$jmin = 0;$distMin = ShapePoint_Comparer::distanceBetween($shapePointList[$i],$cluster_centers[0]);
				for($j = 0; $j < $n; $j ++){
					$dist_act = ShapePoint_Comparer::distanceBetween($shapePointList[$i],$cluster_centers[$j]);
					if($dist_act<$distMin){
						$distMin = $dist_act;
						$jmin = $j;
					}
				}
				if($affectation[$i]!=$jmin){
					$affectation[$i]=$jmin;
					$change = 1;
				}
			}
			// s'il y'a changement calcul de nouveaux centres 
			if($change == 1){
				for($i = 0; $i < $n; $i ++){
					$moyenneX = $cluster_centers[$i]->getX();$moyenneY = $cluster_centers[$i]->getY(); $count = 1;
					$premiere_valeur = 1;
					for($j = 0; $j < $N; $j ++){
						
						
						if($affectation[$j]==$i){
							if($premiere_valeur == 1){
								$premiere_valeur=0;
								$moyenneX = $shapePointList[$j]->getX();
								$moyenneY = $shapePointList[$j]->getY();
								$count = 1;
							}else{
								$moyenneX = $moyenneX + $shapePointList[$j]->getX();
								$moyenneY = $moyenneY + $shapePointList[$j]->getY();
								$count = $count +1;	
							}
						}
					}
					$cluster_centers[$i] = new ShapePoint(floor($moyenneX/$count), floor($moyenneY/$count));
				}
			}
			
			
		}
		// on retourne les centres 
		return $cluster_centers;
		
	}
	public static function distanceBetween($shapePoint1,$shapePoint2){
		return sqrt(pow($shapePoint1->getX()-$shapePoint2->getX(), 2)+
					pow($shapePoint1->getY()-$shapePoint2->getY(), 2));
	}
	public static function getHistogramm($shapePoint, $shapePointList){
		$Rintervall = ShapePoint_Comparer::getRIntervall($shapePointList);
		
	
		$histogramm= array();
		$histogramm_field_number = Constants::getRAreaNumberHistrogram()*Constants::getTetaAreaNumberHistogram();
		
		
		
		
		for($i = 0; $i < $histogramm_field_number; $i ++) {
			$histogramm[$i]=0;
		}
		foreach ($shapePointList as $actual_shapePoint){	
			$R=sqrt(pow(($shapePoint->getX()-
					$actual_shapePoint->getX()
							),2)
					+pow(($shapePoint->getY()-
					$actual_shapePoint->getY()
							),2)
					);
			$RPolar =exp(log10($R));
			$Teta = atan2($actual_shapePoint->getY()-$shapePoint->getY(), 
					$actual_shapePoint->getX()-$shapePoint->getX()) ;
			if ($Teta<0)	{$Teta = $Teta+2*pi();}	
			//echo "\n valeur l'entrée".$shapePoint->getX()."---------".$shapePoint->getY();
			//echo "\n valeur de Teta" .$Teta ;
			//echo "\n valeur de R polaire".$RPolar	;
			//echo "\nvaleur de l'intervalle" .$Rintervall  ; 
			//echo "\n********** index ajouté :".ShapePoint_Comparer::getPinIndex($RPolar, $Teta,$Rintervall);
			$histogramm[ShapePoint_Comparer::getPinIndex($RPolar, $Teta,$Rintervall)] = $histogramm[ShapePoint_Comparer::getPinIndex($RPolar, $Teta,$Rintervall)]+1;		
			
		}
		
		
		return $histogramm;
	}
	
	public static function getPinIndex($RPolar,$Teta,$Rintervall){
		
		$temp= $RPolar/$Rintervall ;
		return Constants::getRAreaNumberHistrogram()*floor($temp)  +
				floor($Teta/ShapePoint_Comparer::getTetaIntervall());
	}
	public static function getTetaIntervall(){
		return 2*pi()/Constants::getTetaAreaNumberHistogram();
	}
	public static function getRIntervall($shapePointList){
		//echo "R max de shapePointList".exp(log10(ShapePoint_Comparer::getRmax($shapePointList)));
		return exp(log10(ShapePoint_Comparer::getRmax($shapePointList)))/Constants::getRAreaNumberHistrogram();
	}
	
	public static function getRmax($shapePointList){
		
		return sqrt(pow((ShapePoint_Comparer::getXMax($shapePointList)-
					ShapePoint_Comparer::getXMin($shapePointList)
							),2)
					+pow((ShapePoint_Comparer::getYMax($shapePointList)-
					ShapePoint_Comparer::getYMin($shapePointList)
							),2)
					);
	}
	
	
	
	public static function getXMax($shapePointList){
		$Xmax = 0;
		foreach ($shapePointList  as $shapePoint){
			if ($shapePoint->getX() > $Xmax ){
				$Xmax = $shapePoint->getX();
			}
				
		}
		return $Xmax;
	}
	public static function getYMax($shapePointList){
		$Ymax = 0;
		foreach ($shapePointList  as $shapePoint){
			if ($shapePoint->getY() > $Ymax ){
				$Ymax = $shapePoint->getY();
			}
	
		}
		return $Ymax;
	}
	public static function getXMin($shapePointList){
		$Xmin = $shapePointList[0]->getX();
		foreach ($shapePointList  as $shapePoint){
			if ($shapePoint->getX() < $Xmin ){
				$Xmin = $shapePoint->getX();
			}
		}
		return $Xmin;
	}
	public static function getYMin($shapePointList){
		$Ymin = $shapePointList[0]->getY();
		foreach ($shapePointList  as $shapePoint){
			if ($shapePoint->getY() < $Ymin ){
				$Ymin = $shapePoint->getY();
			}	
		}
		return $Ymin;
	}
}