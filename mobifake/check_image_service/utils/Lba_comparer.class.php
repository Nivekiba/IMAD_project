<?php

include_once "../entities/Icon.class.php";
include_once "Constants.class.php";
include_once "Lba_transformer.class.php";
class Lba_comparer {
	
	public static function lba_distance($l1Etoil,$a1Etoil,$b1Etoil,$l2Etoil,$a2Etoil,$b2Etoil,$Kl,$Kh,$Kc){
		$C1abEtoil = sqrt(pow($a1Etoil, 2)+pow($b1Etoil, 2));
		$C2abEtoil = sqrt(pow($a2Etoil, 2)+pow($b2Etoil, 2));
		$CabBarEtoil=($C1abEtoil+$C2abEtoil)/2;
		$CabBarEtoil7 =pow($CabBarEtoil, 7);
		$G = 0.5 * (1- sqrt($CabBarEtoil7 /($CabBarEtoil7 + pow(25, 7))));
		$a1Prim = (1+$G)*$a1Etoil;
		$a2Prim = (1+$G)*$a2Etoil;
		$C1Prim = sqrt(pow($a1Prim,2)+pow($b1Etoil,2));
		$C2Prim = sqrt(pow($a2Prim,2)+pow($b2Etoil,2));
		if ($b1Etoil==0 && $a1Prim==0){
			$h1Prim =0;
		}else{
			$h1Prim = atan2($b1Etoil,$a1Prim);
			if ($h1Prim<0)	{$h1Prim = $h1Prim+2*pi();}
			//$h1Prim = atan($a1Prim/$b1Etoil);
		}
		if ($b2Etoil==0 && $a2Prim==0){
			$h2Prim =0;
		}else{
			$h2Prim = atan2($b2Etoil,$a2Prim);
			if ($h2Prim<0)	{$h2Prim = $h2Prim+2*pi();}
			//$h2Prim = atan($a2Prim/$b2Etoil);
		}
		$h1Prim_degres = $h1Prim*180/pi();
		$h2Prim_degres = $h2Prim*180/pi();
		$DeltaLPrim =  $l2Etoil-$l1Etoil;
		$DeltaCPrim =  $C2Prim - $C1Prim;
		$angle_test = ($h2Prim - $h1Prim);
		$produit_test =$C1Prim*$C2Prim;
		if($produit_test==0){
			$DeltahPrim=0;
	
	
		}elseif(abs($angle_test)<=pi()){
			$DeltahPrim = $angle_test;
		}elseif($angle_test>pi()){
			$DeltahPrim = $angle_test - 2*pi();
		}else{
			$DeltahPrim = $angle_test + 2*pi();
		}
		$DeltaHPrim = 2 * sqrt($produit_test)* sin($DeltahPrim/2);
	
		$LBarPrim = ($l1Etoil+$l2Etoil)/2;
		$CBarPrim = ($C1Prim+$C2Prim)/2;
		$somme = $h1Prim + $h2Prim;
		if($produit_test==0){
			$hBarPrim = $somme;
		}elseif(abs($angle_test)<=pi()){
			$hBarPrim = $somme/2;
		}elseif(abs($angle_test)>pi()  &&  $somme<360){
			$hBarPrim = ($somme+360)/2;
		}else{
			$hBarPrim = ($somme-360)/2;
		}
	
		$T = 1-0.17*cos($hBarPrim - pi()/6)+0.24*cos(2*$hBarPrim)
		+ 0.32*cos(3*$hBarPrim + pi()/30)-0.2 * cos(4*$hBarPrim - 63*pi()/180 );
		$hBarPrim_degres = $hBarPrim * 180 / pi();
		$puissance_2= pow(($hBarPrim_degres-275)/25 ,2) ;
		$DeltaTeta = 30 * exp(-$puissance_2);
		$CBarPrim7 = pow($CBarPrim, 7);
		$Rc = 2*sqrt($CBarPrim7/($CBarPrim7+pow(25,7)));
		$puissance = pow(($LBarPrim-50), 2);
		$Sl = 1 + (0.015*$puissance)/sqrt(20+($puissance));
		$Sc= 1 +   (0.045)* $CBarPrim;
		$Sh= 1 +  ((0.015)* $CBarPrim*$T);
		$Rt = -$Rc*sin(  2*$DeltaTeta*pi()/180   );
		$result = sqrt(
				(pow($DeltaLPrim/($Kl*$Sl), 2))+
				(pow($DeltaCPrim/($Kc*$Sc), 2))+
				(pow($DeltaHPrim/($Kh*$Sh),2))+
				$Rt*($DeltaCPrim/($Kc*$Sc))*
				($DeltaHPrim/($Kh*$Sh))
		);
		//echo "\n distance lab ";
		//echo "\n L1 = ".$l1Etoil." ; a1 = ".$a1Etoil."; b1 =  ".$b1Etoil."; a1Prim = ".$a1Prim." ; C1Prim =".$C1Prim ."; h1Prim = ".$h1Prim_degres. "; hBarPrim = ". $hBarPrim_degres."; G= ".$G."; T= ".$T. "; Sl =". $Sl. "; Sc = ".$Sc." ; Sh = ".$Sh." ; Rt = ".$Rt."; E00 = ". $result;
		//echo "\n L2 = ".$l2Etoil." ; a2 = ".$a2Etoil."; b2 =  ".$b2Etoil."; a2Prim = ".$a2Prim." ; C2Prim =".$C2Prim ."; h2Prim = ".$h2Prim_degres. "; hBarPrim = ". $hBarPrim_degres."; G= ".$G."; T= ".$T. "; Sl =". $Sl. "; Sc = ".$Sc." ; Sh = ".$Sh." ; Rt = ".$Rt."; E00 = ". $result;
		return $result;
	}
	
	public static function compare_distinct_lba_values_occurence($distinct_lba_values_occurence1,$distinct_lba_values_occurence2){	
		$min_values = array();
		foreach ($distinct_lba_values_occurence1 as $d1){
			$actual_min_value = 10000000;
			foreach ($distinct_lba_values_occurence2 as $d2){
				$actual_temp = Lba_comparer::lba_distance($d1["l"],
						$d1["a"],
						$d1["b"],
						$d2["l"],
						$d2["a"],
						$d2["b"], 
						Constants::getLabDistanceKl(),
				 Constants::getLabDistanceKh(), 
				Constants::getLabDistanceKc());
					
				if($actual_min_value >= $actual_temp){
					$actual_min_value = $actual_temp;
				}
			}
			$min_values[]=$actual_min_value;
		}
		return array_sum($min_values)/count($min_values);
	}
	
	
}