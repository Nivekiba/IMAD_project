<?php
include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
/// l'entrée ici est constitué de PointList, un  array de point  (array de clés "x" et "y") , 
//  								et de VList un array de floats 
class ThinPlaneSpline {
	
	
	
	public static function ThinPlaneSplineFunction($pointList,$WaVector,$Point){
		$result = 0;$n = count($pointList);
		for($i = 0; $i < $n; $i ++){
			//cho "///////calcul de U pour le produit \n".ThinPlaneSpline::Ufunction($pointList[$i],$Point);
			$result = $result + ThinPlaneSpline::Ufunction($pointList[$i],$Point)*$WaVector[$i];
		}
		
		$result = $result + $WaVector[$n];
		$result = $result + $Point["x"]*$WaVector[$n+1];
		$result = $result + $Point["y"]*$WaVector[$n+2];
		return $result;
	}
	
	
	public static function WaVector($LMatrix,$VZeroVector){
		gc_collect_cycles();
		$LInverse = Lapack::pseudoInverse($LMatrix);
		$n3 = count($LMatrix);
		$result = array();
		for($i = 0; $i < $n3; $i ++){
			$sum = 0;
			for($j = 0; $j < $n3; $j ++){
				$sum = $sum + $LInverse[$i][$j] * $VZeroVector[$j];
			}
			$result[] = $sum;	
		}	
		return $result;
	}
	
	public static function LMatrix($PointList){
		$n = count($PointList);
		$KMatrix = ThinPlaneSpline::KMatix($PointList);
		$Pmatrix = ThinPlaneSpline::PMatrix($PointList);
		$PTransposeMatrix = ThinPlaneSpline::PTransposeMatrix($PointList);
		$ZeroMatix = ThinPlaneSpline::ZeroMatrix();
		$Lmatrix = array();
		for($i = 0; $i < $n; $i ++){
			$irow = array();
			for($j = 0; $j < $n; $j ++){
				$irow[] = $KMatrix[$i][$j];
			}
			for($j = 0; $j < 3; $j ++){
				$irow[] = $Pmatrix[$i][$j];
			}
			$Lmatrix[] = $irow;
		}
		for($i = 0; $i < 3; $i++){
			$irow = array();
			for($j = 0; $j < $n; $j ++){
				$irow[] = $PTransposeMatrix[$i][$j];
			}
			for($j = 0; $j < 3; $j ++){
				$irow[] = $ZeroMatix[$i][$j];
			}
			$Lmatrix[] = $irow;
		}
		
		
		
		return $Lmatrix;
		
	}
	
	
	public static function ZeroMatrix($taille = 3){
		$zeroMatrix = array();
		for($i = 0; $i < $taille; $i ++){
			$irow = array();
			for($j = 0; $j < $taille; $j ++){
				$irow[] = 0;
			}
			$zeroMatrix[]=$irow;
		}
		return $zeroMatrix;
	}
	public static function PTransposeMatrix($PointList){
		$n = count($PointList);
		$PTransposeMatrix = array();
		$irow1 = array();
		for($i = 0; $i < $n; $i ++){
			$irow1[] = 1;			
		}
		$PTransposeMatrix[]=$irow1;
		
		$irow2 = array();
		for($i = 0; $i < $n; $i ++){
			$irow2[] = $PointList[$i]["x"];
		}
		$PTransposeMatrix[]=$irow2;
		
		$irow3 = array();
		for($i = 0; $i < $n; $i ++){
			$irow3[] = $PointList[$i]["y"];
		}
		$PTransposeMatrix[]=$irow3;
		
		
		return $PTransposeMatrix;
	}
	public static function PMatrix($PointList){
		$n = count($PointList);
		$PMatrix = array();
		for($i = 0; $i < $n; $i ++){
			$irow = array();		
				$irow[] = 1;
				$irow[] = $PointList[$i]["x"];
				$irow[] = $PointList[$i]["y"];		
			$PMatrix[]=$irow;
		}
		return $PMatrix;
	}
	public static function KMatix($PointList){
		$n = count($PointList);
		$kMatrix = array();
		for($i = 0; $i < $n; $i ++){
			$irow = array(); 
			for($j = 0; $j < $n; $j ++){
				$irow[] = ThinPlaneSpline::Ufunction($PointList[$j],$PointList[$i]);
				//echo " ----".ThinPlaneSpline::Ufunction($PointList[$j],$PointList[$i]);
			}
			$kMatrix[]=$irow;
		}
	
		return $kMatrix;
		
	}
	public static function Ufunction($P1,$P2){
		$r = ThinPlaneSpline::DistanceBetween($P1, $P2);
		if ($r != 0) {return pow($r, 2)*log10($r);}	
		else {return 0;}
	}
	public static function DistanceBetween($P1,$P2){
		return sqrt(pow($P1["x"]-$P2["x"], 2)+pow($P1["y"]-$P2["y"], 2));
	}
	public static function VZeroVector($VList){
		$n = count($VList);
		$VZeroVector = array();
		for($i = 0; $i < $n; $i ++){
			$VZeroVector[] = 0;
		}
		for($i = 0; $i < $n; $i ++){
			$VZeroVector[$i] = $VList[$i];
		}
		for($i = 0; $i < 3; $i ++){
			$VZeroVector[]=0;
		}
		return $VZeroVector;
	}
	
}